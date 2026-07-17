<?php

namespace App\Http\Controllers\Produksi\PembangunanKawasan;

use App\Http\Controllers\Controller;
use App\Models\MasterBarang;
use App\Models\MasterUpah;
use App\Models\PembangunanKawasan;
use App\Models\PembangunanKawasanBarangOrder;
use App\Models\PembangunanKawasanBarangOrderDetail;
use App\Models\PembangunanKawasanUpahPengajuan;
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

        $groupId = env('FONNTE_ID_GROUP_ORDER_BARANG_KAWASAN');
        if (!$groupId) return;

        $messageGroup = view('notifications.whatsapp.order_barang', [
            'tipe' => 'Kawasan',
            'namaPerumahan' => $kawasan->perumahan->nama_perumahaan ?? '-',
            'namaArea' => $kawasan->nama ?? '-',
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

        $groupId = env('FONNTE_ID_GROUP_RETUR_BARANG_KAWASAN');
        if (!$groupId) return;

        $messageGroup = view('notifications.whatsapp.retur_barang', [
            'tipe' => 'Kawasan',
            'namaPerumahan' => $kawasan->perumahan->nama_perumahaan ?? '-',
            'namaArea' => $kawasan->nama ?? '-',
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
        $query = PembangunanKawasan::with(['perumahan', 'pengawas'])
            ->whereIn('status_pembangunan', ['proses', 'selesai'])
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->latest('created_at');

        if ($user->hasRole('Pengawas Kawasan')) {
            $query->where('pengawas_id', $user->id);
        }

        $allPembangunanKawasan = $query->get();

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
        $data = PembangunanKawasan::with(['perumahan', 'pengawas', 'orders.details.barang', 'orders.details.satuanModel', 'pengajuanUpah'])->findOrFail($id);
        
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

        return view('produksi.pembangunan_kawasan.show', compact('data', 'allBarang', 'penamaanUpah'));
    }

    public function update(Request $request, $id)
    {
        $kawasan = PembangunanKawasan::findOrFail($id);
        $request->validate([
            'status_pembangunan' => 'required|in:proses,selesai',
        ]);

        $kawasan->update([
            'status_pembangunan' => $request->status_pembangunan,
        ]);

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
            'barang.*.jumlah_input' => 'required|numeric|min:0.01',
            'barang.*.satuan_id' => 'required'
        ]);

        $kawasan = PembangunanKawasan::with('perumahan')->find($request->pembangunan_kawasan_id);
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

            $order = PembangunanKawasanBarangOrder::create([
                'nomor_order' => $nomorOrder,
                'pembangunan_kawasan_id' => $request->pembangunan_kawasan_id,
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

            return redirect()->back()->with('success', 'Order barang berhasil diajukan');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan order: ' . $e->getMessage());
        }
    }

    public function returnStore(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:pembangunan_kawasan_barang_order,id',
            'returns' => 'required|array',
            'returns.*.order_detail_id' => 'required|exists:pembangunan_kawasan_barang_order_detail,id',
            'returns.*.jumlah_return' => 'required|numeric|min:0',
            'returns.*.keterangan' => 'nullable|string'
        ]);

        $order = PembangunanKawasanBarangOrder::findOrFail($request->order_id);
        
        $returnRequest = \App\Models\PembangunanKawasanBarangReturn::firstOrCreate(
            ['order_id' => $order->id],
            [
                'pembangunan_kawasan_id' => $order->pembangunan_kawasan_id,
                'tanggal_diajukan' => now(),
                'status' => 'pending',
                'diajukan_oleh' => Auth::id()
            ]
        );

        foreach ($request->returns as $ret) {
            if ($ret['jumlah_return'] > 0) {
                $orderDetail = PembangunanKawasanBarangOrderDetail::find($ret['order_detail_id']);
                
                \App\Models\PembangunanKawasanBarangReturnDetail::updateOrCreate(
                    [
                        'return_id' => $returnRequest->id,
                        'order_detail_id' => $ret['order_detail_id']
                    ],
                    [
                        'barang_id' => $orderDetail->barang_id,
                        'jumlah_return' => $ret['jumlah_return'],
                        'satuan' => $orderDetail->satuan,
                        'keterangan_return' => $ret['keterangan']
                    ]
                );
            }
        }

        if ($order->status_order !== 'pengembalian') {
            $order->update(['status_order' => 'pengembalian']);
        }

        $kawasan = PembangunanKawasan::find($order->pembangunan_kawasan_id);
        if ($kawasan) {
            $this->sendGroupNotificationReturn($kawasan, $returnRequest);
        }

        return redirect()->back()->with('success', 'Pengajuan retur barang berhasil disimpan');
    }

    public function upahStore(Request $request)
    {
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

        $messageGroup = view('notifications.whatsapp.batal_order_barang_kawasan', [
            'tipe' => 'Kawasan',
            'namaPerumahan' => $kawasan->perumahan->nama_perumahaan ?? '-',
            'namaArea' => $kawasan->nama ?? '-',
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
