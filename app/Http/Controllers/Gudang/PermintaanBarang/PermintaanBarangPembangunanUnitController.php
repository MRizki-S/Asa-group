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
}
