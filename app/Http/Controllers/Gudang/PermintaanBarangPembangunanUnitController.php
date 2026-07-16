<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\BarangSatuanKonversi;
use App\Models\MasterBarang;
use App\Models\NotaBarangMasukDetail;
use App\Models\PembangunanUnitBahan;
use App\Models\PembangunanUnitBarangFifoUsage;
use App\Models\PembangunanUnitBarangOrder;
use App\Models\StockGudang;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationPribadiService;

class PermintaanBarangPembangunanUnitController extends Controller
{
    protected NotificationPribadiService $notification;

    public function __construct(NotificationPribadiService $notification)
    {
        $this->notification = $notification;
    }

    public function accBarangOrder(Request $request, $id)
    {
        $order = PembangunanUnitBarangOrder::with([
            'details.barang.baseUnit',
            'details.rapBahan',
            'user',
            'qc',
            'pembangunanUnit.unit.blok',
            'pembangunanUnit.unit.type',
            'pembangunanUnit.tahap.perumahaan',
            'pembangunanUnit.pengawas',
        ])->findOrFail($id);

        if ($order->status_order !== 'diproses') {
            return back()->with('error', 'Permintaan barang unit ini sudah tidak dalam status menunggu.');
        }

        try {
            DB::transaction(function () use ($order, $request) {
                $this->processAcc($order, $request);
            });

            // Kirim notifikasi WA setelah transaksi berhasil commit
            $adminName = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Admin Gudang';
            $nomorOrder = $order->nomor_order ?? ('REQ-' . str_pad($order->id, 5, '0', STR_PAD_LEFT));
            $message = "✅ Orderan barang dengan No. Order *{$nomorOrder}* telah dikonfirmasi oleh Pihak Gudang (*{$adminName}*).";
            $targetGroup = env('FONNTE_ID_ORDER_BARANG_ABM');
            if (!empty($targetGroup)) {
                $this->notification->sendWhatsApp($targetGroup, $message);
            }
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal ACC permintaan barang unit: ' . $e->getMessage());
        }

        return redirect()
            ->route('gudang.permintaanBarang.history', ['jenis_order' => 'pembangunan_unit'])
            ->with('success', 'Permintaan barang unit berhasil di-ACC.');
    }

    // Proses ACC permintaan barang unit pembangunan
    private function processAcc(PembangunanUnitBarangOrder $order, Request $request): void
    {
        // Pastikan pembangunan unit dan UBS/perumahan tujuan stock ada
        $pembangunanUnit = $order->pembangunanUnit;
        $ubsId = $pembangunanUnit?->perumahaan_id;

        if (!$pembangunanUnit || !$ubsId) {
            throw new \Exception('Data pembangunan unit atau UBS/perumahan tujuan stock tidak ditemukan.');
        }

        if (!$order->pembangunan_unit_qc_id) {
            throw new \Exception('Data QC pembangunan unit tidak ditemukan.');
        }

        foreach ($order->details as $detail) {
            // Pastikan detail sesuai dengan jenis order (stock atau direct)
            $this->assertDetailMatchesOrderType($order, $detail);

            if ($detail->konfirmasi) {
                continue;
            }

            $jumlahBase = $this->resolveJumlahBase($detail);
            $hargaTotal = 0.0;
            $hargaSatuanBase = 0.0;

            if ($detail->barang?->is_stock) {
                $stock = StockGudang::where('barang_id', $detail->barang_id)
                    ->where('stock_type', 'UBS')
                    ->where('ubs_id', $ubsId)
                    ->lockForUpdate()
                    ->first();

                if (!$stock || (float) $stock->jumlah_stock < $jumlahBase) {
                    $namaBarang = $detail->nama_barang ?? $detail->barang?->nama_barang ?? 'Barang';
                    throw new \Exception("Stok UBS untuk {$namaBarang} tidak mencukupi.");
                }

                $fifoResult = $this->consumeNotaFifo($detail->barang_id, $jumlahBase);
                $hargaTotal = $fifoResult['harga_total'];
                $hargaSatuanBase = $jumlahBase > 0 ? $hargaTotal / $jumlahBase : 0;

                // Simpan setiap layer FIFO yang dipakai ke tabel fifo_usage
                foreach ($fifoResult['layers'] as $layer) {
                    PembangunanUnitBarangFifoUsage::create([
                        'order_detail_id' => $detail->id,
                        'nota_barang_masuk_detail_id' => $layer['nota_barang_masuk_detail_id'],
                        'jumlah_base' => $layer['jumlah_base'],
                        'jumlah_return_base' => 0,
                        'harga_satuan_snapshot' => $layer['harga_satuan_snapshot'],
                        'harga_total_snapshot' => $layer['harga_total_snapshot'],
                    ]);
                }

                $stock->decrement('jumlah_stock', $jumlahBase);

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
                $hargaTotal = $this->resolveDirectHargaTotal($request, $detail);
                $hargaSatuanBase = $jumlahBase > 0 ? $hargaTotal / $jumlahBase : 0;
            }

            $detail->update([
                'konfirmasi' => true,
                'jumlah_base' => $jumlahBase,
                'harga_satuan_snapshot' => $hargaSatuanBase,
                'harga_total_snapshot' => $hargaTotal,
            ]);

            $this->upsertPembangunanUnitBahan($order, $detail, $hargaTotal);
        }

        $order->update([
            'status_order' => 'selesai',
            'tanggal_selesai' => now(),
        ]);
    }

    // Consume nota barang masuk using FIFO method
    private function consumeNotaFifo(int $barangId, float $jumlahBase): array
    {
        $remaining = $jumlahBase;
        $hargaTotal = 0.0;
        $usedLayers = [];

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

        $available = (float) $layers->sum('jumlah_sisa');
        if ($available + 0.000001 < $jumlahBase) {
            $namaBarang = MasterBarang::where('id', $barangId)->value('nama_barang') ?? 'barang ini';
            throw new \Exception("Sisa nota barang masuk untuk {$namaBarang} tidak mencukupi.");
        }

        foreach ($layers as $layer) {
            if ($remaining <= 0.000001) {
                break;
            }

            $takeQty = min((float) $layer->jumlah_sisa, $remaining);
            $hargaSatuanBase = (float) ($layer->harga_satuan_base ?: 0);

            if ($hargaSatuanBase <= 0 && (float) $layer->jumlah_base > 0) {
                $hargaSatuanBase = (float) $layer->harga_total / (float) $layer->jumlah_base;
            }

            $layerHargaTotal = round($takeQty * $hargaSatuanBase, 2);
            $hargaTotal += $layerHargaTotal;

            $layer->update([
                'jumlah_sisa' => (float) $layer->jumlah_sisa - $takeQty,
            ]);

            // Catat layer yang dipakai untuk INSERT ke fifo_usage
            $usedLayers[] = [
                'nota_barang_masuk_detail_id' => $layer->id,
                'jumlah_base' => $takeQty,
                'harga_satuan_snapshot' => $hargaSatuanBase,
                'harga_total_snapshot' => $layerHargaTotal,
            ];

            $remaining -= $takeQty;
        }

        return [
            'harga_total' => round($hargaTotal, 2),
            'layers' => $usedLayers,
        ];
    }

    // Resolve jumlah base from detail, considering konversi if applicable
    private function resolveJumlahBase($detail): float
    {
        $faktorKonversi = BarangSatuanKonversi::where('barang_id', $detail->barang_id)
            ->where('satuan_id', $detail->satuan_id)
            ->value('konversi_ke_base');

        if ($faktorKonversi === null) {
            return (float) $detail->jumlah_base;
        }

        return round((float) $detail->jumlah_input * (float) $faktorKonversi, 3);
    }

    // Resolve harga total for direct order type
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

    // Upsert data bahan pembangunan unit
    private function upsertPembangunanUnitBahan(PembangunanUnitBarangOrder $order, $detail, float $hargaTotal): void
    {
        $bahan = PembangunanUnitBahan::where('pembangunan_unit_id', $order->pembangunan_unit_id)
            ->where('pembangunan_unit_qc_id', $order->pembangunan_unit_qc_id)
            ->where('barang_id', $detail->barang_id)
            ->first();

        if ($bahan) {
            $bahan->update([
                'jumlah_pakai' => (float) $bahan->jumlah_pakai + (float) $detail->jumlah_input,
                'harga_total_snapshot' => (float) $bahan->harga_total_snapshot + $hargaTotal,
            ]);

            return;
        }

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
