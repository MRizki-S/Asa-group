<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\BarangRakitan;
use App\Models\MasterBarang;
use App\Models\NotaBarangMasuk;
use App\Models\NotaBarangMasukDetail;
use App\Models\ProduksiBarangRakitan;
use App\Models\ProduksiBarangRakitanFifo;
use App\Models\StockGudang;
use App\Models\StockLedger;
use App\Models\Ubs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProduksiRakitanController extends Controller
{
    public function index()
    {
        $baseQuery = ProduksiBarangRakitan::with([
            'barangHasil:id,kode_barang,nama_barang',
            'satuanHasil:id,nama',
            'ubs:id,nama,nama_ubs',
            'creator:id,username',
        ])->latest();

        $produksiActive = (clone $baseQuery)->where('status', 'active')->get();
        $produksiCancelled = (clone $baseQuery)->where('status', 'cancelled')->get();

        return view('gudang.barang-rakitan.produksi-rakitan.index', [
            'produksiActive' => $produksiActive,
            'produksiCancelled' => $produksiCancelled,
            'breadcrumbs' => [
                [
                    'label' => 'Produksi Rakitan',
                    'url' => route('gudang.produksiRakitan.index'),
                ],
            ],
        ]);
    }

    // function view create
    public function create()
    {
        $ubsList = Ubs::select(['id', 'nama_ubs', 'kode_ubs'])
            ->orderBy('nama_ubs')
            ->get();

        $komposisiRakitans = BarangRakitan::with([
            'barangHasil:id,kode_barang,nama_barang',
            'satuanHasil:id,nama',
            'details:id,barang_rakitan_id,barang_bahan_id,satuan_id,qty,qty_base',
            'details.barangBahan:id,kode_barang,nama_barang',
            'details.satuan:id,nama',
        ])
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (BarangRakitan $komposisi) {
                return [
                    'id' => $komposisi->id,
                    'label' => trim(($komposisi->barangHasil?->kode_barang ?? '-') . ' - ' . ($komposisi->barangHasil?->nama_barang ?? '-')),
                    'barang_hasil_id' => $komposisi->barang_hasil_id,
                    'barang_hasil_kode' => $komposisi->barangHasil?->kode_barang ?? '-',
                    'barang_hasil_nama' => $komposisi->barangHasil?->nama_barang ?? '-',
                    'satuan_hasil_id' => $komposisi->satuan_hasil_id,
                    'satuan_hasil_nama' => $komposisi->satuanHasil?->nama ?? '-',
                    'qty_hasil' => (float) $komposisi->qty_hasil,
                    'qty_hasil_base' => (float) $komposisi->qty_hasil_base,
                    'details' => $komposisi->details
                        ->map(function ($detail) {
                            return [
                                'barang_bahan_id' => $detail->barang_bahan_id,
                                'barang_bahan_kode' => $detail->barangBahan?->kode_barang ?? '-',
                                'barang_bahan_nama' => $detail->barangBahan?->nama_barang ?? '-',
                                'satuan_id' => $detail->satuan_id,
                                'satuan_nama' => $detail->satuan?->nama ?? '-',
                                'qty' => (float) $detail->qty,
                                'qty_base' => (float) $detail->qty_base,
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        $stockGudang = StockGudang::select(['barang_id', 'stock_type', 'ubs_id', 'jumlah_stock'])
            ->get()
            ->map(function (StockGudang $stock) {
                return [
                    'barang_id' => $stock->barang_id,
                    'stock_type' => $stock->stock_type,
                    'ubs_id' => $stock->ubs_id,
                    'jumlah_stock' => (float) $stock->jumlah_stock,
                ];
            })
            ->values();

        return view('gudang.barang-rakitan.produksi-rakitan.create', [
            'ubsList' => $ubsList,
            'komposisiRakitans' => $komposisiRakitans,
            'stockGudang' => $stockGudang,
            'breadcrumbs' => [
                [
                    'label' => 'Produksi Rakitan',
                    'url' => route('gudang.produksiRakitan.index'),
                ],
                [
                    'label' => 'Tambah',
                    'url' => '#',
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'tanggal_rakitan' => ['required', 'date'],
            'stock_type' => ['required', Rule::in(['HUB', 'UBS'])],
            'ubs_id' => ['nullable', 'required_if:stock_type,UBS', 'exists:ubs,id'],
            'barang_rakitan_id' => ['required', 'exists:barang_rakitan,id'],
            'qty_hasil' => ['required', 'numeric', 'min:0.001'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['stock_type'] === 'HUB') {
            $validated['ubs_id'] = null;
        }

        // Ambil data master komposisi
        $komposisi = BarangRakitan::with('details.barangBahan')
            ->where('status', 'active')
            ->findOrFail($validated['barang_rakitan_id']);

        // Ratio yield requested vs yield template
        $ratio = (float) $validated['qty_hasil'] / (float) $komposisi->qty_hasil;
        
        // Konversi hasil ke base unit
        $konversiHasilUnit = (float) $komposisi->qty_hasil_base / (float) $komposisi->qty_hasil;
        $qtyHasilBaseTotal = (float) $validated['qty_hasil'] * $konversiHasilUnit;

        try {
            DB::beginTransaction();

            // 2. Pengecekan Stock Bahan (dengan lockForUpdate)
            foreach ($komposisi->details as $detail) {
                $qtyPakaiBase = (float) $detail->qty_base * $ratio;
                
                $stock = StockGudang::where('barang_id', $detail->barang_bahan_id)
                    ->where('stock_type', $validated['stock_type'])
                    ->when($validated['stock_type'] === 'UBS', 
                        fn($q) => $q->where('ubs_id', $validated['ubs_id']),
                        fn($q) => $q->whereNull('ubs_id')
                    )
                    ->lockForUpdate()
                    ->first();

                if (!$stock || (float)$stock->jumlah_stock < $qtyPakaiBase) {
                    $namaBarang = $detail->barangBahan->nama_barang ?? 'Barang';
                    throw new \Exception("Stock barang [$namaBarang] tidak mencukupi.");
                }
            }

            // 3. Generate Nomor Rakitan BRK-XXXX
            $nomorRakitan = $this->generateNomorRakitan();

            // 4. Simpan Header Produksi (Status ACTIVE)
            $produksiRakitan = ProduksiBarangRakitan::create([
                'nomor_rakitan' => $nomorRakitan,
                'tanggal_rakitan' => $validated['tanggal_rakitan'],
                'stock_type' => $validated['stock_type'],
                'ubs_id' => $validated['ubs_id'],
                'barang_rakitan_id' => $komposisi->id,
                'barang_hasil_id' => $komposisi->barang_hasil_id,
                'satuan_hasil_id' => $komposisi->satuan_hasil_id,
                'qty_hasil' => $validated['qty_hasil'],
                'qty_hasil_base' => $qtyHasilBaseTotal,
                'total_biaya' => 0, // Akan diupdate nanti
                'harga_satuan' => 0,
                'harga_satuan_base' => 0,
                'status' => 'active',
                'keterangan' => $validated['keterangan'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $totalBiayaProduksi = 0;

            // 5. Proses Bahan (FIFO & Update Stock & Ledger Keluar)
            foreach ($komposisi->details as $detail) {
                $qtyPakaiDisp = (float) $detail->qty * $ratio;
                $qtyPakaiBase = (float) $detail->qty_base * $ratio;

                // A. Simpan Detail Produksi Bahan
                $prodDetail = $produksiRakitan->details()->create([
                    'barang_bahan_id' => $detail->barang_bahan_id,
                    'satuan_id' => $detail->satuan_id,
                    'qty_pakai' => $qtyPakaiDisp,
                    'qty_pakai_base' => $qtyPakaiBase,
                    'harga_total' => 0, // Akan diupdate oleh FIFO
                ]);

                // B. Kurangi Stock Gudang
                $stockBahan = StockGudang::where('barang_id', $detail->barang_bahan_id)
                    ->where('stock_type', $validated['stock_type'])
                    ->when($validated['stock_type'] === 'UBS', 
                        fn($q) => $q->where('ubs_id', $validated['ubs_id']),
                        fn($q) => $q->whereNull('ubs_id')
                    )
                    ->first(); // Sudah dilock di awal
                $stockBahan->decrement('jumlah_stock', $qtyPakaiBase);

                // C. Proses FIFO dari Nota Barang Masuk Detail
                $fifoCost = $this->consumeFIFO($detail->barang_bahan_id, $qtyPakaiBase, $prodDetail->id);
                $prodDetail->update(['harga_total' => $fifoCost]);
                $totalBiayaProduksi += $fifoCost;

                // D. Catat Stock Ledger Bahan Keluar
                StockLedger::create([
                    'tanggal' => $validated['tanggal_rakitan'],
                    'barang_id' => $detail->barang_bahan_id,
                    'stock_type' => $validated['stock_type'],
                    'ubs_id' => $validated['ubs_id'],
                    'tipe' => 'keluar',
                    'ref_type' => 'ProduksiBarangRakitan',
                    'ref_id' => $produksiRakitan->id,
                    'qty_masuk' => 0,
                    'qty_keluar' => $qtyPakaiBase,
                    'harga_satuan' => $qtyPakaiBase > 0 ? $fifoCost / $qtyPakaiBase : 0,
                    'created_by' => Auth::id(),
                ]);
            }

            // 6. Update Header dengan Biaya yang Terhitung
            $hargaSatuan = $totalBiayaProduksi / (float) $validated['qty_hasil'];
            $hargaSatuanBase = $totalBiayaProduksi / $qtyHasilBaseTotal;

            $produksiRakitan->update([
                'total_biaya' => $totalBiayaProduksi,
                'harga_satuan' => $hargaSatuan,
                'harga_satuan_base' => $hargaSatuanBase,
            ]);

            // 7. Tambah Stock Barang Hasil ke Gudang
            $stockHasil = StockGudang::firstOrCreate(
                [
                    'barang_id' => $komposisi->barang_hasil_id,
                    'stock_type' => $validated['stock_type'],
                    'ubs_id' => $validated['ubs_id'],
                ],
                ['jumlah_stock' => 0, 'minimal_stock' => 0]
            );
            $stockHasil->increment('jumlah_stock', $qtyHasilBaseTotal);

            // 8. Buat Nota Barang Masuk Internal (RKT-XXXX)
            $notaHasil = NotaBarangMasuk::create([
                'nomor_nota' => $this->generateNomorRKT(),
                'tanggal_nota' => $validated['tanggal_rakitan'],
                'supplier' => 'Produksi Rakitan Internal',
                'cara_bayar' => null,
                'status' => 'posted',
                'created_by' => Auth::id(),
                'posted_at' => now(),
            ]);

            // Link nota ke produksi
            $produksiRakitan->update(['nota_barang_masuk_id' => $notaHasil->id]);

            // Simpan detail nota untuk barang hasil
            $notaHasil->details()->create([
                'barang_id' => $komposisi->barang_hasil_id,
                'jumlah_input' => $validated['qty_hasil'],
                'satuan_id' => $komposisi->satuan_hasil_id,
                'jumlah_base' => $qtyHasilBaseTotal,
                'harga_satuan' => $hargaSatuan,
                'harga_satuan_base' => $hargaSatuanBase,
                'harga_total' => $totalBiayaProduksi,
                'jumlah_sisa' => $qtyHasilBaseTotal, // Tersedia untuk FIFO berikutnya
            ]);

            // 9. Catat Stock Ledger Barang Hasil Masuk
            StockLedger::create([
                'tanggal' => $validated['tanggal_rakitan'],
                'barang_id' => $komposisi->barang_hasil_id,
                'stock_type' => $validated['stock_type'],
                'ubs_id' => $validated['ubs_id'],
                'tipe' => 'masuk',
                'ref_type' => 'ProduksiBarangRakitan',
                'ref_id' => $produksiRakitan->id,
                'qty_masuk' => $qtyHasilBaseTotal,
                'qty_keluar' => 0,
                'harga_satuan' => $hargaSatuanBase,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()
                ->route('gudang.produksiRakitan.index')
                ->with('success', "Produksi rakitan $nomorRakitan berhasil disimpan.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memproses produksi: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $produksiRakitan = ProduksiBarangRakitan::with([
            'barangHasil',
            'satuanHasil',
            'ubs',
            'creator',
            'details.barangBahan',
            'details.satuan',
        ])->findOrFail($id);

        return view('gudang.barang-rakitan.produksi-rakitan.show', [
            'item' => $produksiRakitan,
            'breadcrumbs' => [
                [
                    'label' => 'Produksi Rakitan',
                    'url' => route('gudang.produksiRakitan.index'),
                ],
                [
                    'label' => 'Detail',
                    'url' => '#',
                ],
            ],
        ]);
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // 1. Ambil data produksi rakitan beserta relasi
            $produksiRakitan = ProduksiBarangRakitan::with([
                'details.fifoDetails',
                'notaBarangMasuk.details'
            ])
            ->lockForUpdate()
            ->findOrFail($id);

            // Pastikan hanya transaksi dengan status active yang boleh dibatalkan
            if ($produksiRakitan->status !== 'active') {
                throw new \Exception("Hanya produksi dengan status 'active' yang dapat dibatalkan.");
            }

            // 2. Validasi barang hasil rakitan belum pernah dipakai
            $notaHasil = $produksiRakitan->notaBarangMasuk;
            if ($notaHasil) {
                foreach ($notaHasil->details as $detailHasil) {
                    // Bandingkan jumlah_base dan jumlah_sisa
                    if ((float)$detailHasil->jumlah_sisa < (float)$detailHasil->jumlah_base) {
                        throw new \Exception("Produksi rakitan tidak dapat dibatalkan karena sebagian hasil rakitan sudah digunakan.");
                    }
                }
            }

            // 3. Kurangi stock barang hasil rakitan
            $stockHasil = StockGudang::where('barang_id', $produksiRakitan->barang_hasil_id)
                ->where('stock_type', $produksiRakitan->stock_type)
                ->when($produksiRakitan->stock_type === 'UBS', 
                    fn($q) => $q->where('ubs_id', $produksiRakitan->ubs_id),
                    fn($q) => $q->whereNull('ubs_id')
                )
                ->lockForUpdate()
                ->first();

            if (!$stockHasil || (float)$stockHasil->jumlah_stock < (float)$produksiRakitan->qty_hasil_base) {
                throw new \Exception("Stock barang hasil di gudang tidak mencukupi untuk pembatalan.");
            }

            $stockHasil->decrement('jumlah_stock', (float)$produksiRakitan->qty_hasil_base);

            // 4. Hapus nota internal hasil rakitan (karena tidak ada status 'cancelled' di tabel nota)
            if ($notaHasil) {
                // Hapus detailnya dulu
                $notaHasil->details()->delete();
                // Hapus headernya
                $notaHasil->delete();
                
                // Pastikan kolom nota_barang_masuk_id di header produksi di-null-kan 
                // (biasanya otomatis jika migrasi menggunakan nullOnDelete)
                $produksiRakitan->update(['nota_barang_masuk_id' => null]);
            }

            // 5. Kembalikan stock bahan & 7. Catat stock ledger reversal (bahan)
            foreach ($produksiRakitan->details as $detailBahan) {
                $stockBahan = StockGudang::where('barang_id', $detailBahan->barang_bahan_id)
                    ->where('stock_type', $produksiRakitan->stock_type)
                    ->when($produksiRakitan->stock_type === 'UBS', 
                        fn($q) => $q->where('ubs_id', $produksiRakitan->ubs_id),
                        fn($q) => $q->whereNull('ubs_id')
                    )
                    ->lockForUpdate()
                    ->first();

                if (!$stockBahan) {
                    $stockBahan = StockGudang::create([
                        'barang_id' => $detailBahan->barang_bahan_id,
                        'stock_type' => $produksiRakitan->stock_type,
                        'ubs_id' => $produksiRakitan->ubs_id,
                        'jumlah_stock' => 0,
                        'minimal_stock' => 0,
                    ]);
                }

                $stockBahan->increment('jumlah_stock', (float)$detailBahan->qty_pakai_base);

                // LEDGER BAHAN MASUK KEMBALI
                StockLedger::create([
                    'tanggal' => now(),
                    'barang_id' => $detailBahan->barang_bahan_id,
                    'stock_type' => $produksiRakitan->stock_type,
                    'ubs_id' => $produksiRakitan->ubs_id,
                    'tipe' => 'Masuk',
                    'ref_type' => 'ProduksiRakitanCancel',
                    'ref_id' => $produksiRakitan->id,
                    'qty_masuk' => $detailBahan->qty_pakai_base,
                    'qty_keluar' => 0,
                    'harga_satuan' => (float)$detailBahan->qty_pakai_base > 0 ? (float)$detailBahan->harga_total / (float)$detailBahan->qty_pakai_base : 0,
                    'created_by' => Auth::id(),
                ]);

                // 6. Kembalikan layer FIFO bahan
                foreach ($detailBahan->fifoDetails as $fifo) {
                    $layerNota = NotaBarangMasukDetail::findOrFail($fifo->nota_barang_masuk_detail_id);
                    $layerNota->increment('jumlah_sisa', (float)$fifo->qty_base_diambil);
                }
            }

            // 7. Catat stock ledger reversal (barang hasil)
            StockLedger::create([
                'tanggal' => now(),
                'barang_id' => $produksiRakitan->barang_hasil_id,
                'stock_type' => $produksiRakitan->stock_type,
                'ubs_id' => $produksiRakitan->ubs_id,
                'tipe' => 'Keluar',
                'ref_type' => 'ProduksiRakitanCancel',
                'ref_id' => $produksiRakitan->id,
                'qty_masuk' => 0,
                'qty_keluar' => $produksiRakitan->qty_hasil_base,
                'harga_satuan' => (float)$produksiRakitan->harga_satuan_base,
                'created_by' => Auth::id(),
            ]);

            // 8. Update produksi_barang_rakitan
            $produksiRakitan->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id(),
                'cancel_reason' => request('cancel_reason'),
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Produksi barang rakitan berhasil dibatalkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // Consume FIFO - Ambil stock dari Nota Barang Masuk yang paling lama (FIFO)
    private function consumeFIFO($barangId, $qtyNeeded, $detailId): float
    {
        $remaining = $qtyNeeded;
        $totalHarga = 0.0;

        // Ambil layer Nota Barang Masuk yang paling lama (FIFO)
        $layers = NotaBarangMasukDetail::query()
            ->join('nota_barang_masuk', 'nota_barang_masuk.id', '=', 'nota_barang_masuk_detail.nota_id')
            ->where('nota_barang_masuk_detail.barang_id', $barangId)
            ->where('nota_barang_masuk_detail.jumlah_sisa', '>', 0)
            ->where('nota_barang_masuk.status', 'posted')
            ->orderBy('nota_barang_masuk.tanggal_nota', 'asc')
            ->orderBy('nota_barang_masuk.id', 'asc')
            ->orderBy('nota_barang_masuk_detail.id', 'asc')
            ->select('nota_barang_masuk_detail.*')
            ->lockForUpdate()
            ->get();

        foreach ($layers as $layer) {
            if ($remaining <= 0) break;

            $take = min((float)$layer->jumlah_sisa, $remaining);
            
            // Hitung harga satuan base secara presisi dari total nota agar tidak terkena rounding error database
            $hargaSatuanBase = (float)$layer->harga_total / (float)$layer->jumlah_base;
            $subtotal = $take * $hargaSatuanBase;

            // Kurangi sisa di nota
            $layer->decrement('jumlah_sisa', $take);

            // Catat ke tabel FIFO rakitan
            ProduksiBarangRakitanFifo::create([
                'produksi_barang_rakitan_detail_id' => $detailId,
                'nota_barang_masuk_detail_id' => $layer->id,
                'qty_base_diambil' => $take,
                'harga_satuan_base' => $hargaSatuanBase,
                'harga_total' => $subtotal,
            ]);

            $totalHarga += $subtotal;
            $remaining -= $take;
        }

        if ($remaining > 0.000001) {
            $barang = MasterBarang::find($barangId);
            throw new \Exception("Stock FIFO barang [" . ($barang->nama_barang ?? $barangId) . "] tidak mencukupi untuk dicosting.");
        }

        return round($totalHarga, 2);
    }

    // Generate Nomor Barang Rakitan 
    private function generateNomorRakitan(): string
    {
        $prefix = 'BRK-';
        $last = ProduksiBarangRakitan::where('nomor_rakitan', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if (!$last) return $prefix . '0001';

        $lastNumber = (int) substr($last->nomor_rakitan, strlen($prefix));
        return $prefix . str_pad((string)($lastNumber + 1), 4, '0', STR_PAD_LEFT);
    }

    // Generate Nomor Nota Barang Masuk - Rakitan
    private function generateNomorRKT(): string
    {
        $prefix = 'RKT-';
        $last = NotaBarangMasuk::where('nomor_nota', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if (!$last) return $prefix . '0001';

        $lastNumber = (int) substr($last->nomor_nota, strlen($prefix));
        return $prefix . str_pad((string)($lastNumber + 1), 4, '0', STR_PAD_LEFT);
    }
}
