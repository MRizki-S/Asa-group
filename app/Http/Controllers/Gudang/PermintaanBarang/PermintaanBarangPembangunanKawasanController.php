<?php

namespace App\Http\Controllers\Gudang\PermintaanBarang;

use App\Http\Controllers\Controller;
use App\Models\BarangRusak;
use App\Models\BarangSatuanKonversi;
use App\Models\PembangunanKawasanBahan;
use App\Models\PembangunanKawasanBarangFifoUsage;
use App\Models\PembangunanKawasanBarangReturn;
use App\Models\PembangunanKawasanBarangReturnDetail;
use App\Models\PembangunanKawasanBarangReturnFifo;
use App\Models\MasterBarang;
use App\Models\NotaBarangMasukDetail;
use App\Models\PembangunanKawasanBarangOrder;
use App\Models\PembangunanKawasanBarangOrderDetail;
use App\Models\StockGudang;
use App\Models\StockLedger;
use App\Models\Ubs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Services\NotificationPribadiService;

class PermintaanBarangPembangunanKawasanController extends Controller
{
    protected NotificationPribadiService $notification;

    public function __construct(NotificationPribadiService $notification)
    {
        $this->notification = $notification;
    }
    public function indexReturn(Request $request)
    {
        $status = $request->get('status', 'diproses');

        $query = PembangunanKawasanBarangReturn::with([
            'kawasan.perumahan',
            'kawasan.pengawas',
            'createdBy',
            'accBy',
            'details'
        ])
        ->withCount('details');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $returns = $query->latest()->get();

        return view('gudang.return-barang.index', [
            'category' => 'pembangunan_kawasan',
            'titlePage' => 'Konfirmasi Retur Barang Kawasan',
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
                    'label' => 'Retur Barang Kawasan',
                    'url' => route('gudang.returnBarang.kawasan.index'),
                ],
            ],
        ]);
    }

    public function historyReturn(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = PembangunanKawasanBarangReturn::with([
            'kawasan.perumahan',
            'kawasan.pengawas',
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
            'category' => 'pembangunan_kawasan',
            'titlePage' => 'Riwayat Retur Barang Kawasan',
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
                    'label' => 'Retur Barang Kawasan',
                    'url' => route('gudang.returnBarang.kawasan.index'),
                ],
                [
                    'label' => 'Riwayat Retur',
                    'url' => route('gudang.returnBarang.kawasan.history'),
                ],
            ],
        ]);
    }

    public function showReturn($id)
    {
        $return = PembangunanKawasanBarangReturn::with([
            'kawasan.perumahan',
            'kawasan.pengawas',
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
            'category' => 'pembangunan_kawasan',
            'return' => $return,
            'breadcrumbs' => [
                [
                    'label' => 'Retur Barang Kawasan',
                    'url' => route('gudang.returnBarang.kawasan.index'),
                ],
                [
                    'label' => 'Detail Retur #' . ($return->nomor_return ?? $return->id),
                    'url' => route('gudang.returnBarang.kawasan.show', $return->id),
                ],
            ],
        ]);
    }

    public function accBarangReturn(Request $request, $id)
    {
        $return = PembangunanKawasanBarangReturn::with(['kawasan.perumahan', 'details.barang.baseUnit'])->findOrFail($id);

        if ($return->status !== 'diproses') {
            return back()->with('error', 'Status pengajuan retur barang kawasan ini sudah tidak dapat di-ACC.');
        }

        try {
            DB::transaction(function () use ($request, $return) {
                $kawasan = $return->kawasan;
                $perumahan = $kawasan?->perumahan;
                $ubsId = $perumahan?->nama_perumahaan
                    ? Ubs::where('nama_ubs', $perumahan->nama_perumahaan)->value('id')
                    : null;

                $notaRtnId = null;

                foreach ($return->details as $detail) {
                    $itemInput = collect($request->input('items', []))->firstWhere('id', $detail->id);

                    $layakInput = isset($itemInput['jumlah_layak_input'])
                        ? (float)$itemInput['jumlah_layak_input']
                        : (float)$detail->jumlah_base;

                    $rusakInput = isset($itemInput['jumlah_rusak_input'])
                        ? (float)$itemInput['jumlah_rusak_input']
                        : 0.0;

                    if ($layakInput < 0 || $rusakInput < 0) {
                        throw new \Exception("Jumlah barang layak dan rusak untuk {$detail->nama_barang} tidak boleh bernilai negatif.");
                    }

                    $satuanInputId = isset($itemInput['satuan_id']) ? (int)$itemInput['satuan_id'] : $detail->satuan_id;

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

                    // 1. Find FIFO usage records for this barang & kawasan
                    $fifoUsages = PembangunanKawasanBarangFifoUsage::query()
                        ->join('pembangunan_kawasan_barang_order_detail as od', 'od.id', '=', 'pembangunan_kawasan_barang_fifo_usage.order_detail_id')
                        ->join('pembangunan_kawasan_barang_order as o', 'o.id', '=', 'od.order_id')
                        ->where('o.pembangunan_kawasan_id', $return->pembangunan_kawasan_id)
                        ->where('o.status_order', 'selesai')
                        ->where('od.barang_id', $detail->barang_id)
                        ->whereRaw('pembangunan_kawasan_barang_fifo_usage.jumlah_base > pembangunan_kawasan_barang_fifo_usage.jumlah_return_base')
                        ->orderBy('o.tanggal_diajukan', 'asc')
                        ->orderBy('pembangunan_kawasan_barang_fifo_usage.id', 'asc')
                        ->select('pembangunan_kawasan_barang_fifo_usage.*', 'od.id as order_detail_id')
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

                        PembangunanKawasanBarangReturnFifo::create([
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

                        $orderDetail = \App\Models\PembangunanKawasanBarangOrderDetail::find($fifoUsage->order_detail_id);
                        if ($orderDetail) {
                            $orderDetail->increment('jumlah_return_base', $takeQty);
                            $orderDetail->increment('jumlah_return', round($takeQty / $faktor, 3));
                        }

                        $remainingReturn -= $takeQty;
                        $remainingLayak -= $takeLayak;
                        $remainingRusak -= $takeRusak;
                    }

                    $avgHargaSatuan = (float)$detail->jumlah_base > 0 ? $totalHargaReturnDetail / (float)$detail->jumlah_base : 0;
                    $detail->update([
                        'jumlah_layak_base' => $jumlahLayakBase,
                        'jumlah_rusak_base' => $jumlahRusakBase,
                        'harga_satuan_snapshot' => $avgHargaSatuan,
                        'harga_total_snapshot' => $totalHargaReturnDetail,
                    ]);

                    // 5. Kurangi Termin (pembangunan_kawasan_bahan)
                    $bahanQuery = PembangunanKawasanBahan::where('pembangunan_kawasan_id', $return->pembangunan_kawasan_id)
                        ->where('barang_id', $detail->barang_id);

                    if ($return->pembangunan_kawasan_periode_id) {
                        $bahanQuery->where('pembangunan_kawasan_periode_id', $return->pembangunan_kawasan_periode_id);
                    }

                    $bahan = $bahanQuery->first();

                    if ($bahan) {
                        $newJumlahPakai = max(0, (float)$bahan->jumlah_pakai - (float)$detail->jumlah_base);
                        $newHargaTotal = max(0, (float)$bahan->harga_total_snapshot - $totalHargaReturnDetail);
                        $bahan->update([
                            'jumlah_pakai' => $newJumlahPakai,
                            'harga_total_snapshot' => $newHargaTotal,
                        ]);
                    }

                    // Barang Layak -> Masuk HUB PUSAT (stock_type = 'HUB', ubs_id = null)
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
                                'jenis_nota'   => 'return_barang',
                                'cara_bayar'   => 'cash',
                                'stock_type'   => 'UBS',
                                'ubs_id'       => $ubsId,
                                'status'       => 'posted',
                                'created_by'   => Auth::id(),
                                'posted_at'    => now(),
                                'created_at'   => now(),
                                'updated_at'   => now(),
                            ]);
                        }

                        $layakHargaTotal = round($jumlahLayakBase * $avgHargaSatuan, 2);

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

                        // Barang Layak -> Masuk Stok UBS Perumahan asal (stock_type = 'UBS', ubs_id = $ubsId)
                        $stock = StockGudang::where('barang_id', $detail->barang_id)
                            ->where('stock_type', 'UBS')
                            ->where('ubs_id', $ubsId)
                            ->lockForUpdate()
                            ->first();

                        if ($stock) {
                            $stock->increment('jumlah_stock', $jumlahLayakBase);
                        } else {
                            StockGudang::create([
                                'barang_id' => $detail->barang_id,
                                'stock_type' => 'UBS',
                                'ubs_id' => $ubsId,
                                'jumlah_stock' => $jumlahLayakBase,
                            ]);
                        }

                        StockLedger::create([
                            'tanggal' => now(),
                            'barang_id' => $detail->barang_id,
                            'stock_type' => 'UBS',
                            'ubs_id' => $ubsId,
                            'tipe' => 'masuk',
                            'ref_type' => 'PembangunanKawasanBarangReturn',
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
                            'keterangan' => "Barang Rusak dari Retur Kawasan #{$return->pembangunan_kawasan_id} ({$return->nomor_return})",
                            'created_by' => Auth::id(),
                            'posted_at' => now(),
                        ]);
                    }
                }

                $return->update([
                    'status' => 'selesai',
                    'acc_by' => Auth::id(),
                    'acc_at' => now(),
                ]);
            });

            // Kirim notifikasi WA
            $adminName = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Admin Gudang';
            $targetGroup = env('FONNTE_ID_GROUP_ACC_RETUR_BARANG_KAWASAN', env('FONNTE_ID_GROUP_RETUR_BARANG_KAWASAN', env('FONNTE_ID_ORDER_BARANG_ABM')));
            if (!empty($targetGroup)) {
                $message = view('notifications.whatsapp.pembangunan_kawasan.acc_retur_barang', [
                    'return' => $return,
                    'namaPerumahan' => $return->kawasan?->perumahan?->nama_perumahaan ?? '-',
                    'namaKawasan' => $return->kawasan?->nama ?? '-',
                    'adminGudang' => $adminName,
                    'tanggalAcc' => now()->format('d/m/Y H:i') . ' WIB',
                ])->render();
                $this->notification->sendWhatsApp($targetGroup, $message);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal ACC retur barang kawasan: ' . $e->getMessage());
        }

        return back()->with('success', 'Pengajuan retur barang kawasan berhasil di-ACC.');
    }

    public function rejectBarangReturn(Request $request, $id)
    {
        $return = PembangunanKawasanBarangReturn::with('kawasan.perumahan')->findOrFail($id);

        if ($return->status !== 'diproses') {
            return back()->with('error', 'Status pengajuan retur barang ini tidak dapat ditolak.');
        }

        $request->validate([
            'alasan_tolak' => 'required|string',
        ]);

        $return->update([
            'status' => 'ditolak',
            'alasan_tolak' => $request->alasan_tolak,
            'acc_by' => Auth::id(),
            'acc_at' => now(),
        ]);

        $adminName = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Admin Gudang';
        $targetGroup = env('FONNTE_ID_GROUP_TOLAK_RETUR_BARANG_KAWASAN', env('FONNTE_ID_GROUP_RETUR_BARANG_KAWASAN', env('FONNTE_ID_ORDER_BARANG_ABM')));
        if (!empty($targetGroup)) {
            $message = view('notifications.whatsapp.pembangunan_kawasan.tolak_retur_barang', [
                'return' => $return,
                'namaPerumahan' => $return->kawasan?->perumahan?->nama_perumahaan ?? '-',
                'namaKawasan' => $return->kawasan?->nama ?? '-',
                'adminGudang' => $adminName,
                'alasanTolak' => $request->alasan_tolak,
                'tanggal' => now()->format('d/m/Y H:i') . ' WIB',
            ])->render();
            $this->notification->sendWhatsApp($targetGroup, $message);
        }

        return back()->with('success', 'Pengajuan retur barang kawasan berhasil ditolak.');
    }

    public function accBarangOrder(Request $request, $id)
    {
        $order = PembangunanKawasanBarangOrder::with([
            'details.barang.baseUnit',
            'user',
            'kawasan.perumahan',
            'kawasan.pengawas',
        ])->findOrFail($id);

        if ($order->status_order !== 'diproses') {
            return back()->with('error', 'Permintaan barang kawasan ini sudah tidak dalam status menunggu proses gudang.');
        }

        try {
            DB::transaction(function () use ($order, $request) {
                $itemsAcc = $request->input('items_acc', []);
                $hargaTotalInput = $request->input('harga_total', []);

                foreach ($order->details as $detail) {
                    $qtyAcc = isset($itemsAcc[$detail->id]) ? (float) $itemsAcc[$detail->id] : (float) $detail->jumlah_input;
                    if ($qtyAcc < 0) {
                        throw new \Exception("Jumlah acc untuk barang {$detail->nama_barang} tidak boleh negatif.");
                    }

                    $faktorKonversi = BarangSatuanKonversi::where('barang_id', $detail->barang_id)
                        ->where('satuan_id', $detail->satuan_id)
                        ->value('konversi_ke_base') ?? 1.0;

                    $jumlahAccBase = round($qtyAcc * (float) $faktorKonversi, 3);
                    $updateData = [
                        'jumlah_acc' => $qtyAcc,
                        'jumlah_acc_base' => $jumlahAccBase,
                    ];

                    // Simpan harga total snapshot dari input gudang untuk order direct
                    if ($order->jenis_order === 'direct' && isset($hargaTotalInput[$detail->id]) && $hargaTotalInput[$detail->id] !== '') {
                        $ht = (float) $hargaTotalInput[$detail->id];
                        $updateData['harga_total_snapshot'] = $ht;
                        $updateData['harga_satuan_snapshot'] = $jumlahAccBase > 0 ? round($ht / $jumlahAccBase, 2) : 0;
                    }

                    $detail->update($updateData);
                }

                $order->update([
                    'status_order' => 'menunggu_spv',
                    'gudang_by' => Auth::id(),
                    'tanggal_gudang' => now(),
                    'catatan_gudang' => $request->input('catatan_gudang'),
                ]);
            });

            // Kirim notifikasi WA konfirmasi staff gudang ke nomor pribadi SPV Logistik & Pengadaan
            $adminName = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Staff Gudang';
            $namaPerumahan = $order->kawasan?->perumahan?->nama_perumahaan ?? '-';
            $namaKawasan = $order->kawasan?->nama ?? '-';

            $targetHpSpv = env('FONNTE_NO_SPV_LOGISTIK');
            if (!empty($targetHpSpv)) {
                $message = view('notifications.whatsapp.pembangunan_kawasan.gudang_order_barang', [
                    'order' => $order->fresh(['details']),
                    'namaPerumahan' => $namaPerumahan,
                    'namaKawasan' => $namaKawasan,
                    'adminGudang' => $adminName,
                    'tanggalGudang' => now()->format('d/m/Y H:i') . ' WIB',
                ])->render();
                $this->notification->sendWhatsApp($targetHpSpv, $message);
            }
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memproses pengeluaran barang gudang: ' . $e->getMessage());
        }

        return redirect()
            ->route('gudang.permintaanBarang.show', ['id' => $order->id, 'jenis_order' => 'pembangunan_kawasan'])
            ->with('success', 'Barang keluar kawasan berhasil disiapkan dan diteruskan ke SPV Logistik untuk ACC.');
    }

    public function spvAccBarangOrder(Request $request, $id)
    {
        $order = PembangunanKawasanBarangOrder::with([
            'details.barang.baseUnit',
            'user',
            'kawasan.perumahan',
            'kawasan.pengawas',
            'periode',
        ])->findOrFail($id);

        if (!in_array($order->status_order, ['menunggu_spv', 'diproses'])) {
            return back()->with('error', 'Permintaan barang kawasan ini sudah tidak dalam status menunggu persetujuan.');
        }

        try {
            DB::transaction(function () use ($order, $request) {
                // Generate nomor NBK resmi jika belum ada: NBK-KWS-YYYYMMDD-XXXX
                if (!$order->nomor_nbk) {
                    $datePrefix = 'NBK-KWS-' . now()->format('Ymd') . '-';
                    $lastNbk = PembangunanKawasanBarangOrder::where('nomor_nbk', 'like', $datePrefix . '%')
                        ->orderBy('nomor_nbk', 'desc')
                        ->lockForUpdate()
                        ->first();
                    $nextSeq = 1;
                    if ($lastNbk) {
                        $lastSeq = (int) substr($lastNbk->nomor_nbk, strlen($datePrefix));
                        $nextSeq = $lastSeq + 1;
                    }
                    $order->nomor_nbk = $datePrefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
                }

                $this->processAccOrder($order, $request);

                $order->update([
                    'status_order' => 'selesai',
                    'tanggal_selesai' => now(),
                    'acc_by' => Auth::id(),
                    'spv_by' => Auth::id(),
                    'tanggal_spv' => now(),
                    'nomor_nbk' => $order->nomor_nbk,
                ]);
            });

            // Kirim notifikasi WA setelah SPV berhasil ACC ke grup FONNTE_ID_ORDER_BARANG_ABM
            $spvName = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'SPV Logistik';
            $namaPerumahan = $order->kawasan?->perumahan?->nama_perumahaan ?? '-';
            $namaKawasan = $order->kawasan?->nama ?? '-';

            $targetGroup = env('FONNTE_ID_ORDER_BARANG_ABM', env('FONNTE_ID_GROUP_ACC_ORDER_BARANG_KAWASAN', env('FONNTE_ID_GROUP_ORDER_BARANG_KAWASAN')));
            if (!empty($targetGroup)) {
                $message = view('notifications.whatsapp.pembangunan_kawasan.acc_order_barang', [
                    'order' => $order->fresh(['details.barang.baseUnit']),
                    'namaPerumahan' => $namaPerumahan,
                    'namaKawasan' => $namaKawasan,
                    'spvName' => $spvName,
                    'tanggalSpv' => now()->format('d/m/Y H:i') . ' WIB',
                ])->render();
                $this->notification->sendWhatsApp($targetGroup, $message);
            }
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal ACC SPV Logistik: ' . $e->getMessage());
        }

        return redirect()
            ->route('gudang.permintaanBarang.history', ['jenis_order' => 'pembangunan_kawasan'])
            ->with('success', 'Order barang kawasan berhasil di-ACC resmi oleh SPV Logistik. Stok telah dipotong dan dicatat ke data real.');
    }

    public function tolakBarangOrder(Request $request, $id)
    {
        $request->validate([
            'alasan_tolak' => 'required|string|max:1000',
        ]);

        $order = PembangunanKawasanBarangOrder::with([
            'details',
            'kawasan.perumahan'
        ])->findOrFail($id);

        if (!in_array($order->status_order, ['diproses', 'menunggu_spv'])) {
            return back()->with('error', 'Permintaan barang kawasan ini sudah tidak dalam status menunggu.');
        }

        $order->update([
            'status_order' => 'ditolak',
            'alasan_tolak' => $request->alasan_tolak,
        ]);

        $targetGroup = env('FONNTE_ID_GROUP_TOLAK_ORDER_BARANG_KAWASAN', env('FONNTE_ID_GROUP_ORDER_BARANG_KAWASAN', env('FONNTE_ID_ORDER_BARANG_ABM')));
        if (!empty($targetGroup)) {
            $adminName = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Admin Gudang';
            $message = view('notifications.whatsapp.pembangunan_kawasan.tolak_order_barang', [
                'order' => $order,
                'namaPerumahan' => $order->kawasan?->perumahan?->nama_perumahaan ?? '-',
                'namaKawasan' => $order->kawasan?->nama ?? '-',
                'adminGudang' => $adminName,
                'alasanTolak' => $order->alasan_tolak ?? null,
                'tanggal' => now()->format('d/m/Y H:i') . ' WIB',
            ])->render();
            $this->notification->sendWhatsApp($targetGroup, $message);
        }

        return back()->with('success', 'Permintaan barang kawasan berhasil ditolak.');
    }

    public function resubmitBarangOrder(Request $request, $id)
    {
        $request->validate(['catatan' => 'nullable|string|max:1000']);

        $order = PembangunanKawasanBarangOrder::with([
            'details',
            'user',
            'kawasan.perumahan'
        ])->findOrFail($id);

        if ($order->status_order !== 'ditolak') {
            return back()->with('error', 'Hanya permintaan yang ditolak yang dapat diajukan kembali.');
        }

        $order->update([
            'status_order'     => 'diproses',
            'catatan'          => $request->catatan,
            'tanggal_diajukan' => now(),
        ]);

        $targetGroup = env('FONNTE_ID_GROUP_ORDER_BARANG_KAWASAN', env('FONNTE_ID_ORDER_BARANG_ABM'));
        if (!empty($targetGroup)) {
            $pengaju = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Pengaju';
            $message = view('notifications.whatsapp.pembangunan_kawasan.order_barang', [
                'order' => $order,
                'tipe' => 'Kawasan',
                'namaPerumahan' => $order->kawasan?->perumahan?->nama_perumahaan ?? '-',
                'namaKawasan' => $order->kawasan?->nama ?? '-',
                'pengawas' => $order->kawasan?->pengawas?->nama_lengkap ?? $order->kawasan?->pengawas?->name ?? '-',
                'pengaju' => $pengaju,
                'tanggalDiajukan' => now()->format('d/m/Y H:i') . ' WIB',
                'tanggalNbk' => now()->format('d/m/Y H:i') . ' WIB',
            ])->render();
            $this->notification->sendWhatsApp($targetGroup, $message);
        }

        return back()->with('success', 'Permintaan barang kawasan berhasil diajukan kembali.');
    }

    public function notaPdf($id)
    {
        $order = PembangunanKawasanBarangOrder::with([
            'details.barang.baseUnit',
            'user',
            'kawasan.perumahan',
            'kawasan.pengawas',
            'periode',
            'gudangBy',
            'spvBy',
        ])->findOrFail($id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('gudang.permintaan-barang.nota-keluar-kawasan-pdf', compact('order'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('NBK-' . ($order->nomor_nbk ?? $order->nomor_order) . '.pdf');
    }

    private function processAccOrder(PembangunanKawasanBarangOrder $order, Request $request): void
    {
        $kawasan = $order->kawasan;
        $perumahan = $kawasan?->perumahan;
        $ubsId = $perumahan?->nama_perumahaan
            ? Ubs::where('nama_ubs', $perumahan->nama_perumahaan)->value('id')
            : null;

        if (!$kawasan || !$ubsId) {
            throw new \Exception('Data pembangunan kawasan atau UBS/perumahan tujuan stock tidak ditemukan.');
        }

        foreach ($order->details as $detail) {
            $this->assertDetailMatchesOrderType($order, $detail);

            if ($detail->konfirmasi) {
                continue;
            }

            $jumlahBase = $this->resolveJumlahBaseOrder($detail);
            $hargaTotal = 0.0;
            $hargaSatuanBase = 0.0;

            if ($jumlahBase > 0) {
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

                    $fifoResult = $this->consumeNotaFifoOrder($detail->barang_id, $jumlahBase);
                    $hargaTotal = $fifoResult['harga_total'];
                    $hargaSatuanBase = $jumlahBase > 0 ? $hargaTotal / $jumlahBase : 0;

                    foreach ($fifoResult['layers'] as $layer) {
                        PembangunanKawasanBarangFifoUsage::create([
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
                        'ref_type' => 'PembangunanKawasanBarangOrder',
                        'ref_id' => $order->id,
                        'qty_masuk' => 0,
                        'qty_keluar' => $jumlahBase,
                        'harga_satuan' => $hargaSatuanBase,
                        'created_by' => Auth::id(),
                    ]);
                } else {
                    $hargaTotal = $this->resolveDirectHargaTotalOrder($request, $detail);
                    $hargaSatuanBase = $jumlahBase > 0 ? $hargaTotal / $jumlahBase : 0;
                }
            } else {
                // Qty rilis adalah 0 (misal stok gudang habis)
                $hargaTotal = 0.0;
                $hargaSatuanBase = 0.0;
            }

            $detail->update([
                'konfirmasi' => true,
                'jumlah_base' => $jumlahBase,
                'harga_satuan_snapshot' => $hargaSatuanBase,
                'harga_total_snapshot' => $hargaTotal,
            ]);

            if ($jumlahBase > 0) {
                $this->upsertPembangunanKawasanBahan($order, $detail, $hargaTotal);
            }
        }
    }

    private function consumeNotaFifoOrder(int $barangId, float $jumlahBase): array
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

    private function resolveJumlahBaseOrder($detail): float
    {
        $qty = !is_null($detail->jumlah_acc) ? (float) $detail->jumlah_acc : (float) $detail->jumlah_input;

        $faktorKonversi = BarangSatuanKonversi::where('barang_id', $detail->barang_id)
            ->where('satuan_id', $detail->satuan_id)
            ->value('konversi_ke_base');

        if ($faktorKonversi === null) {
            return (float) ($detail->jumlah_acc_base ?? $detail->jumlah_base);
        }

        return round($qty * (float) $faktorKonversi, 3);
    }

    private function resolveDirectHargaTotalOrder(Request $request, $detail): float
    {
        $hargaTotal = $request->input("harga_total.{$detail->id}");

        if ($hargaTotal === null || $hargaTotal === '') {
            if (!is_null($detail->harga_total_snapshot)) {
                return (float) $detail->harga_total_snapshot;
            }
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

    private function assertDetailMatchesOrderType(PembangunanKawasanBarangOrder $order, $detail): void
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

    private function upsertPembangunanKawasanBahan(PembangunanKawasanBarangOrder $order, $detail, float $hargaTotal): void
    {
        $baseUnitName = $detail->barang?->baseUnit?->nama ?? ($detail->satuanModel?->nama ?? ($detail->satuan ?? '-'));
        $periodeId = $order->pembangunan_kawasan_periode_id;

        $bahan = PembangunanKawasanBahan::where('pembangunan_kawasan_id', $order->pembangunan_kawasan_id)
            ->where('pembangunan_kawasan_periode_id', $periodeId)
            ->where('barang_id', $detail->barang_id)
            ->first();

        if ($bahan) {
            $bahan->update([
                'jumlah_pakai' => (float) $bahan->jumlah_pakai + (float) $detail->jumlah_base,
                'harga_total_snapshot' => (float) $bahan->harga_total_snapshot + $hargaTotal,
            ]);
            return;
        }

        PembangunanKawasanBahan::create([
            'pembangunan_kawasan_id' => $order->pembangunan_kawasan_id,
            'pembangunan_kawasan_periode_id' => $periodeId,
            'barang_id' => $detail->barang_id,
            'nama_barang' => $detail->nama_barang ?? $detail->barang?->nama_barang ?? '-',
            'satuan' => $baseUnitName,
            'jumlah_pakai' => (float) $detail->jumlah_base,
            'harga_total_snapshot' => $hargaTotal,
        ]);
    }
}
