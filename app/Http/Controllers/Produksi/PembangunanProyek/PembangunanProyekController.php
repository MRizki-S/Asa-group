<?php

namespace App\Http\Controllers\Produksi\PembangunanProyek;

use App\Http\Controllers\Controller;
use App\Models\MasterBarang;
use App\Models\MasterUpah;
use App\Models\PembangunanProyek;
use App\Models\PembangunanProyekBarangOrder;
use App\Models\PembangunanProyekBarangOrderDetail;
use App\Models\PembangunanProyekBarangReturn;
use App\Models\PembangunanProyekBarangReturnDetail;
use App\Models\BarangSatuanKonversi;
use App\Models\PembangunanProyekUpahPengajuan;
use App\Services\NotificationGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PembangunanProyekController extends Controller
{
    protected NotificationGroupService $notificationGroup;

    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }

    protected function sendGroupNotificationOrder(PembangunanProyek $project, $order)
    {
        $project->loadMissing(['pengawas']);
        $order->loadMissing(['details']);

        $groupId = env('FONNTE_ID_ORDER_BARANG_PROYEK', env('FONNTE_ID_ORDER_BARANG_ABM'));
        if (!$groupId) return;

        $messageGroup = view('notifications.whatsapp.pembangunan_proyek.order_barang', [
            'tipe' => 'Proyek',
            'namaProyek' => $project->nama_project ?? $project->nama ?? '-',
            'pengawas' => $project->pengawas?->nama_lengkap ?? $project->pengawas?->name ?? '-',
            'pengaju' => Auth::user()->nama_lengkap ?? Auth::user()->name,
            'tanggal' => now()->format('d/m/Y H:i') . ' WIB',
            'order' => $order
        ])->render();

        try {
            $this->notificationGroup->send($groupId, $messageGroup);
        } catch (\Exception $e) {
        }
    }

    protected function sendGroupNotificationReturn(PembangunanProyek $project, $return)
    {
        $project->loadMissing(['pengawas']);
        $return->loadMissing(['details.orderDetail']);

        $groupId = env('FONNTE_ID_RETURN_BARANG_PROYEK', env('FONNTE_ID_ORDER_BARANG_ABM'));
        if (!$groupId) return;

        $messageGroup = view('notifications.whatsapp.pembangunan_proyek.retur_barang', [
            'tipe' => 'Proyek',
            'namaProyek' => $project->nama_project ?? $project->nama ?? '-',
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
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $user = Auth::user();
        $query = PembangunanProyek::with(['pengawas'])
            ->whereIn('status_pembangunan', ['proses', 'selesai'])
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->latest('created_at');

        if ($user->hasRole('Pengawas Proyek Mangoon')) {
            $query->where('pengawas_id', $user->id);
        }

        $allPembangunanProyek = $query->get();

        $months = [
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
        $years = range(date('Y') - 5, date('Y') + 2);

        return view('produksi.pembangunan_proyek.index', [
            'allPembangunanProyek' => $allPembangunanProyek,
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'months' => $months,
            'years' => $years,
            'breadcrumbs' => [['label' => 'Pembangunan Proyek Kontraktor', 'url' => route('produksi.pembangunanProyek.index')]],
        ]);
    }

    public function show($id)
    {
        $data = PembangunanProyek::with(['pengawas', 'orders.details.barang', 'orders.details.satuanModel', 'pengajuanUpah'])->findOrFail($id);

        $returns = PembangunanProyekBarangReturn::with(['details.barang.baseUnit', 'details.satuanModel', 'createdBy', 'accBy'])
            ->where('pembangunan_proyek_id', $id)
            ->latest()
            ->get();

        // Calculate received vs returned per barang for this proyek
        $orderedDetails = PembangunanProyekBarangOrderDetail::query()
            ->join('pembangunan_proyek_barang_order as o', 'o.id', '=', 'pembangunan_proyek_barang_order_detail.order_id')
            ->where('o.pembangunan_proyek_id', $id)
            ->where('o.status_order', 'selesai')
            ->select([
                'pembangunan_proyek_barang_order_detail.barang_id',
                DB::raw('MAX(pembangunan_proyek_barang_order_detail.nama_barang) as nama_barang'),
                DB::raw('SUM(pembangunan_proyek_barang_order_detail.jumlah_base) as total_diterima_base'),
            ])
            ->groupBy('pembangunan_proyek_barang_order_detail.barang_id')
            ->get();

        $returnedBaseMap = PembangunanProyekBarangReturnDetail::query()
            ->join('pembangunan_proyek_barang_returns as r', 'r.id', '=', 'pembangunan_proyek_barang_return_details.return_id')
            ->where('r.pembangunan_proyek_id', $id)
            ->whereIn('r.status', ['diproses', 'selesai'])
            ->select([
                'pembangunan_proyek_barang_return_details.barang_id',
                DB::raw('SUM(pembangunan_proyek_barang_return_details.jumlah_base) as total_returned_base'),
            ])
            ->groupBy('pembangunan_proyek_barang_return_details.barang_id')
            ->pluck('total_returned_base', 'barang_id')
            ->toArray();

        $returnableBarang = $orderedDetails->map(function ($d) use ($returnedBaseMap) {
            $totalDiterimaBase = (float) $d->total_diterima_base;
            $sudahReturBase    = (float) ($returnedBaseMap[$d->barang_id] ?? 0);
            $sisaBase          = max(0, $totalDiterimaBase - $sudahReturBase);

            if ($sisaBase <= 0.0001) {
                return null;
            }

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
            ];
        })->filter()->values();

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

        return view('produksi.pembangunan_proyek.show', compact('data', 'allBarang', 'penamaanUpah', 'returns', 'returnableBarang'));
    }

    public function update(Request $request, $id)
    {
        $project = PembangunanProyek::findOrFail($id);
        
        if ($project->status_pembangunan === 'selesai') {
            return redirect()->back()->with('error', 'Proyek ini sudah selesai, status tidak dapat diubah lagi.');
        }

        $request->validate([
            'status_pembangunan' => 'required|in:proses,selesai',
        ]);

        $project->update([
            'status_pembangunan' => $request->status_pembangunan,
        ]);

        return redirect()->back()->with('success', 'Status proyek berhasil diperbarui');
    }

    public function orderStore(Request $request)
    {
        $project = PembangunanProyek::find($request->pembangunan_proyek_id);
        
        if (!$project) {
            return redirect()->back()->with('error', 'Proyek tidak ditemukan');
        }

        if ($project->status_pembangunan === 'selesai') {
            return redirect()->back()->with('error', 'Proyek ini sudah selesai, tidak dapat melakukan order barang.');
        }

        $request->validate([
            'pembangunan_proyek_id' => 'required|exists:pembangunan_proyek,id',
            'jenis_order' => 'required|in:stock,direct',
            'catatan' => 'nullable|string',
            'barang' => 'required|array',
            'barang.*.id' => 'required',
            'barang.*.jumlah_input' => 'required|numeric|min:0.01',
            'barang.*.satuan_id' => 'required'
        ]);

        try {
            DB::beginTransaction();

            $datePrefix = 'ORD-MGN-' . now()->format('Ymd') . '-';
            $lastOrder = PembangunanProyekBarangOrder::where('nomor_order', 'like', $datePrefix . '%')
                ->orderBy('nomor_order', 'desc')
                ->lockForUpdate()
                ->first();

            $nextSeq = 1;
            if ($lastOrder) {
                $lastSeq = (int) substr($lastOrder->nomor_order, strlen($datePrefix));
                $nextSeq = $lastSeq + 1;
            }
            $nomorOrder = $datePrefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

            $order = PembangunanProyekBarangOrder::create([
                'nomor_order' => $nomorOrder,
                'pembangunan_proyek_id' => $request->pembangunan_proyek_id,
                'jenis_order' => $request->jenis_order,
                'catatan' => $request->catatan,
                'tanggal_diajukan' => now(),
                'status_order' => 'diproses',
                'created_by' => Auth::id(),
                'ubs_id' => \App\Models\Ubs::where('kode_ubs', 'MGN')->value('id') ?? 3
            ]);

            $ubsId = \App\Models\Ubs::where('nama_ubs', 'Mangoon.id')->value('id');

            foreach ($request->barang as $item) {
                $barang = MasterBarang::find($item['id']);
                $namaBarang = $barang ? $barang->nama_barang : 'Barang tidak ditemukan';
                $konversi = \App\Models\BarangSatuanKonversi::where('barang_id', $item['id'])
                            ->where('satuan_id', $item['satuan_id'])->first();
                $jumlahBase = $konversi ? ($item['jumlah_input'] * $konversi->konversi_ke_base) : $item['jumlah_input'];

                PembangunanProyekBarangOrderDetail::create([
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

            $project = PembangunanProyek::find($request->pembangunan_proyek_id);
            if ($project) {
                $this->sendGroupNotificationOrder($project, $order);
            }

            return redirect()->back()->with('success', 'Order barang berhasil diajukan');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan order: ' . $e->getMessage());
        }
    }

    public function returnStore(Request $request)
    {
        $request->validate([
            'pembangunan_proyek_id' => 'required|exists:pembangunan_proyek,id',
            'catatan' => 'nullable|string',
            'items' => 'required|array',
            'items.*.barang_id' => 'required|exists:master_barang,id',
            'items.*.satuan_id' => 'required|exists:master_satuan,id',
            'items.*.jumlah_input' => 'required|numeric|min:0.001',
            'items.*.keterangan' => 'nullable|string'
        ]);

        $project = PembangunanProyek::findOrFail($request->pembangunan_proyek_id);
        if ($project->status_pembangunan === 'selesai') {
            return redirect()->back()->with('error', 'Proyek ini sudah selesai, tidak dapat melakukan retur barang.');
        }

        try {
            DB::beginTransaction();

            $datePrefix = 'RTN-MGN-' . now()->format('Ymd') . '-';
            $lastReturn = PembangunanProyekBarangReturn::where('nomor_return', 'like', $datePrefix . '%')
                ->orderBy('nomor_return', 'desc')
                ->lockForUpdate()
                ->first();

            $seq = 1;
            if ($lastReturn) {
                $seq = (int)substr($lastReturn->nomor_return, strlen($datePrefix)) + 1;
            }
            $nomorReturn = $datePrefix . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $lastOrder = PembangunanProyekBarangOrder::where('pembangunan_proyek_id', $project->id)->where('status_order', 'selesai')->latest()->first();

            $returnRequest = PembangunanProyekBarangReturn::create([
                'pembangunan_proyek_id' => $project->id,
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
                    $konversi = \App\Models\BarangSatuanKonversi::where('barang_id', $item['barang_id'])
                        ->where('satuan_id', $item['satuan_id'])->first();
                    $jumlahBase = $konversi ? ($item['jumlah_input'] * $konversi->konversi_ke_base) : $item['jumlah_input'];

                    PembangunanProyekBarangReturnDetail::create([
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

            $this->sendGroupNotificationReturn($project, $returnRequest);

            return redirect()->back()->with('success', 'Pengajuan retur barang proyek berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan retur: ' . $e->getMessage());
        }
    }

    public function upahStore(Request $request)
    {
        $project = PembangunanProyek::findOrFail($request->pembangunan_proyek_id);
        if ($project->status_pembangunan === 'selesai') {
            return redirect()->back()->with('error', 'Proyek ini sudah selesai, tidak dapat mengajukan upah.');
        }

        $request->validate([
            'pembangunan_proyek_id' => 'required|exists:pembangunan_proyek,id',
            'nama_upah' => 'required|string',
            'nominal_diajukan' => 'required|numeric|min:0',
            'catatan_pengawas' => 'nullable|string',
        ]);

        PembangunanProyekUpahPengajuan::create([
            'pembangunan_proyek_id' => $request->pembangunan_proyek_id,
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
        $order = PembangunanProyekBarangOrder::with(['details'])->findOrFail($id);

        if ($order->status_order !== 'diproses') {
            return redirect()->back()->with('error', 'Gagal membatalkan order! Order ini sudah tidak dalam status menunggu.');
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $project = PembangunanProyek::find($order->pembangunan_proyek_id);
            if ($project) {
                $this->sendGroupNotificationCancelOrder($project, $order);
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

    protected function sendGroupNotificationCancelOrder(PembangunanProyek $project, $order)
    {
        $project->loadMissing(['pengawas']);
        $order->loadMissing(['details']);

        $groupId = env('FONNTE_ID_GROUP_BATAL_ORDER_BARANG_PROYEK');
        if (!$groupId) return;

        $messageGroup = view('notifications.whatsapp.pembangunan_proyek.batal_order_barang', [
            'tipe' => 'Proyek',
            'namaProyek' => $project->nama_project ?? $project->nama ?? '-',
            'pembatal' => Auth::user()->nama_lengkap ?? Auth::user()->name,
            'tanggal' => now()->format('d/m/Y H:i') . ' WIB',
            'order' => $order
        ])->render();

        try {
            $this->notificationGroup->send($groupId, $messageGroup);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WA Cancel Proyek Order Error: ' . $e->getMessage());
        }
    }

    public function upahDestroy($id)
    {
        $upah = PembangunanProyekUpahPengajuan::findOrFail($id);

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
