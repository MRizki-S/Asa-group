<?php

namespace App\Http\Controllers\Produksi\PembangunanProyek;

use App\Http\Controllers\Controller;
use App\Models\MasterBarang;
use App\Models\MasterSatuan;
use App\Models\PembangunanProyek;
use App\Models\PembangunanProyekBarangOrder;
use App\Models\PembangunanProyekBarangOrderDetail;
use App\Models\Ubs;
use App\Services\NotificationGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PembangunanProyekOrderBarangController extends Controller
{
    protected NotificationGroupService $notificationGroup;

    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }

    public function sendGroupNotificationOrder(PembangunanProyek $proyek, $order)
    {
        $proyek->loadMissing(['pengawas']);
        $order->loadMissing(['details']);

        $groupId = env('FONNTE_ID_GROUP_ORDER_BARANG_PROYEK', env('FONNTE_ID_ORDER_BARANG_PROYEK', env('FONNTE_ID_ORDER_BARANG_ABM')));
        if (!$groupId) return;

        $namaProyek = $proyek->nama_project ?? $proyek->nama ?? '-';
        $pengawas = $proyek->pengawas?->nama_lengkap ?? $proyek->pengawas?->name ?? '-';
        $pengaju = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Pengaju';

        $messageGroup = view('notifications.whatsapp.pembangunan_proyek.order_barang', [
            'namaProyek' => $namaProyek,
            'pengawas' => $pengawas,
            'pengaju' => $pengaju,
            'tanggalDiajukan' => ($order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') : now()->format('d/m/Y H:i')) . ' WIB',
            'tanggalNbk' => ($order->tanggal_diajukan ? \Carbon\Carbon::parse($order->tanggal_diajukan)->format('d/m/Y H:i') : ($order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') : now()->format('d/m/Y H:i'))) . ' WIB',
            'order' => $order
        ])->render();

        try {
            $this->notificationGroup->send($groupId, $messageGroup);
        } catch (\Exception $e) {
            Log::error('WA Error Proyek Order: ' . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = PembangunanProyekBarangOrder::with([
            'proyek.pengawas',
            'user',
            'details'
        ])->latest('tanggal_diajukan');

        if ($user->hasRole('PENGAWAS PROYEK (S&P)')) {
            $query->whereHas('proyek', function ($q) use ($user) {
                $q->where('pengawas_id', $user->id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status_order', $request->status);
        }

        $orders = $query->paginate(15);

        return view('produksi.pembangunan-proyek.order-barang.index', [
            'orders' => $orders,
            'breadcrumbs' => [
                ['label' => 'Pembangunan Proyek', 'url' => route('produksi.pembangunanProyek.index')],
                ['label' => 'Order Barang Proyek', 'url' => route('produksi.pembangunanProyek.orderIndex')],
            ],
        ]);
    }

    public function create(Request $request)
    {
        $user = Auth::user();

        $queryProyek = PembangunanProyek::with(['pengawas'])
            ->whereIn('status_pembangunan', ['proses', 'selesai', 'selesai dengan catatan']);

        $preselectProyekId = $request->get('pembangunan_proyek_id');

        if ($user->hasRole('PENGAWAS PROYEK (S&P)')) {
            $queryProyek->where(function ($q) use ($user, $preselectProyekId) {
                $q->where('pengawas_id', $user->id);
                if ($preselectProyekId) {
                    $q->orWhere('id', $preselectProyekId);
                }
            });
        }

        $pembangunanProyek = $queryProyek->get()->map(function ($pp) {
            $pp->is_selesai = ($pp->status_pembangunan === 'selesai');
            $pp->label_formatted = $pp->nama_project ?? $pp->nama ?? 'Proyek #' . $pp->id;
            $pp->pengawas_nama = $pp->pengawas?->nama_lengkap ?? $pp->pengawas?->name ?? '-';
            return $pp;
        });

        // UBS Mangoon.id
        $ubsId = Ubs::where('nama_ubs', 'like', '%mangoon%')->value('id') ?? 3;

        // Ambil barang gudang dengan stok UBS Mangoon
        $barangGudangQuery = MasterBarang::with(['baseUnit', 'satuanKonversi.satuan'])
            ->with(['stock' => function ($q) use ($ubsId) {
                $q->where('stock_type', 'UBS')->where('ubs_id', $ubsId);
            }]);

        $barangGudang = $barangGudangQuery->get()->map(function ($b) {
            $stokTotal = $b->stock ? $b->stock->sum('jumlah_stock') : 0;
            $satuans = collect();
            if ($b->baseUnit) {
                $satuans->push([
                    'id' => $b->base_unit_id,
                    'nama_satuan' => $b->baseUnit->nama,
                    'konversi_ke_base' => 1
                ]);
            }
            if ($b->satuanKonversi) {
                foreach ($b->satuanKonversi as $sk) {
                    if ($sk->satuan) {
                        $satuans->push([
                            'id' => $sk->satuan_id,
                            'nama_satuan' => $sk->satuan->nama,
                            'konversi_ke_base' => (float) $sk->konversi_ke_base
                        ]);
                    }
                }
            }

            return [
                'id' => $b->id,
                'kode_barang' => $b->kode_barang,
                'nama_barang' => $b->nama_barang,
                'is_stock' => (bool) $b->is_stock,
                'base_unit_id' => $b->base_unit_id,
                'base_unit_nama' => $b->baseUnit->nama ?? '',
                'stok_gudang' => (float) $stokTotal,
                'satuans' => $satuans->unique('id')->values()->toArray(),
            ];
        });

        return view('produksi.pembangunan-proyek.order-barang.create', [
            'pembangunanProyek' => $pembangunanProyek,
            'barangGudang' => $barangGudang,
            'preselectProyekId' => $preselectProyekId,
            'breadcrumbs' => [
                ['label' => 'Pembangunan Proyek', 'url' => route('produksi.pembangunanProyek.index')],
                ['label' => 'Order Barang Proyek', 'url' => route('produksi.pembangunanProyek.orderIndex')],
                ['label' => 'Buat Order Barang', 'url' => route('produksi.pembangunanProyek.orderCreate')],
            ],
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->has('items') && $request->has('barang')) {
            $request->merge([
                'items' => collect($request->input('barang'))->map(function ($b) {
                    return [
                        'barang_id' => $b['id'] ?? $b['barang_id'] ?? null,
                        'satuan_id' => $b['satuan_id'] ?? null,
                        'jumlah_input' => $b['jumlah_input'] ?? null,
                    ];
                })->toArray()
            ]);
        }

        $request->validate([
            'pembangunan_proyek_id' => 'required|exists:pembangunan_proyek,id',
            'jenis_order' => 'required|in:stock,direct',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:master_barang,id',
            'items.*.satuan_id' => 'required|exists:master_satuan,id',
            'items.*.jumlah_input' => 'required|numeric|min:0.0001',
        ]);

        $proyek = PembangunanProyek::find($request->pembangunan_proyek_id);

        if (!$proyek) {
            return response()->json(['message' => 'Proyek tidak ditemukan'], 404);
        }

        if ($proyek->status_pembangunan === 'selesai') {
            return response()->json(['message' => 'Proyek ini sudah selesai, tidak dapat melakukan order barang.'], 422);
        }

        $ubsId = Ubs::where('nama_ubs', 'like', '%mangoon%')->value('id') ?? 3;

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

            $tglDiajukan = now();
            if ($request->filled('tanggal_order')) {
                $tglStr = $request->tanggal_order;
                if ($request->filled('waktu_order')) {
                    $tglStr .= ' ' . $request->waktu_order;
                }
                try {
                    $tglDiajukan = \Carbon\Carbon::parse($tglStr);
                } catch (\Exception $e) {
                    $tglDiajukan = now();
                }
            }

            $order = PembangunanProyekBarangOrder::create([
                'nomor_order' => $nomorOrder,
                'pembangunan_proyek_id' => $request->pembangunan_proyek_id,
                'jenis_order' => $request->jenis_order,
                'tanggal_diajukan' => $tglDiajukan,
                'status_order' => 'diproses',
                'catatan' => $request->catatan,
                'created_by' => Auth::id(),
                'ubs_id' => $ubsId
            ]);

            foreach ($request->items as $item) {
                $barang = MasterBarang::find($item['barang_id']);
                $namaBarang = $barang ? $barang->nama_barang : 'Barang tidak ditemukan';
                $satuan = MasterSatuan::find($item['satuan_id']);
                $konversi = \App\Models\BarangSatuanKonversi::where('barang_id', $item['barang_id'])
                    ->where('satuan_id', $item['satuan_id'])->first();
                $jumlahBase = $konversi ? ($item['jumlah_input'] * $konversi->konversi_ke_base) : $item['jumlah_input'];

                PembangunanProyekBarangOrderDetail::create([
                    'order_id' => $order->id,
                    'barang_id' => $item['barang_id'],
                    'satuan_id' => $item['satuan_id'],
                    'jumlah_input' => $item['jumlah_input'],
                    'nama_barang' => $namaBarang,
                    'satuan' => $satuan->nama ?? '',
                    'jumlah_base' => $jumlahBase,
                    'ubs_id' => $ubsId
                ]);
            }

            DB::commit();

            if ($proyek) {
                $this->sendGroupNotificationOrder($proyek, $order);
            }

            return response()->json(['message' => 'Order barang proyek berhasil diajukan', 'order_id' => $order->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan order: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $order = PembangunanProyekBarangOrder::with(['details'])->findOrFail($id);

        if ($order->status_order !== 'diproses') {
            return redirect()->back()->with('error', 'Gagal membatalkan order! Order ini sudah tidak dalam status menunggu.');
        }

        try {
            DB::beginTransaction();

            $proyek = PembangunanProyek::find($order->pembangunan_proyek_id);
            if ($proyek) {
                $this->sendGroupNotificationCancelOrder($proyek, $order);
            }

            $order->details()->delete();
            $order->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Order barang proyek berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    protected function sendGroupNotificationCancelOrder(PembangunanProyek $proyek, $order)
    {
        $proyek->loadMissing(['pengawas']);
        $order->loadMissing(['details']);

        $groupId = env('FONNTE_ID_GROUP_BATAL_ORDER_BARANG_PROYEK');
        if (!$groupId) return;

        $namaProyek = $proyek->nama_project ?? $proyek->nama ?? '-';
        $pembatal = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Pengguna';

        $messageGroup = view('notifications.whatsapp.pembangunan_proyek.batal_order_barang', [
            'namaProyek' => $namaProyek,
            'pembatal' => $pembatal,
            'tanggal' => now()->format('d/m/Y H:i') . ' WIB',
            'order' => $order
        ])->render();

        try {
            $this->notificationGroup->send($groupId, $messageGroup);
        } catch (\Exception $e) {
            Log::error('WA Cancel Proyek Order Error: ' . $e->getMessage());
        }
    }
}
