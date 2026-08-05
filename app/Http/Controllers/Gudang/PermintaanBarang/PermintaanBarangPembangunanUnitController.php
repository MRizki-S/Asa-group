<?php

namespace App\Http\Controllers\Gudang\PermintaanBarang;

use App\Http\Controllers\Controller;
use App\Models\BarangRusak;
use App\Models\BarangSatuanKonversi;
use App\Models\MasterBarang;
use App\Models\NotaBarangMasukDetail;
use App\Models\PembangunanUnitBahan;
use App\Models\PembangunanUnitBarangFifoUsage;
use App\Models\PembangunanUnitBarangOrder;
use App\Models\PembangunanUnitBarangOrderDetail;
use App\Models\PembangunanUnitBarangReturn;
use App\Models\PembangunanUnitBarangReturnDetail;
use App\Models\PembangunanUnitBarangReturnFifo;
use App\Models\StockGudang;
use App\Models\StockLedger;
use App\Models\Ubs;
use App\Services\NotificationPribadiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            $unit = $order->pembangunanUnit?->unit;
            $namaPerumahan = $unit?->tahap?->perumahaan?->nama_perumahaan ?? '-';
            $namaTahap = $unit?->tahap?->nama_tahap ?? '-';
            $namaUnit = $unit?->nama_unit ?? '-';

            $targetGroup = env('FONNTE_ID_GROUP_ACC_ORDER_BARANG_UNIT', env('FONNTE_ID_GROUP_ORDER_BARANG_UNIT', env('FONNTE_ID_ORDER_BARANG_ABM')));
            if (!empty($targetGroup)) {
                $message = view('notifications.whatsapp.pembangunan_unit.acc_order_barang', [
                    'order' => $order,
                    'namaPerumahan' => $namaPerumahan,
                    'namaTahap' => $namaTahap,
                    'namaUnit' => $namaUnit,
                    'adminGudang' => $adminName,
                    'tanggalAcc' => now()->format('d/m/Y H:i') . ' WIB',
                ])->render();
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

    public function tolakBarangOrder(Request $request, $id)
    {
        $request->validate(['catatan' => 'nullable|string|max:1000']);

        $order = PembangunanUnitBarangOrder::with([
            'details',
            'qc',
            'pembangunanUnit.unit.tahap.perumahaan'
        ])->findOrFail($id);

        if ($order->status_order !== 'diproses') {
            return back()->with('error', 'Permintaan barang ini sudah tidak dalam status menunggu.');
        }

        $order->update([
            'status_order' => 'ditolak',
            'catatan'      => $request->catatan,
        ]);

        // Kirim notifikasi WA penolakan order barang
        $targetGroup = env('FONNTE_ID_GROUP_TOLAK_ORDER_BARANG_UNIT', env('FONNTE_ID_GROUP_ORDER_BARANG_UNIT', env('FONNTE_ID_ORDER_BARANG_ABM')));
        if (!empty($targetGroup)) {
            $adminName = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Admin Gudang';
            $unit = $order->pembangunanUnit?->unit;
            $message = view('notifications.whatsapp.pembangunan_unit.tolak_order_barang', [
                'order' => $order,
                'namaPerumahan' => $unit?->tahap?->perumahaan?->nama_perumahaan ?? '-',
                'namaTahap' => $unit?->tahap?->nama_tahap ?? '-',
                'namaUnit' => $unit?->nama_unit ?? '-',
                'adminGudang' => $adminName,
                'tanggal' => now()->format('d/m/Y H:i') . ' WIB',
            ])->render();
            $this->notification->sendWhatsApp($targetGroup, $message);
        }

        return back()->with('success', 'Permintaan barang berhasil ditolak.');
    }

    public function resubmitBarangOrder(Request $request, $id)
    {
        $request->validate(['catatan' => 'nullable|string|max:1000']);

        $order = PembangunanUnitBarangOrder::with([
            'details',
            'user',
            'qc',
            'pembangunanUnit.unit.tahap.perumahaan'
        ])->findOrFail($id);

        if ($order->status_order !== 'ditolak') {
            return back()->with('error', 'Hanya permintaan yang ditolak yang dapat diajukan kembali.');
        }

        $order->update([
            'status_order'    => 'diproses',
            'catatan'         => $request->catatan,
            'tanggal_diajukan' => now(),
        ]);

        // Kirim notifikasi WA resubmit order barang
        $targetGroup = env('FONNTE_ID_GROUP_RESUBMIT_ORDER_BARANG_UNIT', env('FONNTE_ID_GROUP_ORDER_BARANG_UNIT', env('FONNTE_ID_ORDER_BARANG_ABM')));
        if (!empty($targetGroup)) {
            $pengaju = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Pengaju';
            $unit = $order->pembangunanUnit?->unit;
            $message = view('notifications.whatsapp.pembangunan_unit.resubmit_order_barang', [
                'order' => $order,
                'namaPerumahan' => $unit?->tahap?->perumahaan?->nama_perumahaan ?? '-',
                'namaTahap' => $unit?->tahap?->nama_tahap ?? '-',
                'namaUnit' => $unit?->nama_unit ?? '-',
                'namaQc' => $order->qc?->nama_qc ?? '-',
                'pengaju' => $pengaju,
                'tanggal' => now()->format('d/m/Y H:i') . ' WIB',
            ])->render();
            $this->notification->sendWhatsApp($targetGroup, $message);
        }

        return back()->with('success', 'Permintaan barang berhasil diajukan kembali.');
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
            'acc_by' => Auth::id(),
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
        $baseUnitName = $detail->barang?->baseUnit?->nama ?? ($detail->satuanModel?->nama ?? ($detail->satuan ?? '-'));

        $bahan = PembangunanUnitBahan::where('pembangunan_unit_id', $order->pembangunan_unit_id)
            ->where('pembangunan_unit_qc_id', $order->pembangunan_unit_qc_id)
            ->where('barang_id', $detail->barang_id)
            ->first();

        if ($bahan) {
            $bahan->update([
                'jumlah_pakai' => (float) $bahan->jumlah_pakai + (float) $detail->jumlah_base,
                'harga_total_snapshot' => (float) $bahan->harga_total_snapshot + $hargaTotal,
            ]);

            return;
        }

        PembangunanUnitBahan::create([
            'pembangunan_unit_id' => $order->pembangunan_unit_id,
            'pembangunan_unit_qc_id' => $order->pembangunan_unit_qc_id,
            'barang_id' => $detail->barang_id,
            'nama_barang' => $detail->nama_barang ?? $detail->barang?->nama_barang ?? '-',
            'satuan' => $baseUnitName,
            'jumlah_pakai' => (float) $detail->jumlah_base,
            'harga_total_snapshot' => $hargaTotal,
        ]);
    }

    // Process Gudang ACC for Return Barang Unit
    public function accBarangReturn(Request $request, $id)
    {
        $return = PembangunanUnitBarangReturn::with([
            'details.barang',
            'pembangunanUnit.unit.tahap.perumahaan',
            'pembangunanUnit.perumahaan'
        ])->findOrFail($id);

        if ($return->status !== 'diproses') {
            return back()->with('error', 'Status pengajuan retur barang ini tidak dalam status diproses.');
        }

        try {
            DB::transaction(function () use ($return, $request) {
                $pembangunanUnit = $return->pembangunanUnit;
                $perumahan = $pembangunanUnit?->perumahaan ?? $pembangunanUnit?->unit?->tahap?->perumahaan;
                $ubsId = $perumahan?->nama_perumahaan
                    ? Ubs::where('nama_ubs', $perumahan->nama_perumahaan)->value('id')
                    : ($pembangunanUnit?->perumahaan_id);

                if (!$ubsId) {
                    throw new \Exception('UBS / Perumahan tujuan retur barang tidak ditemukan.');
                }

                $qcId = $return->pembangunan_unit_qc_id;
                $notaRtnId = null;
                $notaRtnTotal = 0.0;

                foreach ($return->details as $detail) {
                    $itemInput = collect($request->input('items', []))->firstWhere('id', $detail->id);

                    $layakInput = isset($itemInput['jumlah_layak_input'])
                        ? (float)$itemInput['jumlah_layak_input']
                        : (isset($itemInput['jumlah_layak_base']) ? (float)$itemInput['jumlah_layak_base'] : (float)$detail->jumlah_base);

                    $rusakInput = isset($itemInput['jumlah_rusak_input'])
                        ? (float)$itemInput['jumlah_rusak_input']
                        : (isset($itemInput['jumlah_rusak_base']) ? (float)$itemInput['jumlah_rusak_base'] : 0.0);

                    if ($layakInput < 0 || $rusakInput < 0) {
                        throw new \Exception("Jumlah barang layak dan rusak untuk {$detail->nama_barang} tidak boleh bernilai negatif.");
                    }                    $satuanInputId = isset($itemInput['satuan_id']) ? (int)$itemInput['satuan_id'] : $detail->satuan_id;

                    $faktor = 1.0;
                    if ($detail->barang && $detail->barang->base_unit_id == $satuanInputId) {
                        $faktor = 1.0;
                    } else {
                        $faktor = BarangSatuanKonversi::where('barang_id', $detail->barang_id)
                            ->where('satuan_id', $satuanInputId)
                            ->value('konversi_ke_base') ?? 1.0;
                    }

                    $jumlahLayakBase = round($layakInput * $faktor, 4);
                    $jumlahRusakBase = round($rusakInput * $faktor, 4);

                    if (($jumlahLayakBase + $jumlahRusakBase) > ((float)$detail->jumlah_base + 0.001)) {
                        throw new \Exception("Jumlah barang layak + rusak untuk {$detail->nama_barang} tidak boleh melebihi jumlah base (" . (float)$detail->jumlah_base . ").");
                    }

                    if (abs(($jumlahLayakBase + $jumlahRusakBase) - (float)$detail->jumlah_base) > 0.001) {
                        throw new \Exception("Jumlah layak + rusak untuk barang {$detail->nama_barang} harus persis sama dengan total return (" . (float)$detail->jumlah_base . ").");
                    }

                    // 1. Find FIFO usage records for this barang & QC, ordered by FIFO (oldest order)
                    $fifoUsages = PembangunanUnitBarangFifoUsage::query()
                        ->join('pembangunan_unit_barang_order_detail as od', 'od.id', '=', 'pembangunan_unit_barang_fifo_usage.order_detail_id')
                        ->join('pembangunan_unit_barang_order as o', 'o.id', '=', 'od.order_id')
                        ->where('o.pembangunan_unit_qc_id', $qcId)
                        ->where('o.status_order', 'selesai')
                        ->where('od.barang_id', $detail->barang_id)
                        ->whereRaw('pembangunan_unit_barang_fifo_usage.jumlah_base > pembangunan_unit_barang_fifo_usage.jumlah_return_base')
                        ->orderBy('o.tanggal_diajukan', 'asc')
                        ->orderBy('pembangunan_unit_barang_fifo_usage.id', 'asc')
                        ->select('pembangunan_unit_barang_fifo_usage.*', 'od.id as order_detail_id')
                        ->lockForUpdate()
                        ->get();

                    $remainingReturn = (float)$detail->jumlah_base;
                    $remainingLayak = $jumlahLayakBase;
                    $remainingRusak = $jumlahRusakBase;
                    $totalHargaReturnDetail = 0.0;

                    foreach ($fifoUsages as $fifoUsage) {
                        if ($remainingReturn <= 0.0001) break;

                        $sisaInUsage = (float)$fifoUsage->jumlah_base - (float)$fifoUsage->jumlah_return_base;
                        $takeQty = min($sisaInUsage, $remainingReturn);

                        $takeLayak = min($takeQty, $remainingLayak);
                        $takeRusak = $takeQty - $takeLayak;

                        $hargaSatuanSnapshot = (float)$fifoUsage->harga_satuan_snapshot;
                        $hargaTotalSnapshot = round($takeQty * $hargaSatuanSnapshot, 2);
                        $totalHargaReturnDetail += $hargaTotalSnapshot;

                        PembangunanUnitBarangReturnFifo::create([
                            'return_detail_id' => $detail->id,
                            'fifo_usage_id' => $fifoUsage->id,
                            'jumlah_base' => $takeQty,
                            'jumlah_return_base' => $takeQty,
                            'jumlah_layak_base' => $takeLayak,
                            'jumlah_rusak_base' => $takeRusak,
                            'harga_satuan_snapshot' => $hargaSatuanSnapshot,
                            'harga_total_snapshot' => $hargaTotalSnapshot,
                        ]);

                        $fifoUsage->increment('jumlah_return_base', $takeQty);

                        $orderDetail = \App\Models\PembangunanUnitBarangOrderDetail::find($fifoUsage->order_detail_id);
                        if ($orderDetail) {
                            $orderDetail->increment('jumlah_return_base', $takeQty);
                            $orderDetail->increment('jumlah_return', round($takeQty / $faktor, 3));
                        }

                        $remainingReturn -= $takeQty;
                        $remainingLayak -= $takeLayak;
                        $remainingRusak -= $takeRusak;
                    }

                    if ($remainingReturn > 0.001) {
                        throw new \Exception("Data pemakaian FIFO tidak mencukupi untuk melakukan return barang {$detail->nama_barang}.");
                    }

                    $avgHargaSatuan = (float)$detail->jumlah_base > 0 ? $totalHargaReturnDetail / (float)$detail->jumlah_base : 0;
                    $detail->update([
                        'jumlah_layak_base' => $jumlahLayakBase,
                        'jumlah_rusak_base' => $jumlahRusakBase,
                        'harga_satuan_snapshot' => $avgHargaSatuan,
                        'harga_total_snapshot' => $totalHargaReturnDetail,
                    ]);

                    // 5. Kurangi Termin (pembangunan_unit_bahan)
                    $bahan = PembangunanUnitBahan::where('pembangunan_unit_id', $return->pembangunan_unit_id)
                        ->where('pembangunan_unit_qc_id', $qcId)
                        ->where('barang_id', $detail->barang_id)
                        ->first();

                    if ($bahan) {
                        $newJumlahPakai = max(0, (float)$bahan->jumlah_pakai - (float)$detail->jumlah_base);
                        $newHargaTotal = max(0, (float)$bahan->harga_total_snapshot - $totalHargaReturnDetail);
                        $bahan->update([
                            'jumlah_pakai' => $newJumlahPakai,
                            'harga_total_snapshot' => $newHargaTotal,
                        ]);
                    }

                    // 6 & 7. Barang Layak -> Nota RTN, Stock Gudang, Stock Ledger
                    if ($jumlahLayakBase > 0) {
                        if (!$notaRtnId) {
                            $datePrefixNota = 'RTN-' . now()->format('Ymd') . '-';
                            $lastNota = DB::table('nota_barang_masuk')
                                ->where('nomor_nota', 'like', $datePrefixNota . '%')
                                ->orderBy('id', 'desc')
                                ->first();
                            $seq = $lastNota ? ((int)substr($lastNota->nomor_nota, strlen($datePrefixNota)) + 1) : 1;
                            $nomorNota = $datePrefixNota . str_pad($seq, 4, '0', STR_PAD_LEFT);

                            $notaRtnId = DB::table('nota_barang_masuk')->insertGetId([
                                'nomor_nota'   => $nomorNota,
                                'tanggal_nota' => now()->format('Y-m-d'),
                                'supplier'     => "Return Proyek Unit #{$return->pembangunan_unit_id} ({$return->nomor_return})",
                                'cara_bayar'   => 'cash',
                                'status'       => 'posted',
                                'created_by'   => Auth::id(),
                                'posted_at'    => now(),
                                'created_at'   => now(),
                                'updated_at'   => now(),
                            ]);
                        }

                        $layakHargaTotal = round($jumlahLayakBase * $avgHargaSatuan, 2);
                        $notaRtnTotal += $layakHargaTotal;

                        DB::table('nota_barang_masuk_detail')->insert([
                            'nota_id'           => $notaRtnId,
                            'barang_id'         => $detail->barang_id,
                            'jumlah_input'      => round($jumlahLayakBase / $faktor, 3),
                            'satuan_id'         => $satuanInputId,
                            'jumlah_base'       => $jumlahLayakBase,
                            'jumlah_sisa'       => $jumlahLayakBase,
                            'harga_satuan'      => round($avgHargaSatuan * $faktor, 2),
                            'harga_satuan_base' => $avgHargaSatuan,
                            'harga_total'       => $layakHargaTotal,
                            'created_at'        => now(),
                            'updated_at'        => now(),
                        ]);

                        // Barang Layak -> Masuk HUB PUSAT (stock_type = 'HUB', ubs_id = null)
                        $stock = StockGudang::where('barang_id', $detail->barang_id)
                            ->where('stock_type', 'HUB')
                            ->lockForUpdate()
                            ->first();

                        if ($stock) {
                            $stock->increment('jumlah_stock', $jumlahLayakBase);
                        } else {
                            StockGudang::create([
                                'barang_id' => $detail->barang_id,
                                'stock_type' => 'HUB',
                                'ubs_id' => null,
                                'jumlah_stock' => $jumlahLayakBase,
                            ]);
                        }

                        StockLedger::create([
                            'tanggal' => now(),
                            'barang_id' => $detail->barang_id,
                            'stock_type' => 'HUB',
                            'ubs_id' => null,
                            'tipe' => 'masuk',
                            'ref_type' => 'PembangunanUnitBarangReturn',
                            'ref_id' => $return->id,
                            'qty_masuk' => $jumlahLayakBase,
                            'qty_keluar' => 0,
                            'harga_satuan' => $avgHargaSatuan,
                            'created_by' => Auth::id(),
                        ]);
                    }

                    // 8. Barang Rusak -> insert into barang_rusak
                    if ($jumlahRusakBase > 0) {
                        $nomorBr = 'BR-' . now()->format('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(5));
                        BarangRusak::create([
                            'nomor_barang_rusak' => $nomorBr,
                            'tgl_rusak' => now(),
                            'stock_type' => 'UBS',
                            'ubs_id' => $ubsId,
                            'barang_id' => $detail->barang_id,
                            'satuan_id' => $satuanInputId,
                            'qty_out' => round($jumlahRusakBase / $faktor, 3),
                            'qty_base' => $jumlahRusakBase,
                            'status' => 'posted',
                            'keterangan' => "Barang Rusak dari Retur Unit #{$return->pembangunan_unit_id} ({$return->nomor_return})",
                            'created_by' => Auth::id(),
                            'posted_at' => now(),
                        ]);
                    }
                }

                // 9. Update status return -> selesai
                $return->update([
                    'status' => 'selesai',
                    'acc_by' => Auth::id(),
                    'acc_at' => now(),
                ]);
            });

            // Kirim notifikasi WA setelah transaction commit
            $adminName = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Admin Gudang';
            $unit = $return->pembangunanUnit?->unit;
            $namaPerumahan = $unit?->tahap?->perumahaan?->nama_perumahaan ?? '-';
            $namaTahap = $unit?->tahap?->nama_tahap ?? '-';
            $namaUnit = $unit?->nama_unit ?? '-';

            $targetGroup = env('FONNTE_ID_GROUP_ACC_RETUR_BARANG_UNIT', env('FONNTE_ID_GROUP_RETUR_BARANG_UNIT', env('FONNTE_ID_ORDER_BARANG_ABM')));
            if (!empty($targetGroup)) {
                $message = view('notifications.whatsapp.pembangunan_unit.acc_retur_barang', [
                    'return' => $return,
                    'namaPerumahan' => $namaPerumahan,
                    'namaTahap' => $namaTahap,
                    'namaUnit' => $namaUnit,
                    'adminGudang' => $adminName,
                    'tanggalAcc' => now()->format('d/m/Y H:i') . ' WIB',
                ])->render();
                $this->notification->sendWhatsApp($targetGroup, $message);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal ACC retur barang: ' . $e->getMessage());
        }

        return back()->with('success', 'Pengajuan retur barang berhasil di-ACC.');
    }

    public function showReturn($id)
    {
        $return = PembangunanUnitBarangReturn::with([
            'pembangunanUnit.unit',
            'pembangunanUnit.tahap.perumahaan',
            'pembangunanUnit.pengawas',
            'qc',
            'createdBy',
            'accBy',
            'details.barang.baseUnit',
            'details.barang.satuanKonversi.satuan',
            'details.satuanModel',
        ])->findOrFail($id);

        foreach ($return->details as $det) {
            $options = collect();

            if ($det->barang && $det->barang->baseUnit) {
                $options->push([
                    'id' => $det->barang->base_unit_id,
                    'nama' => $det->barang->baseUnit->nama,
                    'faktor' => 1.0,
                ]);
            }

            if ($det->barang && $det->barang->satuanKonversi) {
                foreach ($det->barang->satuanKonversi as $konv) {
                    if ($konv->satuan) {
                        $options->push([
                            'id' => $konv->satuan_id,
                            'nama' => $konv->satuan->nama,
                            'faktor' => (float)$konv->konversi_ke_base,
                        ]);
                    }
                }
            }

            if ($det->satuan_id && !$options->contains('id', $det->satuan_id)) {
                $options->push([
                    'id' => $det->satuan_id,
                    'nama' => $det->satuan,
                    'faktor' => 1.0,
                ]);
            }

            $det->satuan_options = $options->unique('id')->values()->toArray();
        }

        return view('gudang.return-barang.show', [
            'category' => 'pembangunan_unit',
            'return' => $return,
            'breadcrumbs' => [
                [
                    'label' => 'Material Proyek',
                    'url' => '#',
                ],
                [
                    'label' => 'Retur Barang Unit',
                    'url' => route('gudang.returnBarang.unit.index'),
                ],
                [
                    'label' => 'Detail Retur #' . $return->nomor_return,
                    'url' => route('gudang.returnBarang.unit.show', $return->id),
                ],
            ],
        ]);
    }

    // Process Gudang Reject for Return Barang Unit
    public function rejectBarangReturn(Request $request, $id)
    {
        $return = PembangunanUnitBarangReturn::findOrFail($id);

        if ($return->status !== 'diproses') {
            return back()->with('error', 'Status retur tidak dalam status diproses.');
        }

        $request->validate([
            'alasan_tolak' => 'required|string|max:1000',
        ]);

        $return->update([
            'status' => 'ditolak',
            'alasan_tolak' => $request->alasan_tolak,
            'acc_by' => Auth::id(),
            'acc_at' => now(),
        ]);

        $adminName = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Admin Gudang';
        $unit = $return->pembangunanUnit?->unit;
        $namaPerumahan = $unit?->tahap?->perumahaan?->nama_perumahaan ?? '-';
        $namaTahap = $unit?->tahap?->nama_tahap ?? '-';
        $namaUnit = $unit?->nama_unit ?? '-';

        $targetGroup = env('FONNTE_ID_GROUP_TOLAK_RETUR_BARANG_UNIT', env('FONNTE_ID_GROUP_RETUR_BARANG_UNIT', env('FONNTE_ID_ORDER_BARANG_ABM')));
        if (!empty($targetGroup)) {
            $message = view('notifications.whatsapp.pembangunan_unit.tolak_retur_barang', [
                'return' => $return,
                'namaPerumahan' => $namaPerumahan,
                'namaTahap' => $namaTahap,
                'namaUnit' => $namaUnit,
                'adminGudang' => $adminName,
                'alasanTolak' => $request->alasan_tolak,
                'tanggal' => now()->format('d/m/Y H:i') . ' WIB',
            ])->render();
            $this->notification->sendWhatsApp($targetGroup, $message);
        }

        return back()->with('success', 'Pengajuan retur barang berhasil ditolak.');
    }

    public function indexReturn(Request $request)
    {
        $status = $request->query('status', 'diproses');

        $query = PembangunanUnitBarangReturn::with([
            'pembangunanUnit.unit',
            'pembangunanUnit.tahap.perumahaan',
            'qc',
            'createdBy',
            'details'
        ])
        ->withCount('details');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $returns = $query->latest()->get();

        return view('gudang.return-barang.index', [
            'category' => 'pembangunan_unit',
            'titlePage' => 'Konfirmasi Retur Barang Unit',
            'returns' => $returns,
            'status' => $status,
            'isHistory' => false,
            'statusOptions' => [
                'diproses' => 'Menunggu',
                'selesai' => 'Selesai',
                'ditolak' => 'Ditolak',
            ],
            'breadcrumbs' => [
                [
                    'label' => 'Material Proyek',
                    'url' => '#',
                ],
                [
                    'label' => 'Retur Barang Unit',
                    'url' => route('gudang.returnBarang.unit.index'),
                ],
            ],
        ]);
    }

    public function historyReturn(Request $request)
    {
        $status = $request->query('status', 'all');

        $query = PembangunanUnitBarangReturn::with([
            'pembangunanUnit.unit',
            'pembangunanUnit.tahap.perumahaan',
            'qc',
            'createdBy',
            'accBy',
            'details'
        ])
        ->withCount('details')
        ->whereIn('status', ['selesai', 'ditolak']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $returns = $query->latest()->get();

        return view('gudang.return-barang.index', [
            'category' => 'pembangunan_unit',
            'titlePage' => 'Riwayat Retur Barang Unit',
            'returns' => $returns,
            'status' => $status,
            'isHistory' => true,
            'statusOptions' => [
                'selesai' => 'Selesai',
                'ditolak' => 'Ditolak',
            ],
            'breadcrumbs' => [
                [
                    'label' => 'Material Proyek',
                    'url' => '#',
                ],
                [
                    'label' => 'Retur Barang Unit',
                    'url' => route('gudang.returnBarang.unit.index'),
                ],
                [
                    'label' => 'Riwayat Retur',
                    'url' => route('gudang.returnBarang.unit.history'),
                ],
            ],
        ]);
    }

    protected function currentPerumahaanId()
    {
        $user = Auth::user();
        return $user->is_global ? session('current_perumahaan_id', null) : $user->perumahaan_id;
    }

    public function create(Request $request)
    {
        $category = $request->get('category', 'pembangunan_unit');
        $perumahaanId = $this->currentPerumahaanId();

        $pembangunanUnits = collect();
        $pembangunanKawasan = collect();
        $pembangunanProyek = collect();

        if ($category === 'pembangunan_unit') {
            $queryUnits = \App\Models\PembangunanUnit::with([
                'unit.tahap.perumahaan',
                'pembangunanUnitQc.pembangunanUnitRapBahan.barang.baseUnit',
                'pembangunanUnitQc.pembangunanUnitRapBahan.barang.satuanKonversi.satuan'
            ])
            ->whereNotIn('status_pembangunan', ['selesai', 'selesai dengan catatan']);

            if ($perumahaanId) {
                $queryUnits->where('perumahaan_id', $perumahaanId);
            }
            $pembangunanUnits = $queryUnits->get();
        } elseif ($category === 'pembangunan_kawasan') {
            $queryKawasan = \App\Models\PembangunanKawasan::with(['perumahan', 'periodes' => function($q) {
                $q->where('status', 'proses');
            }]);
            if ($perumahaanId) {
                $queryKawasan->where('perumahaan_id', $perumahaanId);
            }
            $pembangunanKawasan = $queryKawasan->get();
        } elseif ($category === 'pembangunan_proyek_mangoon') {
            // Proyek hanya Mangoon — tidak ada filter perumahaan_id
            $pembangunanProyek = \App\Models\PembangunanProyek::query()->get();
        }

        // Ambil stok gudang
        // Untuk proyek Mangoon: semua gudang Mangoon (tanpa filter perumahaan_id)
        // Untuk unit/kawasan: filter sesuai perumahaan_id
        $barangGudangQuery = MasterBarang::with(['baseUnit', 'satuanKonversi.satuan']);

        if ($category === 'pembangunan_proyek_mangoon') {
            // Tampilkan semua stok tanpa filter perumahaan
            $barangGudangQuery->with('stock');
        } elseif ($perumahaanId) {
            $barangGudangQuery->with(['stock' => function ($q) use ($perumahaanId) {
                $q->where('ubs_id', $perumahaanId);
            }]);
        } else {
            $barangGudangQuery->with('stock');
        }

        $barangGudang = $barangGudangQuery->get()->map(function ($b) {
            $stokTotal = $b->stock ? $b->stock->sum('jumlah_stock') : 0;
            $b->stok_gudang_aktif = (float) $stokTotal;
            return $b;
        });

        $titles = [
            'pembangunan_unit' => 'Tambah Barang Keluar Unit',
            'pembangunan_kawasan' => 'Tambah Barang Keluar Kawasan',
            'pembangunan_proyek_mangoon' => 'Tambah Barang Keluar Proyek',
        ];

        return view('gudang.permintaan-barang.create', [
            'category' => $category,
            'titlePage' => $titles[$category] ?? 'Tambah Barang Keluar',
            'pembangunanUnits' => $pembangunanUnits,
            'pembangunanKawasan' => $pembangunanKawasan,
            'pembangunanProyek' => $pembangunanProyek,
            'barangGudang' => $barangGudang,
            'breadcrumbs' => [
                [
                    'label' => 'Permintaan Barang',
                    'url' => route('gudang.permintaanBarang.index', ['jenis_order' => $category]),
                ],
                [
                    'label' => 'Tambah Barang Keluar',
                    'url' => route('gudang.permintaanBarang.pembangunanUnit.create', ['category' => $category]),
                ],
            ],
        ]);
    }

    public function getQcList($pembangunanUnitId)
    {
        $perumahaanId = $this->currentPerumahaanId();

        $pembangunanUnit = \App\Models\PembangunanUnit::with([
            'pembangunanUnitQc.masterQc',
            'pembangunanUnitQc.pembangunanUnitRapBahan.barang.baseUnit',
            'pembangunanUnitQc.pembangunanUnitRapBahan.barang.satuanKonversi.satuan',
            'pembangunanUnitQc.pembangunanUnitRapBahan.barang.stock' => function($q) use ($perumahaanId) {
                if ($perumahaanId) {
                    $q->where('ubs_id', $perumahaanId);
                }
            }
        ])->findOrFail($pembangunanUnitId);

        $qcs = $pembangunanUnit->pembangunanUnitQc->map(function($qc) {
            $nama = $qc->nama_qc;
            if (!$nama) {
                $nama = $qc->masterQc->nama_qc ?? $qc->masterQc->nama ?? ('QC Ke-' . ($qc->qc_urutan_ke ?? $qc->id));
            }

            return [
                'id' => $qc->id,
                'nama' => $nama,
                'is_servis' => (bool) $qc->is_servis,
                'rap_bahan' => $qc->pembangunanUnitRapBahan->map(function($rap) {
                    $satuans = collect();
                    if ($rap->barang && $rap->barang->baseUnit) {
                        $satuans->push([
                            'id' => $rap->barang->base_unit_id,
                            'nama_satuan' => $rap->barang->baseUnit->nama,
                            'konversi_ke_base' => 1
                        ]);
                    }
                    if ($rap->barang && $rap->barang->satuanKonversi) {
                        foreach ($rap->barang->satuanKonversi as $sk) {
                            if ($sk->satuan) {
                                $satuans->push([
                                    'id' => $sk->satuan_id,
                                    'nama_satuan' => $sk->satuan->nama,
                                    'konversi_ke_base' => (float)$sk->konversi_ke_base
                                ]);
                            }
                        }
                    }

                    $satuanNama = $rap->satuan;
                    if (!$satuanNama && $rap->barang && $rap->barang->baseUnit) {
                        $satuanNama = $rap->barang->baseUnit->nama;
                    }

                    $stokGudang = 0;
                    if ($rap->barang && $rap->barang->stock) {
                        $stokGudang = (float) $rap->barang->stock->sum('jumlah_stock');
                    }

                    // Hitung total akumulasi barang yang sudah diajukan/diorder dari RAP ini
                    $totalOrderedBase = (float) \App\Models\PembangunanUnitBarangOrderDetail::where('rap_bahan_id', $rap->id)->sum('jumlah_base');

                    return [
                        'id' => $rap->id,
                        'barang_id' => $rap->barang_id,
                        'nama_barang' => $rap->nama_barang ?? ($rap->barang->nama_barang ?? '-'),
                        'kode_barang' => $rap->barang->kode_barang ?? '-',
                        'stok_gudang' => $stokGudang,
                        'volume' => (float) ($rap->jumlah_standar ?? $rap->volume ?? 0),
                        'faktor_konversi' => (float) ($rap->faktor_konversi ?? 1),
                        'total_ordered_base' => $totalOrderedBase,
                        'base_unit_id' => $rap->satuan_id ?? ($rap->barang->base_unit_id ?? null),
                        'base_unit_nama' => $satuanNama ?? ($rap->barang->baseUnit->nama ?? ''),
                        'satuans' => $satuans->unique('id')->values()->toArray(),
                        'is_stock' => (bool) ($rap->barang->is_stock ?? true)
                    ];
                })
            ];
        });

        return response()->json([
            'success' => true,
            'qcs' => $qcs
        ]);
    }

    public function edit(Request $request, $id)
    {
        $category = $request->get('category', 'pembangunan_unit');

        $order = null;
        if ($category === 'pembangunan_unit') {
            $order = PembangunanUnitBarangOrder::with([
                'details.barang.baseUnit',
                'details.barang.satuanKonversi.satuan',
                'details.rapBahan',
                'pembangunanUnit.unit.tahap.perumahaan',
                'qc'
            ])->findOrFail($id);
        } elseif ($category === 'pembangunan_kawasan') {
            $order = \App\Models\PembangunanKawasanBarangOrder::with([
                'details.barang.baseUnit',
                'details.barang.satuanKonversi.satuan',
                'kawasan.perumahan'
            ])->findOrFail($id);
        } elseif ($category === 'pembangunan_proyek_mangoon') {
            $order = \App\Models\PembangunanProyekBarangOrder::with([
                'details.barang.baseUnit',
                'details.barang.satuanKonversi.satuan',
                'proyek'
            ])->findOrFail($id);
        }

        if (!$order || $order->status_order !== 'ditolak') {
            return redirect()->route('gudang.permintaanBarang.show', ['id' => $id, 'jenis_order' => $category])
                ->with('error', 'Hanya order dengan status ditolak yang dapat diedit.');
        }

        // Ambil perumahaanId dari data order itu sendiri (bukan dari session)
        // karena unit/kawasan tidak bisa diubah di halaman edit
        $perumahaanId = null;
        if ($category === 'pembangunan_unit') {
            $perumahaanId = $order->pembangunanUnit->perumahaan_id ?? null;
        } elseif ($category === 'pembangunan_kawasan') {
            $perumahaanId = $order->kawasan->perumahaan_id ?? null;
        }
        // proyek tidak difilter per perumahan

        $pembangunanUnits = collect();
        $pembangunanKawasan = collect();
        $pembangunanProyek = collect();

        if ($category === 'pembangunan_unit') {
            $queryUnits = \App\Models\PembangunanUnit::with([
                'unit.tahap.perumahaan',
                'pembangunanUnitQc.pembangunanUnitRapBahan.barang.baseUnit',
                'pembangunanUnitQc.pembangunanUnitRapBahan.barang.satuanKonversi.satuan'
            ]);
            if ($perumahaanId) {
                $queryUnits->where('perumahaan_id', $perumahaanId);
            }
            $pembangunanUnits = $queryUnits->get();
        } elseif ($category === 'pembangunan_kawasan') {
            $queryKawasan = \App\Models\PembangunanKawasan::with(['perumahan', 'periodes' => function($q) {
                $q->where('status', 'proses');
            }]);
            if ($perumahaanId) {
                $queryKawasan->where('perumahaan_id', $perumahaanId);
            }
            $pembangunanKawasan = $queryKawasan->get();
        } elseif ($category === 'pembangunan_proyek_mangoon') {
            $pembangunanProyek = \App\Models\PembangunanProyek::query()->get();
        }

        $barangGudangQuery = MasterBarang::with(['baseUnit', 'satuanKonversi.satuan']);

        if ($category === 'pembangunan_proyek_mangoon') {
            // Tampilkan semua stok tanpa filter perumahaan
            $barangGudangQuery->with('stock');
        } elseif ($perumahaanId) {
            $barangGudangQuery->with(['stock' => function ($q) use ($perumahaanId) {
                $q->where('ubs_id', $perumahaanId);
            }]);
        } else {
            $barangGudangQuery->with('stock');
        }

        $barangGudang = $barangGudangQuery->get()->map(function ($b) {
            $stokTotal = $b->stock ? $b->stock->sum('jumlah_stock') : 0;
            $b->stok_gudang_aktif = (float) $stokTotal;
            return $b;
        });

        $titles = [
            'pembangunan_unit' => 'Edit Permintaan Barang Unit',
            'pembangunan_kawasan' => 'Edit Permintaan Barang Kawasan',
            'pembangunan_proyek_mangoon' => 'Edit Permintaan Barang Proyek',
        ];

        return view('gudang.permintaan-barang.edit', [
            'order' => $order,
            'category' => $category,
            'titlePage' => $titles[$category] ?? 'Edit Permintaan Barang',
            'pembangunanUnits' => $pembangunanUnits,
            'pembangunanKawasan' => $pembangunanKawasan,
            'pembangunanProyek' => $pembangunanProyek,
            'barangGudang' => $barangGudang,
            'breadcrumbs' => [
                [
                    'label' => 'Permintaan Barang',
                    'url' => route('gudang.permintaanBarang.index', ['jenis_order' => $category]),
                ],
                [
                    'label' => 'Detail Permintaan',
                    'url' => route('gudang.permintaanBarang.show', ['id' => $id, 'jenis_order' => $category]),
                ],
                [
                    'label' => 'Edit Permintaan',
                    'url' => route('gudang.permintaanBarang.edit', ['id' => $id, 'category' => $category]),
                ],
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = $request->get('category', 'pembangunan_unit');

        $order = null;
        if ($category === 'pembangunan_unit') {
            $order = PembangunanUnitBarangOrder::with('details')->findOrFail($id);
        } elseif ($category === 'pembangunan_kawasan') {
            $order = \App\Models\PembangunanKawasanBarangOrder::with('details')->findOrFail($id);
        } elseif ($category === 'pembangunan_proyek_mangoon') {
            $order = \App\Models\PembangunanProyekBarangOrder::with('details')->findOrFail($id);
        }

        if (!$order || $order->status_order !== 'ditolak') {
            return response()->json(['message' => 'Hanya permintaan dengan status ditolak yang dapat diedit.'], 422);
        }

        try {
            DB::beginTransaction();

            $order->update([
                'catatan' => $request->catatan,
                'jenis_order' => $request->jenis_order ?? $order->jenis_order,
            ]);

            // Hapus detail lama dan ganti dengan detail baru
            $order->details()->delete();

            if ($category === 'pembangunan_unit') {
                $pembangunanUnit = $order->pembangunanUnit;
                foreach ($request->items as $item) {
                    $barang = MasterBarang::findOrFail($item['barang_id']);
                    $faktorKonversi = BarangSatuanKonversi::where('barang_id', $item['barang_id'])
                        ->where('satuan_id', $item['satuan_id'])
                        ->value('konversi_ke_base');

                    $faktorKonversi = (float) ($faktorKonversi ?? ($item['faktor_konversi'] ?? 1));
                    if ($faktorKonversi <= 0) {
                        throw new \Exception("Konversi satuan untuk {$item['nama_barang']} tidak valid.");
                    }

                    $newQtyBase = $faktorKonversi * (float) $item['jumlah_input'];
                    $alasan = $item['alasan'] ?? null;

                    if (!empty($item['pembangunan_unit_rap_bahan_id'])) {
                        $rapBahan = \App\Models\PembangunanUnitRapBahan::findOrFail($item['pembangunan_unit_rap_bahan_id']);
                        $alreadyOrderedBase = PembangunanUnitBarangOrderDetail::where('rap_bahan_id', $rapBahan->id)
                            ->where('order_id', '!=', $order->id)
                            ->sum('jumlah_base');

                        $rapTotalBase = (float) $rapBahan->jumlah_standar * (float) $rapBahan->faktor_konversi;

                        if (($alreadyOrderedBase + $newQtyBase) > ($rapTotalBase + 0.001)) {
                            if (empty($alasan)) {
                                throw new \Exception("Order barang {$barang->nama_barang} melebihi RAP. Harap masukkan alasan melebihi RAP.");
                            }
                        }
                    }

                    PembangunanUnitBarangOrderDetail::create([
                        'order_id' => $order->id,
                        'barang_id' => $item['barang_id'],
                        'nama_barang' => $barang->nama_barang,
                        'satuan_id' => $item['satuan_id'],
                        'satuan' => $item['satuan'],
                        'ubs_id' => $pembangunanUnit?->perumahaan_id,
                        'rap_bahan_id' => $item['pembangunan_unit_rap_bahan_id'] ?? null,
                        'jumlah_input' => $item['jumlah_input'],
                        'jumlah_base' => $newQtyBase,
                        'alasan_permintaan_tidak_sesuai_rap' => $alasan,
                    ]);
                }
            } elseif ($category === 'pembangunan_kawasan') {
                foreach ($request->barang as $item) {
                    $barang = MasterBarang::findOrFail($item['id']);
                    $satuan = \App\Models\MasterSatuan::find($item['satuan_id']);
                    $faktor = BarangSatuanKonversi::where('barang_id', $item['id'])
                        ->where('satuan_id', $item['satuan_id'])
                        ->value('konversi_ke_base') ?? 1.0;

                    $jumlahBase = round((float) $item['jumlah_input'] * (float) $faktor, 3);

                    \App\Models\PembangunanKawasanBarangOrderDetail::create([
                        'order_id' => $order->id,
                        'barang_id' => $item['id'],
                        'nama_barang' => $barang->nama_barang,
                        'satuan_id' => $item['satuan_id'],
                        'satuan' => $satuan?->nama ?? '-',
                        'jumlah_input' => $item['jumlah_input'],
                        'jumlah_base' => $jumlahBase,
                    ]);
                }
            } elseif ($category === 'pembangunan_proyek_mangoon') {
                foreach ($request->barang as $item) {
                    $barang = MasterBarang::findOrFail($item['id']);
                    $satuan = \App\Models\MasterSatuan::find($item['satuan_id']);
                    $faktor = BarangSatuanKonversi::where('barang_id', $item['id'])
                        ->where('satuan_id', $item['satuan_id'])
                        ->value('konversi_ke_base') ?? 1.0;

                    $jumlahBase = round((float) $item['jumlah_input'] * (float) $faktor, 3);

                    \App\Models\PembangunanProyekBarangOrderDetail::create([
                        'order_id' => $order->id,
                        'barang_id' => $item['id'],
                        'nama_barang' => $barang->nama_barang,
                        'satuan_id' => $item['satuan_id'],
                        'satuan' => $satuan?->nama ?? '-',
                        'jumlah_input' => $item['jumlah_input'],
                        'jumlah_base' => $jumlahBase,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Permintaan barang berhasil disimpan. Silakan klik Ajukan Ulang untuk mengajukan kembali.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memperbarui permintaan: ' . $e->getMessage()
            ], 500);
        }
    }
}
