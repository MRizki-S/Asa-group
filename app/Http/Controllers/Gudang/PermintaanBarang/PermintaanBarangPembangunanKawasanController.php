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
                                'supplier'     => "Return Proyek Kawasan #{$return->pembangunan_kawasan_id} ({$return->nomor_return})",
                                'cara_bayar'   => 'cash',
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

        return back()->with('success', 'Pengajuan retur barang kawasan telah ditolak.');
    }
}
