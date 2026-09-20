<?php

namespace App\Http\Controllers\Produksi\PembangunanKawasan;

use App\Http\Controllers\Controller;
use App\Models\MasterBarang;
use App\Models\MasterSatuan;
use App\Models\PembangunanKawasan;
use App\Models\PembangunanKawasanBarangOrder;
use App\Models\PembangunanKawasanBarangOrderDetail;
use App\Models\PembangunanKawasanPeriode;
use App\Services\NotificationGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PembangunanKawasanOrderBarangController extends Controller
{
    protected NotificationGroupService $notificationGroup;

    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }

    public function sendGroupNotificationOrder(PembangunanKawasan $kawasan, $order)
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
            'tanggalDiajukan' => ($order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') : now()->format('d/m/Y H:i')) . ' WIB',
            'tanggalNbk' => ($order->tanggal_diajukan ? \Carbon\Carbon::parse($order->tanggal_diajukan)->format('d/m/Y H:i') : ($order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') : now()->format('d/m/Y H:i'))) . ' WIB',
            'order' => $order
        ])->render();

        try {
            $this->notificationGroup->send($groupId, $messageGroup);
        } catch (\Exception $e) {
            Log::error('WA Error Kawasan Order: ' . $e->getMessage());
        }
    }

    protected function currentPerumahaanId()
    {
        $user = Auth::user();
        return $user->is_global ? session('current_perumahaan_id', null) : $user->perumahaan_id;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $perumahaanId = $this->currentPerumahaanId();

        $query = PembangunanKawasanBarangOrder::with([
            'kawasan.perumahan',
            'kawasan.pengawas',
            'periode',
            'user',
            'details'
        ])->latest('tanggal_diajukan');

        if ($user->hasRole('PENGAWAS PROYEK (S&P)')) {
            $query->whereHas('kawasan', function ($q) use ($user, $perumahaanId) {
                $q->where('pengawas_id', $user->id);
                if ($perumahaanId) {
                    $q->where('perumahaan_id', $perumahaanId);
                }
            });
        } elseif ($perumahaanId) {
            $query->whereHas('kawasan', function ($q) use ($perumahaanId) {
                $q->where('perumahaan_id', $perumahaanId);
            });
        }

        if ($request->filled('status')) {
            $query->where('status_order', $request->status);
        }

        $orders = $query->paginate(15);

        return view('produksi.pembangunan-kawasan.order-barang.index', [
            'orders' => $orders,
            'breadcrumbs' => [
                ['label' => 'Pembangunan Kawasan', 'url' => route('produksi.pembangunanKawasan.index')],
                ['label' => 'Order Barang Kawasan', 'url' => route('produksi.pembangunanKawasan.orderIndex')],
            ],
        ]);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $perumahaanId = $this->currentPerumahaanId();

        $queryKawasan = PembangunanKawasan::with([
            'perumahan',
            'pengawas',
            'periodes.pengawas'
        ])->whereIn('status_pembangunan', ['proses', 'selesai', 'selesai dengan catatan']);

        $preselectKawasanId = $request->get('pembangunan_kawasan_id');
        if ($preselectKawasanId) {
            $targetKawasan = PembangunanKawasan::find($preselectKawasanId);
            if ($targetKawasan && $targetKawasan->perumahaan_id) {
                $perumahaanId = $targetKawasan->perumahaan_id;
            }
        }

        if ($user->hasRole('PENGAWAS PROYEK (S&P)')) {
            $queryKawasan->where(function ($q) use ($user, $preselectKawasanId) {
                $q->where('pengawas_id', $user->id);
                if ($preselectKawasanId) {
                    $q->orWhere('id', $preselectKawasanId);
                }
            });
        }

        if ($perumahaanId) {
            $queryKawasan->where(function ($q) use ($perumahaanId, $preselectKawasanId) {
                $q->where('perumahaan_id', $perumahaanId);
                if ($preselectKawasanId) {
                    $q->orWhere('id', $preselectKawasanId);
                }
            });
        }

        $pembangunanKawasan = $queryKawasan->get()->map(function ($pk) {
            $namaPerumahan = $pk->perumahan->nama_perumahaan ?? '';
            $activePeriode = $pk->periodes->where('status', 'proses')->last() ?? $pk->periodes->last();

            $pk->is_selesai = ($pk->status_pembangunan === 'selesai');
            $pk->label_formatted = "{$namaPerumahan} - {$pk->nama}";
            $pk->pengawas_nama = $activePeriode?->pengawas?->nama_lengkap ?? $pk->pengawas?->nama_lengkap ?? '-';
            $pk->subcon_nama = $activePeriode?->subcon ?? '-';
            $pk->periode_aktif_id = $activePeriode?->id;
            $pk->has_active_periode = !empty($activePeriode);
            $pk->periode_label = $activePeriode ? (($activePeriode->tanggal_mulai ? \Carbon\Carbon::parse($activePeriode->tanggal_mulai)->format('d/m/Y') : '-') . ' s/d ' . ($activePeriode->tanggal_selesai ? \Carbon\Carbon::parse($activePeriode->tanggal_selesai)->format('d/m/Y') : 'Sekarang')) : 'Tidak ada periode aktif';
            return $pk;
        });

        // Ambil barang gudang dengan stok UBS jika ada perumahaanId
        $barangGudangQuery = MasterBarang::with(['baseUnit', 'satuanKonversi.satuan']);

        if ($perumahaanId) {
            $barangGudangQuery->with(['stock' => function ($q) use ($perumahaanId) {
                $q->where('stock_type', 'UBS')->where('ubs_id', $perumahaanId);
            }]);
        } else {
            $barangGudangQuery->with(['stock' => function ($q) {
                $q->where('stock_type', 'UBS');
            }]);
        }

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

        return view('produksi.pembangunan-kawasan.order-barang.create', [
            'pembangunanKawasan' => $pembangunanKawasan,
            'barangGudang' => $barangGudang,
            'preselectKawasanId' => $preselectKawasanId,
            'breadcrumbs' => [
                ['label' => 'Pembangunan Kawasan', 'url' => route('produksi.pembangunanKawasan.index')],
                ['label' => 'Order Barang Kawasan', 'url' => route('produksi.pembangunanKawasan.orderIndex')],
                ['label' => 'Buat Order Barang', 'url' => route('produksi.pembangunanKawasan.orderCreate')],
            ],
        ]);
    }

    public function store(Request $request)
    {
        // Support both 'items' and 'barang' payload keys
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
            'pembangunan_kawasan_id' => 'required|exists:pembangunan_kawasan,id',
            'jenis_order' => 'required|in:stock,direct',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:master_barang,id',
            'items.*.satuan_id' => 'required|exists:master_satuan,id',
            'items.*.jumlah_input' => 'required|numeric|min:0.0001',
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

            $order = PembangunanKawasanBarangOrder::create([
                'nomor_order' => $nomorOrder,
                'pembangunan_kawasan_id' => $request->pembangunan_kawasan_id,
                'pembangunan_kawasan_periode_id' => $activePeriode?->id,
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

                PembangunanKawasanBarangOrderDetail::create([
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

            if ($kawasan) {
                $this->sendGroupNotificationOrder($kawasan, $order);
            }

            return response()->json(['message' => 'Order barang kawasan berhasil diajukan', 'order_id' => $order->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan order: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $order = PembangunanKawasanBarangOrder::with(['details'])->findOrFail($id);

        if ($order->status_order !== 'diproses') {
            return redirect()->back()->with('error', 'Gagal membatalkan order! Order ini sudah tidak dalam status menunggu.');
        }

        try {
            DB::beginTransaction();

            $kawasan = PembangunanKawasan::find($order->pembangunan_kawasan_id);
            if ($kawasan) {
                $this->sendGroupNotificationCancelOrder($kawasan, $order);
            }

            $order->details()->delete();
            $order->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Order barang kawasan berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
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
            Log::error('WA Cancel Kawasan Order Error: ' . $e->getMessage());
        }
    }
}
