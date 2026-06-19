<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\MasterBarang;
use App\Models\PembangunanKawasan;
use App\Models\PembangunanKawasanBarangOrder;
use App\Models\PembangunanKawasanBarangOrderDetail;
use App\Models\PembangunanKawasanUpahPengajuan;
use App\Services\NotificationGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MasterUpah;

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
        $allPembangunanKawasan = PembangunanKawasan::with(['perumahan', 'pengawas'])
            ->whereIn('status_pembangunan', ['proses', 'selesai', 'selesai dengan catatan'])
            ->latest('created_at')
            ->get();

        return view('produksi.pembangunan_kawasan.index', [
            'allPembangunanKawasan' => $allPembangunanKawasan,
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
            'status_pembangunan' => 'required|in:selesai,selesai dengan catatan',
            'catatan' => 'nullable|string'
        ]);

        $kawasan->update([
            'status_pembangunan' => $request->status_pembangunan,
            'catatan' => $request->catatan,
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

        $order = PembangunanKawasanBarangOrder::create([
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

        if ($kawasan) {
            $this->sendGroupNotificationOrder($kawasan, $order);
        }

        return redirect()->back()->with('success', 'Order barang berhasil diajukan');
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
        
        // Find existing return request or create new
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

        // Update order status if not already pengembalian
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
}
