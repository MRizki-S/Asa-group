<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\BarangSatuanKonversi;
use App\Models\MasterBarang;
use App\Models\NotaBarangMasukDetail;
use App\Models\PembangunanUnitBahan;
use App\Models\PembangunanUnitBarangOrder;
use App\Models\StockGudang;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermintaanBarangController extends Controller
{
    private function orderQuery()
    {
        return PembangunanUnitBarangOrder::with([
            'details',
            'user',
            'qc',
            'pembangunanUnit.unit.blok',
            'pembangunanUnit.unit.type',
            'pembangunanUnit.tahap.perumahaan',
            'pembangunanUnit.pengawas',
        ])
            ->withCount('details')
            ->latest('tanggal_diajukan');
    }

    private function statusOptions(bool $includeMenunggu = true): array
    {
        $options = [
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
            'pengembalian' => 'Pengembalian',
        ];

        return $includeMenunggu
            ? ['diproses' => 'Menunggu'] + $options
            : $options;
    }

    // view daftar permintaan barang yang masih diproses (status menunggu)
    public function index(Request $request)
    {
        $status = $request->get('status', 'diproses');
        $jenisOrder = $request->get('jenis_order', 'all');

        $query = $this->orderQuery();

        if ($status !== 'all') {
            $query->where('status_order', $status);
        }

        if ($jenisOrder !== 'all') {
            $query->where('jenis_order', $jenisOrder);
        }

        $orders = $query->get();

        return view('gudang.permintaan-barang.index', [
            'orders' => $orders,
            'status' => $status,
            'jenisOrder' => $jenisOrder,
            'statusOptions' => $this->statusOptions(),
            'isHistory' => false,
            'titlePage' => 'Daftar Permintaan Barang Proyek',
            'breadcrumbs' => [
                [
                    'label' => 'Permintaan Barang',
                    'url' => route('gudang.permintaanBarang.index'),
                ],
            ],
        ]);
    }

    // view history dari permintaan barang
    public function history(Request $request)
    {
        $status = $request->get('status', 'all');
        $jenisOrder = $request->get('jenis_order', 'all');

        $query = $this->orderQuery();

        if ($status === 'all') {
            $query->where('status_order', '!=', 'diproses');
        } else {
            $query->where('status_order', $status);
        }

        if ($jenisOrder !== 'all') {
            $query->where('jenis_order', $jenisOrder);
        }

        $orders = $query
            ->get();

        return view('gudang.permintaan-barang.index', [
            'orders' => $orders,
            'status' => $status,
            'jenisOrder' => $jenisOrder,
            'statusOptions' => $this->statusOptions(false),
            'isHistory' => true,
            'titlePage' => 'Riwayat Permintaan Barang',
            'breadcrumbs' => [
                [
                    'label' => 'Permintaan Barang',
                    'url' => route('gudang.permintaanBarang.index'),
                ],
                [
                    'label' => 'Riwayat Permintaan Barang',
                    'url' => route('gudang.permintaanBarang.history'),
                ],
            ],
        ]);
    }

    // function untuk melihat detail order barang sebelum dilakukan acc oleh gudang
    public function show($id)
    {
        $order = PembangunanUnitBarangOrder::with([
            'details.barang',
            'details.rapBahan',
            'user',
            'qc',
            'pembangunanUnit.unit.blok',
            'pembangunanUnit.unit.type',
            'pembangunanUnit.tahap.perumahaan',
            'pembangunanUnit.pengawas',
        ])->findOrFail($id);

        return view('gudang.permintaan-barang.show', [
            'order' => $order,
            'breadcrumbs' => [
                [
                    'label' => 'Permintaan Barang',
                    'url' => route('gudang.permintaanBarang.index'),
                ],
                [
                    'label' => 'Detail Permintaan - REQ-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'url' => route('gudang.permintaanBarang.show', $order->id),
                ],
            ],
        ]);
    }

    public function acc(Request $request, $id)
    {
        // Ambil order beserta detail barang, master barang, dan pembangunan unit.
        // Relasi ini dibutuhkan untuk menentukan tipe barang, nama satuan, serta UBS/perumahan tujuan stock.
        $order = PembangunanUnitBarangOrder::with([
            'details.barang.baseUnit',
            'pembangunanUnit',
        ])->findOrFail($id);

        // ACC hanya boleh dilakukan sekali ketika order masih menunggu diproses gudang.
        // Ini mencegah stock dan FIFO nota barang masuk berkurang dua kali untuk order yang sama.
        if ($order->status_order !== 'diproses') {
            return back()->with('error', 'Permintaan barang ini sudah tidak dalam status menunggu.');
        }

        try {
            // Semua proses ACC dibuat atomic.
            // Kalau cek stock, FIFO, ledger, atau realisasi bahan gagal, seluruh perubahan otomatis rollback.
            DB::transaction(function () use ($order, $request) {
                $pembangunanUnit = $order->pembangunanUnit;

                // Di sistem ini id perumahaan dianggap sama dengan id UBS.
                // Jadi order dari perumahan 1 akan mengambil stock_gudang stock_type UBS dengan ubs_id 1.
                $ubsId = $pembangunanUnit?->perumahaan_id;

                // Tanpa data pembangunan unit/UBS, sistem tidak tahu stock unit mana yang harus dikurangi.
                if (!$pembangunanUnit || !$ubsId) {
                    throw new \Exception('Data pembangunan unit atau UBS/perumahan tidak ditemukan.');
                }

                // Proses setiap item order satu per satu.
                // Setiap detail akan dikonfirmasi, dihitung nilai FIFO-nya, lalu masuk realisasi bahan proyek.
                foreach ($order->details as $detail) {
                    $this->assertDetailMatchesOrderType($order, $detail);

                    // Jika ada detail yang sudah pernah dikonfirmasi, lewati agar tidak double pengurangan stock.
                    if ($detail->konfirmasi) {
                        continue;
                    }

                    // jumlah_base adalah kuantitas dalam satuan dasar barang.
                    // Stock gudang dan FIFO nota barang masuk dikurangi berdasarkan nilai base ini.
                    // Nilainya dihitung ulang dari master konversi agar order lama/frontend stale tidak membuat stock salah.
                    $jumlahBase = $this->resolveJumlahBase($detail);

                    // Default harga akan diganti sesuai tipe order.
                    // Stock mengambil harga dari FIFO nota, sedangkan direct mengambil input manual gudang.
                    $hargaTotal = 0.0;
                    $hargaSatuanBase = 0.0;

                    // Barang stock harus benar-benar keluar dari stock UBS dan mengonsumsi layer nota FIFO.
                    // Barang direct tetap masuk realisasi bahan, tetapi tidak mengurangi stock gudang.
                    if ($detail->barang?->is_stock) {
                        // Lock baris stock agar dua user tidak bisa ACC dan mengurangi stock yang sama bersamaan.
                        $stock = StockGudang::where('barang_id', $detail->barang_id)
                            ->where('stock_type', 'UBS')
                            ->where('ubs_id', $ubsId)
                            ->lockForUpdate()
                            ->first();

                        // Validasi stock UBS harus cukup sebelum nota FIFO dikurangi.
                        if (!$stock || (float) $stock->jumlah_stock < $jumlahBase) {
                            $namaBarang = $detail->nama_barang ?? $detail->barang?->nama_barang ?? 'Barang';
                            throw new \Exception("Stok UBS untuk {$namaBarang} tidak mencukupi.");
                        }

                        // Ambil harga aktual dari nota barang masuk paling lama yang masih punya jumlah_sisa.
                        // Helper ini juga langsung mengurangi jumlah_sisa pada layer nota yang dipakai.
                        $fifoResult = $this->consumeNotaFifo($detail->barang_id, $jumlahBase);
                        $hargaTotal = $fifoResult['harga_total'];
                        $hargaSatuanBase = $jumlahBase > 0 ? $hargaTotal / $jumlahBase : 0;

                        // Setelah FIFO valid, kurangi stock UBS sesuai jumlah base yang benar-benar keluar.
                        $stock->decrement('jumlah_stock', $jumlahBase);

                        // Simpan jejak keluar stock untuk audit riwayat stock.
                        StockLedger::create([
                            'tanggal' => now(),
                            'barang_id' => $detail->barang_id,
                            'stock_type' => 'UBS',
                            'ubs_id' => $ubsId,
                            'tipe' => 'keluar',
                            'ref_type' => 'PembangunanUnitBarangOrder',
                            'ref_id' => $order->id,
                            'qty_masuk' => 0,
                            'qty_keluar' => $jumlahBase,
                            'harga_satuan' => $hargaSatuanBase,
                            'created_by' => Auth::id(),
                        ]);
                    } else {
                        // Barang direct tidak punya sumber harga dari stock/nota.
                        // Karena itu total harga wajib diinput manual ketika ACC.
                        $hargaTotal = $this->resolveDirectHargaTotal($request, $detail);
                        $hargaSatuanBase = $jumlahBase > 0 ? $hargaTotal / $jumlahBase : 0;
                    }

                    // Simpan snapshot harga pada detail order.
                    // Walaupun harga disembunyikan di view gudang, data ini tetap dipakai laporan realisasi bahan.
                    $detail->update([
                        'konfirmasi' => true,
                        'jumlah_base' => $jumlahBase,
                        'harga_satuan_snapshot' => $hargaSatuanBase,
                        'harga_total_snapshot' => $hargaTotal,
                    ]);

                    // Masukkan barang ke realisasi bahan pembangunan unit.
                    // Jika barang yang sama pada QC yang sama sudah ada, jumlah dan harga diakumulasikan.
                    $this->upsertPembangunanUnitBahan($order, $detail, $hargaTotal);
                }

                // Jika semua detail berhasil diproses, order dinyatakan selesai.
                $order->update([
                    'status_order' => 'selesai',
                    'tanggal_selesai' => now(),
                ]);
            });
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal ACC permintaan barang: ' . $e->getMessage());
        }

        return redirect()
            ->route('gudang.permintaanBarang.history')
            ->with('success', 'Permintaan barang berhasil di-ACC.');
    }

    private function consumeNotaFifo(int $barangId, float $jumlahBase): array
    {
        // Sisa kuantitas yang harus diambil dari layer nota barang masuk.
        $remaining = $jumlahBase;

        // Total harga akan dihitung dari kombinasi beberapa layer FIFO bila satu layer tidak cukup.
        $hargaTotal = 0.0;

        // Ambil layer nota barang masuk yang sudah posted dan masih punya sisa.
        // FIFO mengikuti tanggal nota masuk, lalu id detail sebagai tie-breaker untuk nota di tanggal yang sama.
        $layers = NotaBarangMasukDetail::query()
            ->select('nota_barang_masuk_detail.*')
            ->join('nota_barang_masuk', 'nota_barang_masuk.id', '=', 'nota_barang_masuk_detail.nota_id')
            ->where('nota_barang_masuk_detail.barang_id', $barangId)
            ->where('nota_barang_masuk_detail.jumlah_sisa', '>', 0)
            ->where('nota_barang_masuk.status', 'posted')
            ->orderBy('nota_barang_masuk.tanggal_nota')
            ->orderBy('nota_barang_masuk_detail.id')
            ->lockForUpdate()
            ->get();

        // Pastikan total sisa nota cukup untuk memenuhi permintaan.
        // Kalau stock_gudang cukup tapi layer nota tidak cukup, transaksi tetap dibatalkan agar data tidak pincang.
        $available = (float) $layers->sum('jumlah_sisa');
        if ($available + 0.000001 < $jumlahBase) {
            $namaBarang = MasterBarang::where('id', $barangId)->value('nama_barang') ?? 'barang ini';
            throw new \Exception("Sisa nota barang masuk untuk {$namaBarang} tidak mencukupi.");
        }

        // Konsumsi setiap layer FIFO sampai kebutuhan jumlah base terpenuhi.
        foreach ($layers as $layer) {
            // Berhenti ketika seluruh kebutuhan sudah terpenuhi.
            if ($remaining <= 0.000001) {
                break;
            }

            // Ambil sebanyak mungkin dari layer saat ini, tetapi tidak melebihi kebutuhan tersisa.
            $takeQty = min((float) $layer->jumlah_sisa, $remaining);

            // harga_satuan_base adalah harga per satuan dasar barang.
            // Jika data lama belum punya nilai ini, fallback dihitung dari harga_total / jumlah_base.
            $hargaSatuanBase = (float) ($layer->harga_satuan_base ?: 0);

            if ($hargaSatuanBase <= 0 && (float) $layer->jumlah_base > 0) {
                $hargaSatuanBase = (float) $layer->harga_total / (float) $layer->jumlah_base;
            }

            // Tambahkan nilai harga dari layer ini ke total snapshot order.
            $hargaTotal += $takeQty * $hargaSatuanBase;

            // Kurangi sisa nota sesuai kuantitas yang dipakai proyek.
            $layer->update([
                'jumlah_sisa' => (float) $layer->jumlah_sisa - $takeQty,
            ]);

            // Update kebutuhan tersisa sebelum lanjut ke layer nota berikutnya.
            $remaining -= $takeQty;
        }

        // Harga dibulatkan dua desimal karena kolom database bertipe decimal(18,2).
        return [
            'harga_total' => round($hargaTotal, 2),
        ];
    }

    private function resolveJumlahBase($detail): float
    {
        // Ambil faktor konversi terbaru dari master satuan barang.
        // Contoh Cempolong: 3 ljr dengan konversi 4 akan menjadi 12 meter/base.
        $faktorKonversi = BarangSatuanKonversi::where('barang_id', $detail->barang_id)
            ->where('satuan_id', $detail->satuan_id)
            ->value('konversi_ke_base');

        if ($faktorKonversi === null) {
            return (float) $detail->jumlah_base;
        }

        return round((float) $detail->jumlah_input * (float) $faktorKonversi, 3);
    }

    // function untuk mendapatkan harga total dari inputan user
    private function resolveDirectHargaTotal(Request $request, $detail): float
    {
        $hargaTotal = $request->input("harga_total.{$detail->id}");

        if ($hargaTotal === null || $hargaTotal === '') {
            $namaBarang = $detail->nama_barang ?? $detail->barang?->nama_barang ?? 'Barang';
            throw new \Exception("Harga total untuk {$namaBarang} wajib diisi.");
        }

        $hargaTotal = (float) $hargaTotal;

        if ($hargaTotal < 0) {
            $namaBarang = $detail->nama_barang ?? $detail->barang?->nama_barang ?? 'Barang';
            throw new \Exception("Harga total untuk {$namaBarang} tidak boleh minus.");
        }

        return round($hargaTotal, 2);
    }

    // function untuk memastikan detail barang sesuai dengan jenis order
    private function assertDetailMatchesOrderType(PembangunanUnitBarangOrder $order, $detail): void
    {
        $barang = $detail->barang;

        if (!$barang) {
            throw new \Exception("Data master barang untuk {$detail->nama_barang} tidak ditemukan.");
        }

        $expectedStock = $order->jenis_order === 'stock';

        if ((bool) $barang->is_stock !== $expectedStock) {
            $jenis = $expectedStock ? 'stock' : 'direct';
            throw new \Exception("Barang {$detail->nama_barang} tidak sesuai dengan jenis order {$jenis}.");
        }
    }

    private function upsertPembangunanUnitBahan(PembangunanUnitBarangOrder $order, $detail, float $hargaTotal): void
    {
        // Cari realisasi bahan untuk proyek, QC, dan barang yang sama.
        // Kalau sudah ada, order berikutnya cukup menambah jumlah_pakai dan harga_total_snapshot.
        $bahan = PembangunanUnitBahan::where('pembangunan_unit_id', $order->pembangunan_unit_id)
            ->where('pembangunan_unit_qc_id', $order->pembangunan_unit_qc_id)
            ->where('barang_id', $detail->barang_id)
            ->first();

        // Akumulasi ke row realisasi bahan yang sudah ada.
        if ($bahan) {
            $bahan->update([
                'jumlah_pakai' => (float) $bahan->jumlah_pakai + (float) $detail->jumlah_input,
                'harga_total_snapshot' => (float) $bahan->harga_total_snapshot + $hargaTotal,
            ]);

            return;
        }

        // Buat row realisasi bahan baru bila barang ini belum pernah masuk pada QC tersebut.
        PembangunanUnitBahan::create([
            'pembangunan_unit_id' => $order->pembangunan_unit_id,
            'pembangunan_unit_qc_id' => $order->pembangunan_unit_qc_id,
            'barang_id' => $detail->barang_id,
            'nama_barang' => $detail->nama_barang ?? $detail->barang?->nama_barang ?? '-',
            'satuan' => $detail->satuan ?? $detail->barang?->baseUnit?->nama ?? '-',
            'jumlah_pakai' => (float) $detail->jumlah_input,
            'harga_total_snapshot' => $hargaTotal,
        ]);
    }
}
