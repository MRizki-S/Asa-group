<?php

namespace App\Http\Controllers\Produksi\PembangunanKawasan;

use App\Http\Controllers\Controller;
use App\Models\MasterBarang;
use App\Models\MasterUpah;
use App\Models\PembangunanKawasan;
use App\Models\PembangunanKawasanBarangOrder;
use App\Models\PembangunanKawasanBarangOrderDetail;
use App\Models\PembangunanKawasanBarangReturn;
use App\Models\PembangunanKawasanBarangReturnDetail;
use App\Models\BarangSatuanKonversi;
use App\Models\PembangunanKawasanUpahPengajuan;
use App\Models\PembangunanKawasanPeriode;
use App\Services\NotificationGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PembangunanKawasanController extends Controller
{
    protected NotificationGroupService $notificationGroup;

    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }

    protected function sendGroupNotificationOrder(PembangunanKawasan $kawasan, $order)
    {
        $kawasan->loadMissing(['pengawas', 'perumahan']);
        $order->loadMissing(['details']);

        $groupId = env('FONNTE_ID_GROUP_ORDER_BARANG_KAWASAN', env('FONNTE_ID_ORDER_BARANG_KAWASAN', env('FONNTE_ID_ORDER_BARANG_ABM')));
        if (!$groupId) return;

        $messageGroup = view('notifications.whatsapp.pembangunan_kawasan.order_barang', [
            'tipe' => 'Kawasan',
            'namaPerumahan' => $kawasan->perumahan->nama_perumahaan ?? '-',
            'namaKawasan' => $kawasan->nama ?? '-',
            'pengawas' => $kawasan->pengawas?->nama_lengkap ?? $kawasan->pengawas?->name ?? '-',
            'pengaju' => Auth::user()->nama_lengkap ?? Auth::user()->name,
            'tanggal' => now()->format('d/m/Y H:i') . ' WIB',
            'order' => $order
        ])->render();

        try {
            $this->notificationGroup->send($groupId, $messageGroup);
        } catch (\Exception $e) {
        }
    }

    protected function sendGroupNotificationReturn(PembangunanKawasan $kawasan, $return)
    {
        $kawasan->loadMissing(['pengawas', 'perumahan']);
        $return->loadMissing(['details.orderDetail']);

        $groupId = env('FONNTE_ID_GROUP_RETUR_BARANG_KAWASAN', env('FONNTE_ID_RETURN_BARANG_KAWASAN', env('FONNTE_ID_ORDER_BARANG_ABM')));
        if (!$groupId) return;

        $messageGroup = view('notifications.whatsapp.pembangunan_kawasan.retur_barang', [
            'tipe' => 'Kawasan',
            'namaPerumahan' => $kawasan->perumahan->nama_perumahaan ?? '-',
            'namaKawasan' => $kawasan->nama ?? '-',
            'pengaju' => Auth::user()->nama_lengkap ?? Auth::user()->name,
            'tanggal' => now()->format('d/m/Y H:i') . ' WIB',
            'return' => $return
        ])->render();

        try {
            $this->notificationGroup->send($groupId, $messageGroup);
        } catch (\Exception $e) {
        }
    }

    public function index(Request $request)
    {
        $month = $request->input('month', 'all');
        $year = $request->input('year', date('Y'));

        $user = Auth::user();
        $query = PembangunanKawasan::with(['perumahan', 'pengawas', 'periodes.pengawas'])
            ->whereIn('status_pembangunan', ['proses', 'selesai', 'selesai dengan catatan']);

        if ($user->hasRole('PENGAWAS PROYEK (S&P)')) {
            $query->where('pengawas_id', $user->id);
        }

        if ($year !== 'all') {
            if ($month !== 'all') {
                $startRange = \Carbon\Carbon::createFromDate((int)$year, (int)$month, 1)->startOfMonth()->toDateTimeString();
                $endRange   = \Carbon\Carbon::createFromDate((int)$year, (int)$month, 1)->endOfMonth()->toDateTimeString();
            } else {
                $startRange = \Carbon\Carbon::createFromDate((int)$year, 1, 1)->startOfYear()->toDateTimeString();
                $endRange   = \Carbon\Carbon::createFromDate((int)$year, 12, 31)->endOfYear()->toDateTimeString();
            }

            $query->where(function ($q) use ($startRange, $endRange) {
                $q->where(function ($sub) use ($endRange) {
                    $sub->whereNotNull('tanggal_mulai')->where('tanggal_mulai', '<=', $endRange)
                        ->orWhere(function ($s2) use ($endRange) {
                            $s2->whereNull('tanggal_mulai')->where('created_at', '<=', $endRange);
                        });
                })->where(function ($sub) use ($startRange) {
                    $sub->whereNull('tanggal_selesai')
                        ->orWhere('tanggal_selesai', '>=', $startRange);
                });
            });
        }

        $allPembangunanKawasan = $query->latest('created_at')->get();

        $months = [
            'all' => 'Semua Bulan',
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        $currentYear = (int) date('Y');
        $years = ['all' => 'Semua Tahun'];
        for ($y = $currentYear - 5; $y <= $currentYear + 2; $y++) {
            $years[(string)$y] = (string)$y;
        }

        return view('produksi.pembangunan_kawasan.index', [
            'allPembangunanKawasan' => $allPembangunanKawasan,
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'months' => $months,
            'years' => $years,
            'breadcrumbs' => [['label' => 'Pembangunan Kawasan', 'url' => route('produksi.pembangunanKawasan.index')]],
        ]);
    }

    public function show($id)
    {
        $data = PembangunanKawasan::with(['perumahan', 'pengawas', 'periodes.pengawas', 'orders.pembuat', 'orders.accUser', 'orders.details.barang', 'orders.details.satuanModel', 'pengajuanUpah'])->findOrFail($id);
        
        $returns = PembangunanKawasanBarangReturn::with(['details.barang.baseUnit', 'details.satuanModel', 'createdBy', 'accBy'])
            ->where('pembangunan_kawasan_id', $id)
            ->latest()
            ->get();

        $activePeriode = $data->periodes()->where('status', 'proses')->latest()->first();
        $activePeriodeId = $activePeriode?->id;

        // Calculate received vs returned per barang for the ACTIVE SESSION of this kawasan
        $orderedDetailsQuery = PembangunanKawasanBarangOrderDetail::query()
            ->join('pembangunan_kawasan_barang_order as o', 'o.id', '=', 'pembangunan_kawasan_barang_order_detail.order_id')
            ->where('o.pembangunan_kawasan_id', $id)
            ->where('o.status_order', 'selesai');

        if ($activePeriodeId) {
            $orderedDetailsQuery->where('o.pembangunan_kawasan_periode_id', $activePeriodeId);
            $orderedDetails = $orderedDetailsQuery
                ->select([
                    'pembangunan_kawasan_barang_order_detail.barang_id',
                    DB::raw('MAX(pembangunan_kawasan_barang_order_detail.nama_barang) as nama_barang'),
                    DB::raw('SUM(pembangunan_kawasan_barang_order_detail.jumlah_base) as total_diterima_base'),
                    DB::raw('GROUP_CONCAT(DISTINCT o.jenis_order) as jenis_orders'),
                ])
                ->groupBy('pembangunan_kawasan_barang_order_detail.barang_id')
                ->get();
        } else {
            $orderedDetails = collect();
        }

        $returnedBaseMapQuery = PembangunanKawasanBarangReturnDetail::query()
            ->join('pembangunan_kawasan_barang_returns as r', 'r.id', '=', 'pembangunan_kawasan_barang_return_details.return_id')
            ->where('r.pembangunan_kawasan_id', $id)
            ->where('r.status', 'selesai');

        if ($activePeriodeId) {
            $returnedBaseMapQuery->where('r.pembangunan_kawasan_periode_id', $activePeriodeId);
        }

        $returnedBaseMap = $returnedBaseMapQuery
            ->select([
                'pembangunan_kawasan_barang_return_details.barang_id',
                DB::raw('SUM(pembangunan_kawasan_barang_return_details.jumlah_base) as total_returned_base'),
            ])
            ->groupBy('pembangunan_kawasan_barang_return_details.barang_id')
            ->pluck('total_returned_base', 'barang_id')
            ->toArray();

        $returnableBarang = $orderedDetails->map(function ($d) use ($returnedBaseMap) {
            $totalDiterimaBase = (float) $d->total_diterima_base;
            $sudahReturBase    = (float) ($returnedBaseMap[$d->barang_id] ?? 0);
            $sisaBase          = max(0, $totalDiterimaBase - $sudahReturBase);

            $masterBarang = MasterBarang::with(['baseUnit', 'satuanKonversi.satuan'])->find($d->barang_id);
            $baseSatuanNama = $masterBarang?->baseUnit?->nama ?? 'Unit';
            $baseSatuanId   = $masterBarang?->base_unit_id;

            $satuanOptions = [];
            if ($baseSatuanId) {
                $satuanOptions[] = [
                    'satuan_id'        => $baseSatuanId,
                    'nama_satuan'      => $baseSatuanNama,
                    'konversi_ke_base' => 1.0,
                    'is_base'          => true,
                ];
            }

            if ($masterBarang?->satuanKonversi) {
                foreach ($masterBarang->satuanKonversi as $konv) {
                    if ($konv->satuan_id != $baseSatuanId && $konv->satuan) {
                        $satuanOptions[] = [
                            'satuan_id'        => $konv->satuan_id,
                            'nama_satuan'      => $konv->satuan->nama,
                            'konversi_ke_base' => (float) $konv->konversi_ke_base,
                            'is_base'          => false,
                        ];
                    }
                }
            }

            return [
                'barang_id'           => $d->barang_id,
                'nama_barang'         => $masterBarang->nama_barang ?? $d->nama_barang,
                'total_diterima_base' => $totalDiterimaBase,
                'sudah_retur_base'    => $sudahReturBase,
                'sisa_retur_base'     => $sisaBase,
                'base_satuan_nama'    => $baseSatuanNama,
                'satuan_options'      => $satuanOptions,
                'jenis_orders'        => array_filter(explode(',', $d->jenis_orders ?? '')),
            ];
        })->values();

        $allBarang = MasterBarang::with(['satuanKonversi.satuan'])
            ->select('id', 'kode_barang', 'nama_barang', 'is_stock')
            ->get()
            ->map(function ($barang) {
                return [
                    'id' => $barang->id,
                    'kode_barang' => $barang->kode_barang,
                    'nama_barang' => $barang->nama_barang,
                    'is_stock' => $barang->is_stock,
                    'satuans' => $barang->satuanKonversi->map(function ($konversi) {
                        return [
                            'id' => $konversi->satuan->id ?? null,
                            'nama_satuan' => $konversi->satuan->nama ?? 'Unknown',
                            'is_base' => $konversi->is_default,
                            'nilai_konversi' => $konversi->konversi_ke_base,
                        ];
                    }),
                ];
            });

        $penamaanUpah = MasterUpah::all();

        return view('produksi.pembangunan_kawasan.show', compact('data', 'allBarang', 'penamaanUpah', 'returns', 'returnableBarang'));
    }

    public function update(Request $request, $id)
    {
        $kawasan = PembangunanKawasan::findOrFail($id);
        
        if ($kawasan->status_pembangunan === 'selesai') {
            return redirect()->back()->with('error', 'Kawasan ini sudah selesai, status tidak dapat diubah lagi.');
        }

        $request->validate([
            'status_pembangunan' => 'required|in:proses,selesai,selesai dengan catatan',
        ]);

        $kawasan->update([
            'status_pembangunan' => $request->status_pembangunan,
        ]);

        $activePeriode = $kawasan->periodes()->where('status', 'proses')->first();
        if ($activePeriode) {
            $activePeriode->update(['status' => $request->status_pembangunan]);
        }

        return redirect()->back()->with('success', 'Status kawasan berhasil diperbarui');
    }

    public function orderStore(Request $request)
    {
        $request->validate([
            'pembangunan_kawasan_id' => 'required|exists:pembangunan_kawasan,id',
            'jenis_order' => 'required|in:stock,direct',
            'catatan' => 'nullable|string',
            'barang' => 'required|array',
            'barang.*.id' => 'required',
            'barang.*.jumlah_input' => 'required|numeric|min:0.0001',
            'barang.*.satuan_id' => 'required'
        ]);

        $kawasan = PembangunanKawasan::with('perumahan')->find($request->pembangunan_kawasan_id);
        
        if (!$kawasan) {
            return response()->json(['message' => 'Kawasan tidak ditemukan'], 404);
        }

        if ($kawasan->status_pembangunan === 'selesai') {
            return response()->json(['message' => 'Kawasan ini sudah selesai, tidak dapat melakukan order barang.'], 422);
        }

        $namaPerumahan = $kawasan?->perumahan?->nama_perumahaan;
        $ubsId = $namaPerumahan ? \App\Models\Ubs::where('nama_ubs', $namaPerumahan)->value('id') : null;

        try {
            DB::beginTransaction();

            $datePrefix = 'ORD-KWS-' . now()->format('Ymd') . '-';
            $lastOrder = PembangunanKawasanBarangOrder::where('nomor_order', 'like', $datePrefix . '%')
                ->orderBy('nomor_order', 'desc')
                ->lockForUpdate()
                ->first();

            $nextSeq = 1;
            if ($lastOrder) {
                $lastSeq = (int) substr($lastOrder->nomor_order, strlen($datePrefix));
                $nextSeq = $lastSeq + 1;
            }
            $nomorOrder = $datePrefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

            $activePeriode = PembangunanKawasanPeriode::where('pembangunan_kawasan_id', $request->pembangunan_kawasan_id)
                ->where('status', 'proses')
                ->latest()
                ->first();

            $order = PembangunanKawasanBarangOrder::create([
                'nomor_order' => $nomorOrder,
                'pembangunan_kawasan_id' => $request->pembangunan_kawasan_id,
                'pembangunan_kawasan_periode_id' => $activePeriode?->id,
                'jenis_order' => $request->jenis_order,
                'catatan' => $request->catatan,
                'tanggal_diajukan' => now(),
                'status_order' => 'diproses',
                'created_by' => Auth::id(),
                'ubs_id' => $ubsId
            ]);

            foreach ($request->barang as $item) {
                $barang = MasterBarang::find($item['id']);
                $namaBarang = $barang ? $barang->nama_barang : 'Barang tidak ditemukan';
                $konversi = \App\Models\BarangSatuanKonversi::where('barang_id', $item['id'])
                            ->where('satuan_id', $item['satuan_id'])->first();
                $jumlahBase = $konversi ? ($item['jumlah_input'] * $konversi->konversi_ke_base) : $item['jumlah_input'];

                PembangunanKawasanBarangOrderDetail::create([
                    'order_id' => $order->id,
                    'barang_id' => $item['id'],
                    'satuan_id' => $item['satuan_id'],
                    'jumlah_input' => $item['jumlah_input'],
                    'nama_barang' => $namaBarang,
                    'satuan' => \App\Models\MasterSatuan::find($item['satuan_id'])->nama ?? '',
                    'jumlah_base' => $jumlahBase,
                    'ubs_id' => $ubsId
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();

            if ($kawasan) {
                $this->sendGroupNotificationOrder($kawasan, $order);
            }

            return response()->json(['message' => 'Order barang berhasil diajukan', 'order_id' => $order->id]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan order: ' . $e->getMessage()], 500);
        }
    }

    public function returnStore(Request $request)
    {
        $request->validate([
            'pembangunan_kawasan_id' => 'required|exists:pembangunan_kawasan,id',
            'catatan' => 'nullable|string',
            'items' => 'required|array',
            'items.*.barang_id' => 'required|exists:master_barang,id',
            'items.*.satuan_id' => 'required|exists:master_satuan,id',
            'items.*.jumlah_input' => 'required|numeric|min:0.0001',
            'items.*.keterangan' => 'nullable|string'
        ]);

        $kawasan = PembangunanKawasan::findOrFail($request->pembangunan_kawasan_id);
        if ($kawasan->status_pembangunan === 'selesai') {
            return redirect()->back()->with('error', 'Kawasan ini sudah selesai, tidak dapat melakukan retur barang.');
        }

        try {
            DB::beginTransaction();

            $datePrefix = 'RTN-KWS-' . now()->format('Ymd') . '-';
            $lastReturn = PembangunanKawasanBarangReturn::where('nomor_return', 'like', $datePrefix . '%')
                ->orderBy('nomor_return', 'desc')
                ->lockForUpdate()
                ->first();

            $seq = 1;
            if ($lastReturn) {
                $seq = (int)substr($lastReturn->nomor_return, strlen($datePrefix)) + 1;
            }
            $nomorReturn = $datePrefix . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $lastOrder = PembangunanKawasanBarangOrder::where('pembangunan_kawasan_id', $kawasan->id)->where('status_order', 'selesai')->latest()->first();

            $activePeriode = PembangunanKawasanPeriode::where('pembangunan_kawasan_id', $kawasan->id)
                ->where('status', 'proses')
                ->latest()
                ->first();

            $returnRequest = PembangunanKawasanBarangReturn::create([
                'pembangunan_kawasan_id' => $kawasan->id,
                'pembangunan_kawasan_periode_id' => $activePeriode?->id,
                'order_id' => $lastOrder?->id,
                'nomor_return' => $nomorReturn,
                'tanggal_return' => now(),
                'tanggal_diajukan' => now(),
                'catatan' => $request->catatan,
                'status' => 'diproses',
                'diajukan_oleh' => Auth::id()
            ]);

            foreach ($request->items as $item) {
                if ((float)$item['jumlah_input'] > 0) {
                    $barang = MasterBarang::find($item['barang_id']);
                    $satuan = \App\Models\MasterSatuan::find($item['satuan_id']);
                    $konversi = BarangSatuanKonversi::where('barang_id', $item['barang_id'])
                        ->where('satuan_id', $item['satuan_id'])->first();
                    $jumlahBase = $konversi ? ($item['jumlah_input'] * $konversi->konversi_ke_base) : $item['jumlah_input'];

                    PembangunanKawasanBarangReturnDetail::create([
                        'return_id' => $returnRequest->id,
                        'order_detail_id' => null,
                        'barang_id' => $item['barang_id'],
                        'satuan_id' => $item['satuan_id'],
                        'satuan' => $satuan->nama ?? '',
                        'nama_barang' => $barang->nama_barang ?? '',
                        'jumlah_input' => $item['jumlah_input'],
                        'jumlah_base' => $jumlahBase,
                        'jumlah_return' => $item['jumlah_input'],
                        'keterangan' => $item['keterangan'] ?? null,
                    ]);
                }
            }

            DB::commit();

            $this->sendGroupNotificationReturn($kawasan, $returnRequest);

            return redirect()->back()->with('success', 'Pengajuan retur barang kawasan berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan retur: ' . $e->getMessage());
        }
    }

    public function upahStore(Request $request)
    {
        $kawasan = PembangunanKawasan::findOrFail($request->pembangunan_kawasan_id);
        if ($kawasan->status_pembangunan === 'selesai') {
            return redirect()->back()->with('error', 'Kawasan ini sudah selesai, tidak dapat mengajukan upah.');
        }

        $request->validate([
            'pembangunan_kawasan_id' => 'required|exists:pembangunan_kawasan,id',
            'nama_upah' => 'required|string',
            'nominal_diajukan' => 'required|numeric|min:0',
            'catatan_pengawas' => 'nullable|string',
        ]);

        PembangunanKawasanUpahPengajuan::create([
            'pembangunan_kawasan_id' => $request->pembangunan_kawasan_id,
            'nama_upah' => $request->nama_upah,
            'nominal_diajukan' => $request->nominal_diajukan,
            'catatan_pengawas' => $request->catatan_pengawas,
            'status_pengajuan' => 'req_mgr_produksi',
            'tanggal_diajukan' => now(),
        ]);

        return redirect()->back()->with('success', 'Pengajuan upah berhasil dibuat');
    }

    public function orderDestroy($id)
    {
        $order = PembangunanKawasanBarangOrder::with(['details'])->findOrFail($id);

        if ($order->status_order !== 'diproses') {
            return redirect()->back()->with('error', 'Gagal membatalkan order! Order ini sudah tidak dalam status menunggu.');
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $kawasan = PembangunanKawasan::find($order->pembangunan_kawasan_id);
            if ($kawasan) {
                $this->sendGroupNotificationCancelOrder($kawasan, $order);
            }

            $order->details()->delete();
            $order->delete();

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->back()->with('success', 'Order barang berhasil dibatalkan.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    protected function sendGroupNotificationCancelOrder(PembangunanKawasan $kawasan, $order)
    {
        $kawasan->loadMissing(['pengawas', 'perumahan']);
        $order->loadMissing(['details']);

        $groupId = env('FONNTE_ID_GROUP_BATAL_ORDER_BARANG_KAWASAN');
        if (!$groupId) return;

        $messageGroup = view('notifications.whatsapp.pembangunan_kawasan.batal_order_barang', [
            'tipe' => 'Kawasan',
            'namaPerumahan' => $kawasan->perumahan->nama_perumahaan ?? '-',
            'namaKawasan' => $kawasan->nama ?? '-',
            'pembatal' => Auth::user()->nama_lengkap ?? Auth::user()->name,
            'tanggal' => now()->format('d/m/Y H:i') . ' WIB',
            'order' => $order
        ])->render();

        try {
            $this->notificationGroup->send($groupId, $messageGroup);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WA Cancel Kawasan Order Error: ' . $e->getMessage());
        }
    }

    public function upahDestroy($id)
    {
        $upah = PembangunanKawasanUpahPengajuan::findOrFail($id);

        if (!is_null($upah->disetujui_mgr_produksi) || !is_null($upah->disetujui_mgr_dukungan) || !is_null($upah->disetujui_akuntan) || !is_null($upah->ditolak_pada)) {
            return redirect()->back()->with('error', 'Gagal membatalkan pengajuan upah! Data sudah diproses.');
        }

        try {
            $upah->delete();
            return redirect()->back()->with('success', 'Pengajuan upah berhasil dibatalkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
