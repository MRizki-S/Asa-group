<?php

namespace App\Http\Controllers\Gudang\PermintaanBarang;

use App\Http\Controllers\Controller;
use App\Models\BarangSatuanKonversi;
use App\Models\MasterBarang;
use App\Models\NotaBarangMasukDetail;
use App\Models\PembangunanKawasanBahan;
use App\Models\PembangunanKawasanBarangOrder;
use App\Models\PembangunanKawasanBarangFifoUsage;
use App\Models\PembangunanProyekBahan;
use App\Models\PembangunanProyekBarangOrder;
use App\Models\PembangunanProyekBarangFifoUsage;
use App\Models\PembangunanUnitBahan;
use App\Models\PembangunanUnitBarangOrder;
use App\Models\StockGudang;
use App\Models\StockLedger;
use App\Models\Ubs;
use App\Services\NotificationPribadiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermintaanBarangController extends Controller
{
    protected NotificationPribadiService $notification;

    public function __construct(NotificationPribadiService $notification)
    {
        $this->notification = $notification;
    }

    private function getOrderConfig($jenisOrder)
    {
        $configs = [
            'pembangunan_unit' => [
                'model' => PembangunanUnitBarangOrder::class,
                'with' => [
                    'details.barang.baseUnit',
                    'details.rapBahan',
                    'user',
                    'qc',
                    'pembangunanUnit.unit.blok',
                    'pembangunanUnit.unit.type',
                    'pembangunanUnit.tahap.perumahaan',
                    'pembangunanUnit.pengawas',
                ],
                'title' => 'Permintaan Barang Unit',
                'bahanModel' => PembangunanUnitBahan::class,
                'parent_id_field' => 'pembangunan_unit_id',
                'qc_id_field' => 'pembangunan_unit_qc_id',
            ],
            'pembangunan_kawasan' => [
                'model' => PembangunanKawasanBarangOrder::class,
                'with' => [
                    'details.barang.baseUnit',
                    'pembuat',
                    'kawasan.perumahan',
                    'kawasan.pengawas',
                    'gudangBy',
                    'spvBy',
                ],
                'title' => 'Permintaan Barang Kawasan',
                'bahanModel' => PembangunanKawasanBahan::class,
                'parent_id_field' => 'pembangunan_kawasan_id',
                'qc_id_field' => null,
            ],
            'pembangunan_proyek_mangoon' => [
                'model' => PembangunanProyekBarangOrder::class,
                'with' => [
                    'details.barang.baseUnit',
                    'pembuat',
                    'proyek',
                ],
                'title' => 'Permintaan Barang Proyek Mangoon',
                'bahanModel' => PembangunanProyekBahan::class,
                'parent_id_field' => 'pembangunan_proyek_id',
                'qc_id_field' => null,
            ],
        ];

        return $configs[$jenisOrder] ?? $configs['pembangunan_unit'];
    }

    private function orderQuery($category)
    {
        $config = $this->getOrderConfig($category);

        return $config['model']::with($config['with'])
            ->withCount('details')
            ->latest('tanggal_diajukan');
    }

    private function statusOptions(bool $includeMenunggu = true): array
    {
        $options = [
            'menunggu_spv' => 'Menunggu ACC SPV',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
        ];

        return $includeMenunggu
            ? ['diproses' => 'Diajukan'] + $options
            : $options;
    }

    /**
     * Tentukan pembatasan jenis order (stock vs direct vs cash) untuk user saat ini berdasarkan hak akses.
     * Return: [$canStock, $canDirect, $canCash, $allowedJenis, $forcedJenis]
     * - $forcedJenis = 'stock'  -> jika HANYA punya hak aksi-stock
     * - $forcedJenis = 'direct' -> jika HANYA punya hak aksi-direct
     * - $forcedJenis = 'cash'   -> jika HANYA punya hak aksi-cash
     * - $forcedJenis = null     -> jika punya 2 aksi, 3 aksi, atau 0 aksi (hanya read-only)
     * - $allowedJenis           -> array jenis order yang diizinkan dilihat user (misal ['stock', 'direct'])
     */
    private function resolveJenisOrderAccess($user, string $category): array
    {
        if (!$user) {
            return [false, false, false, [], null];
        }

        $isSuperadmin = $user->hasRole('Superadmin');

        $catPerm = match ($category) {
            'pembangunan_kawasan' => 'pemb-kawasan',
            'pembangunan_proyek_mangoon', 'pembangunan_proyek' => 'pemb-mangoon',
            default => 'pemb-unit',
        };

        $canStock = $isSuperadmin
            || $user->can("gudang.permintaan-barang.{$catPerm}.aksi-stock")
            || $user->can('gudang.permintaan-barang.aksi-stock');

        $canDirect = $isSuperadmin
            || $user->can("gudang.permintaan-barang.{$catPerm}.aksi-direct")
            || $user->can('gudang.permintaan-barang.aksi-direct');

        $canCash = $isSuperadmin
            || $user->can("gudang.permintaan-barang.{$catPerm}.aksi-cash")
            || $user->can('gudang.permintaan-barang.aksi-cash');

        $activeActions = [];
        if ($canStock) $activeActions[] = 'stock';
        if ($canDirect) $activeActions[] = 'direct';
        if ($canCash) $activeActions[] = 'cash';

        $totalAksi = count($activeActions);

        if ($isSuperadmin || $totalAksi === 3 || $totalAksi === 0) {
            // Superadmin, punya ke-3 aksi, ATAU tidak punya aksi sama sekali (hanya punya akses .read)
            // -> Bisa melihat semua daftar
            $allowedJenis = ['stock', 'direct', 'cash'];
            $forcedJenis = null;
        } elseif ($totalAksi === 1) {
            // Hanya punya 1 aksi -> terkunci khusus pada jenis tersebut
            $allowedJenis = $activeActions;
            $forcedJenis = $activeActions[0];
        } else {
            // Punya 2 aksi dari 3 -> hanya boleh melihat 2 jenis aksi yang dimiliki
            $allowedJenis = $activeActions;
            $forcedJenis = null;
        }

        return [$canStock, $canDirect, $canCash, $allowedJenis, $forcedJenis];
    }

    // view daftar permintaan barang yang masih diproses (status menunggu)
    public function index(Request $request)
    {
        $status = $request->get('status', 'diproses');
        $rawJenisOrder = $request->get('jenis_order', 'pembangunan_unit');

        // Categories from sidebar
        $categories = ['pembangunan_unit', 'pembangunan_kawasan', 'pembangunan_proyek_mangoon'];

        // Resolve category and filter
        if (in_array($rawJenisOrder, $categories)) {
            $category = $rawJenisOrder;
            $filterJenis = 'all';
        } else {
            $category = $request->get('category', 'pembangunan_unit');
            $filterJenis = in_array($rawJenisOrder, ['stock', 'direct', 'cash']) ? $rawJenisOrder : 'all';
        }

        $config = $this->getOrderConfig($category);
        $query = $this->orderQuery($category);

        // 🔒 Cek pembatasan hak akses aksi-stock vs aksi-direct vs aksi-cash
        $user = Auth::user();
        [$canStock, $canDirect, $canCash, $allowedJenis, $forcedJenis] = $this->resolveJenisOrderAccess($user, $category);

        if ($forcedJenis !== null) {
            // Hanya punya salah satu hak akses -> paksa jenis_order tersebut
            $filterJenis = $forcedJenis;
            $query->where('jenis_order', $forcedJenis);
        } else {
            if ($filterJenis !== 'all') {
                if (in_array($filterJenis, $allowedJenis)) {
                    $query->where('jenis_order', $filterJenis);
                } else {
                    $filterJenis = 'all';
                    $query->whereIn('jenis_order', $allowedJenis);
                }
            } else {
                if (count($allowedJenis) < 3) {
                    $query->whereIn('jenis_order', $allowedJenis);
                }
            }
        }

        if ($status === 'diproses') {
            // Untuk tab permintaan berjalan, tampilkan baik yang diproses maupun menunggu_spv jika filter default
            $query->whereIn('status_order', ['diproses', 'menunggu_spv']);
        } elseif ($status !== 'all') {
            $query->where('status_order', $status);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_diajukan', $request->tanggal);
        }

        $orders = $query->get();

        return view('gudang.permintaan-barang.index', [
            'orders' => $orders,
            'status' => $status,
            'category' => $category,
            'jenisOrder' => $filterJenis,
            'forcedJenis' => $forcedJenis,
            'canStock' => $canStock,
            'canDirect' => $canDirect,
            'canCash' => $canCash,
            'allowedJenis' => $allowedJenis,
            'tanggal' => $request->get('tanggal'),
            'statusOptions' => $this->statusOptions(),
            'isHistory' => false,
            'titlePage' => $config['title'],
            'breadcrumbs' => [
                [
                    'label' => 'Permintaan Barang',
                    'url' => route('gudang.permintaanBarang.index', ['jenis_order' => $category]),
                ],
            ],
        ]);
    }

    // view history dari permintaan barang
    public function history(Request $request)
    {
        $status = $request->get('status', 'all');
        $rawJenisOrder = $request->get('jenis_order', 'pembangunan_unit');

        $categories = ['pembangunan_unit', 'pembangunan_kawasan', 'pembangunan_proyek_mangoon'];

        if (in_array($rawJenisOrder, $categories)) {
            $category = $rawJenisOrder;
            $filterJenis = 'all';
        } else {
            $category = $request->get('category', 'pembangunan_unit');
            $filterJenis = in_array($rawJenisOrder, ['stock', 'direct', 'cash']) ? $rawJenisOrder : 'all';
        }

        $config = $this->getOrderConfig($category);
        $query = $this->orderQuery($category);

        // 🔒 Cek pembatasan hak akses aksi-stock vs aksi-direct vs aksi-cash
        $user = Auth::user();
        [$canStock, $canDirect, $canCash, $allowedJenis, $forcedJenis] = $this->resolveJenisOrderAccess($user, $category);

        if ($forcedJenis !== null) {
            // Hanya punya salah satu hak akses -> paksa jenis_order tersebut
            $filterJenis = $forcedJenis;
            $query->where('jenis_order', $forcedJenis);
        } else {
            if ($filterJenis !== 'all') {
                if (in_array($filterJenis, $allowedJenis)) {
                    $query->where('jenis_order', $filterJenis);
                } else {
                    $filterJenis = 'all';
                    $query->whereIn('jenis_order', $allowedJenis);
                }
            } else {
                if (count($allowedJenis) < 3) {
                    $query->whereIn('jenis_order', $allowedJenis);
                }
            }
        }

        if ($status === 'all') {
            $query->whereNotIn('status_order', ['diproses', 'menunggu_spv']);
        } else {
            $query->where('status_order', $status);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_diajukan', $request->tanggal);
        }

        $orders = $query->get();

        return view('gudang.permintaan-barang.index', [
            'orders' => $orders,
            'status' => $status,
            'category' => $category,
            'jenisOrder' => $filterJenis,
            'forcedJenis' => $forcedJenis,
            'canStock' => $canStock,
            'canDirect' => $canDirect,
            'canCash' => $canCash,
            'allowedJenis' => $allowedJenis,
            'statusOptions' => $this->statusOptions(false),
            'isHistory' => true,
            'titlePage' => 'Riwayat ' . $config['title'],
            'breadcrumbs' => [
                [
                    'label' => 'Permintaan Barang',
                    'url' => route('gudang.permintaanBarang.index', ['jenis_order' => $category]),
                ],
                [
                    'label' => 'Riwayat',
                    'url' => route('gudang.permintaanBarang.history', ['jenis_order' => $category]),
                ],
            ],
        ]);
    }

    // function untuk melihat detail order barang sebelum dilakukan acc oleh gudang
    public function show(Request $request, $id)
    {
        $category = $request->get('jenis_order', 'pembangunan_unit');
        $config = $this->getOrderConfig($category);

        $order = $config['model']::with($config['with'])->findOrFail($id);

        $ubsId = $order->ubs_id;
        if (!$ubsId) {
            if ($category === 'pembangunan_unit') {
                $ubsId = $order->pembangunanUnit?->perumahaan_id;
            } elseif ($category === 'pembangunan_kawasan') {
                $ubsId = $order->kawasan?->perumahaan_id ?? $order->kawasan?->perumahan_id;
            }
        }

        $stocks = [];
        $ubsCode = null;
        if ($ubsId) {
            $ubsCode = Ubs::where('id', $ubsId)->value('kode_ubs');
            $stocks = StockGudang::where('stock_type', 'UBS')
                ->where('ubs_id', $ubsId)
                ->whereIn('barang_id', $order->details->pluck('barang_id'))
                ->get()
                ->pluck('jumlah_stock', 'barang_id')
                ->toArray();
        }

        $conversions = [];
        foreach ($order->details as $detail) {
            $factor = BarangSatuanKonversi::where('barang_id', $detail->barang_id)
                ->where('satuan_id', $detail->satuan_id)
                ->value('konversi_ke_base');
            $conversions[$detail->id] = (float) ($factor ?? 1.0);
        }

        return view('gudang.permintaan-barang.show', [
            'order' => $order,
            'category' => $category,
            'stocks' => $stocks,
            'conversions' => $conversions,
            'ubsCode' => $ubsCode,
            'breadcrumbs' => [
                [
                    'label' => 'Permintaan Barang',
                    'url' => route('gudang.permintaanBarang.index', ['jenis_order' => $category]),
                ],
                [
                    'label' => 'Detail Permintaan - REQ-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'url' => route('gudang.permintaanBarang.show', ['id' => $order->id, 'jenis_order' => $category]),
                ],
            ],
        ]);
    }

    public function acc(Request $request, $id)
    {
        $category = $request->get('jenis_order', 'pembangunan_kawasan');
        
        if (!in_array($category, ['pembangunan_kawasan', 'pembangunan_proyek_mangoon'])) {
            return back()->with('error', 'Kategori permintaan barang tidak valid.');
        }

        $config = $this->getOrderConfig($category);
        $order = $config['model']::with($config['with'])->findOrFail($id);

        if ($order->status_order !== 'diproses') {
            return back()->with('error', 'Permintaan barang ini sudah tidak dalam status menunggu.');
        }

        $user = Auth::user();
        $isStock = ($order->jenis_order ?? 'stock') === 'stock';
        $isDirect = ($order->jenis_order ?? '') === 'direct';
        $isCash = ($order->jenis_order ?? '') === 'cash';
        $catPerm = $category === 'pembangunan_kawasan' ? 'pemb-kawasan' : 'pemb-mangoon';

        $canAcc = $user->hasRole('Superadmin')
            || ($isStock && ($user->can("gudang.permintaan-barang.{$catPerm}.aksi-stock") || $user->can('gudang.permintaan-barang.aksi-stock')))
            || ($isDirect && ($user->can("gudang.permintaan-barang.{$catPerm}.aksi-direct") || $user->can('gudang.permintaan-barang.aksi-direct')))
            || ($isCash && ($user->can("gudang.permintaan-barang.{$catPerm}.aksi-cash") || $user->can('gudang.permintaan-barang.aksi-cash')));

        if (!$canAcc) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk ACC permintaan barang ' . ($order->jenis_order ?? 'ini') . '.');
        }

        try {
            DB::transaction(function () use ($order, $request, $category) {
                $this->processAcc($order, $request, $category);
            });

            // Kirim notifikasi WA setelah transaksi berhasil commit
            $approver = Auth::user();
            $adminName = $approver->nama_lengkap ?? $approver->username ?? 'Petugas';
            $spvRole = $approver->getRoleNames()->first() ?? 'Petugas';
            if ($category === 'pembangunan_kawasan') {
                $targetGroup = env('FONNTE_ID_ORDER_BARANG_ABM', env('FONNTE_ID_GROUP_ACC_ORDER_BARANG_KAWASAN', env('FONNTE_ID_GROUP_ORDER_BARANG_KAWASAN')));
                if (!empty($targetGroup)) {
                    $order->loadMissing(['kawasan.perumahan', 'details.barang.baseUnit']);
                    $message = view('notifications.whatsapp.pembangunan_kawasan.acc_order_barang', [
                        'order' => $order,
                        'namaPerumahan' => $order->kawasan?->perumahan?->nama_perumahaan ?? '-',
                        'namaKawasan' => $order->kawasan?->nama ?? '-',
                        'spvName' => $adminName,
                        'spvRole' => $spvRole,
                        'tanggalSpv' => now()->format('d/m/Y H:i') . ' WIB',
                    ])->render();
                    $this->notification->sendWhatsApp($targetGroup, $message);
                }
            } else {
                $targetGroup = env('FONNTE_ID_ORDER_BARANG_ABM', env('FONNTE_ID_GROUP_ACC_ORDER_BARANG_PROYEK', env('FONNTE_ID_GROUP_ORDER_BARANG_PROYEK')));
                if (!empty($targetGroup)) {
                    $order->loadMissing(['proyek', 'details.barang.baseUnit']);
                    $message = view('notifications.whatsapp.pembangunan_proyek.acc_order_barang', [
                        'order' => $order,
                        'namaProyek' => $order->proyek?->nama_project ?? $order->proyek?->nama ?? '-',
                        'spvName' => $adminName,
                        'spvRole' => $spvRole,
                        'tanggalSpv' => now()->format('d/m/Y H:i') . ' WIB',
                    ])->render();
                    $this->notification->sendWhatsApp($targetGroup, $message);
                }
            }
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal ACC permintaan barang: ' . $e->getMessage());
        }

        return redirect()
            ->route('gudang.permintaanBarang.history', ['jenis_order' => $category])
            ->with('success', 'Permintaan barang berhasil di-ACC.');
    }

    public function tolak(Request $request, $id)
    {
        $request->validate([
            'alasan_tolak' => 'required|string|max:1000',
        ]);

        $category = $request->get('jenis_order', 'pembangunan_kawasan');
        $config   = $this->getOrderConfig($category);
        $order    = $config['model']::with($config['with'])->findOrFail($id);

        if ($order->status_order !== 'diproses') {
            return back()->with('error', 'Permintaan barang ini sudah tidak dalam status menunggu.');
        }

        $user = Auth::user();
        $isStock = ($order->jenis_order ?? 'stock') === 'stock';
        $isDirect = ($order->jenis_order ?? '') === 'direct';
        $isCash = ($order->jenis_order ?? '') === 'cash';
        $catPerm = $category === 'pembangunan_kawasan' ? 'pemb-kawasan' : 'pemb-mangoon';

        $canTolak = $user->hasRole('Superadmin')
            || ($isStock && ($user->can("gudang.permintaan-barang.{$catPerm}.aksi-stock") || $user->can('gudang.permintaan-barang.aksi-stock')))
            || ($isDirect && ($user->can("gudang.permintaan-barang.{$catPerm}.aksi-direct") || $user->can('gudang.permintaan-barang.aksi-direct')))
            || ($isCash && ($user->can("gudang.permintaan-barang.{$catPerm}.aksi-cash") || $user->can('gudang.permintaan-barang.aksi-cash')));

        if (!$canTolak) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk menolak permintaan barang ' . ($order->jenis_order ?? 'ini') . '.');
        }

        $order->update([
            'status_order' => 'ditolak',
            'alasan_tolak' => $request->alasan_tolak,
        ]);

        // Kirim notifikasi WA penolakan order barang kawasan/proyek
        $adminName = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Admin Gudang';
        if ($category === 'pembangunan_kawasan') {
            $targetGroup = env('FONNTE_ID_GROUP_TOLAK_ORDER_BARANG_KAWASAN', env('FONNTE_ID_GROUP_ORDER_BARANG_KAWASAN', env('FONNTE_ID_ORDER_BARANG_ABM')));
            if (!empty($targetGroup)) {
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
        } elseif ($category === 'pembangunan_proyek_mangoon') {
            $targetGroup = env('FONNTE_ID_GROUP_TOLAK_ORDER_BARANG_PROYEK', env('FONNTE_ID_GROUP_ORDER_BARANG_PROYEK', env('FONNTE_ID_ORDER_BARANG_ABM')));
            if (!empty($targetGroup)) {
                $message = view('notifications.whatsapp.pembangunan_proyek.tolak_order_barang', [
                    'order' => $order,
                    'namaProyek' => $order->proyek?->nama ?? '-',
                    'adminGudang' => $adminName,
                    'alasanTolak' => $order->alasan_tolak ?? null,
                    'tanggal' => now()->format('d/m/Y H:i') . ' WIB',
                ])->render();
                $this->notification->sendWhatsApp($targetGroup, $message);
            }
        }

        return back()->with('success', 'Permintaan barang berhasil ditolak.');
    }

    public function resubmit(Request $request, $id)
    {
        $request->validate(['catatan' => 'nullable|string|max:1000']);

        $category = $request->get('jenis_order', 'pembangunan_kawasan');
        $config   = $this->getOrderConfig($category);
        $order    = $config['model']::with($config['with'])->findOrFail($id);

        if ($order->status_order !== 'ditolak') {
            return back()->with('error', 'Hanya permintaan yang ditolak yang dapat diajukan kembali.');
        }

        $order->update([
            'status_order'     => 'diproses',
            'catatan'          => $request->catatan,
            'tanggal_diajukan' => now(),
        ]);

        // Kirim notifikasi WA resubmit order barang kawasan/proyek
        $pengaju = Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Pengaju';
        if ($category === 'pembangunan_kawasan') {
            $targetGroup = env('FONNTE_ID_GROUP_RESUBMIT_ORDER_BARANG_KAWASAN', env('FONNTE_ID_GROUP_ORDER_BARANG_KAWASAN', env('FONNTE_ID_ORDER_BARANG_ABM')));
            if (!empty($targetGroup)) {
                $message = view('notifications.whatsapp.pembangunan_kawasan.resubmit_order_barang', [
                    'order' => $order,
                    'namaPerumahan' => $order->kawasan?->perumahan?->nama_perumahaan ?? '-',
                    'namaKawasan' => $order->kawasan?->nama ?? '-',
                    'pengaju' => $pengaju,
                    'tanggal' => now()->format('d/m/Y H:i') . ' WIB',
                ])->render();
                $this->notification->sendWhatsApp($targetGroup, $message);
            }
        } elseif ($category === 'pembangunan_proyek_mangoon') {
            $targetGroup = env('FONNTE_ID_GROUP_RESUBMIT_ORDER_BARANG_PROYEK', env('FONNTE_ID_GROUP_ORDER_BARANG_PROYEK', env('FONNTE_ID_ORDER_BARANG_ABM')));
            if (!empty($targetGroup)) {
                $message = view('notifications.whatsapp.pembangunan_proyek.resubmit_order_barang', [
                    'order' => $order,
                    'namaProyek' => $order->proyek?->nama ?? '-',
                    'pengaju' => $pengaju,
                    'tanggal' => now()->format('d/m/Y H:i') . ' WIB',
                ])->render();
                $this->notification->sendWhatsApp($targetGroup, $message);
            }
        }

        return back()->with('success', 'Permintaan barang berhasil diajukan kembali.');
    }

    private function processAcc($order, Request $request, string $category): void
    {
        $ubsId = $order->ubs_id;
        
        if (!$ubsId) {
            if ($category === 'pembangunan_kawasan') {
                $ubsId = $order->kawasan?->perumahaan_id ?? $order->kawasan?->perumahan_id;
            }
        }

        if (!$ubsId) {
            throw new \Exception('Data UBS/perumahan tujuan stock tidak ditemukan.');
        }

        foreach ($order->details as $detail) {
            $this->assertDetailMatchesOrderType($order, $detail);

            if ($detail->konfirmasi) {
                continue;
            }

            $jumlahBase = $this->resolveJumlahBase($detail);
            $hargaTotal = 0.0;
            $hargaSatuanBase = 0.0;

            if ($order->jenis_order === 'cash') {
                $cashPrices = $this->resolveCashPrices($request, $detail);
                $hargaTotal = $cashPrices['harga_total'];
                $hargaSatuanBase = $cashPrices['harga_satuan'];

                $detail->update([
                    'konfirmasi' => true,
                    'jumlah_base' => $jumlahBase,
                    'harga_satuan_snapshot' => $hargaSatuanBase,
                    'harga_total_snapshot' => $hargaTotal,
                ]);

                if ($jumlahBase > 0) {
                    $this->upsertBahan($order, $detail, $hargaTotal, $category);
                }
                continue;
            }

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

                    $fifoResult = $this->consumeNotaFifo($detail->barang_id, $jumlahBase);
                    $hargaTotal = $fifoResult['harga_total'];
                    $hargaSatuanBase = $jumlahBase > 0 ? $hargaTotal / $jumlahBase : 0;

                    // Simpan setiap layer FIFO yang dipakai ke tabel fifo_usage
                    foreach ($fifoResult['layers'] as $layer) {
                        if ($category === 'pembangunan_kawasan') {
                            PembangunanKawasanBarangFifoUsage::create([
                                'order_detail_id' => $detail->id,
                                'nota_barang_masuk_detail_id' => $layer['nota_barang_masuk_detail_id'],
                                'jumlah_base' => $layer['jumlah_base'],
                                'jumlah_return_base' => 0,
                                'harga_satuan_snapshot' => $layer['harga_satuan_snapshot'],
                                'harga_total_snapshot' => $layer['harga_total_snapshot'],
                            ]);
                        } else {
                            PembangunanProyekBarangFifoUsage::create([
                                'order_detail_id' => $detail->id,
                                'nota_barang_masuk_detail_id' => $layer['nota_barang_masuk_detail_id'],
                                'jumlah_base' => $layer['jumlah_base'],
                                'jumlah_return_base' => 0,
                                'harga_satuan_snapshot' => $layer['harga_satuan_snapshot'],
                                'harga_total_snapshot' => $layer['harga_total_snapshot'],
                            ]);
                        }
                    }

                    $stock->decrement('jumlah_stock', $jumlahBase);

                    StockLedger::create([
                        'tanggal' => now(),
                        'barang_id' => $detail->barang_id,
                        'stock_type' => 'UBS',
                        'ubs_id' => $ubsId,
                        'tipe' => 'keluar',
                        'ref_type' => get_class($order),
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
                $this->upsertBahan($order, $detail, $hargaTotal, $category);
            }
        }

        $order->update([
            'status_order' => 'selesai',
            'tanggal_selesai' => now(),
        ]);
    }

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

    private function resolveCashPrices(Request $request, $detail): array
    {
        $hargaTotalInput = $request->input("harga_total.{$detail->id}");
        $hargaSatuanInput = $request->input("harga_satuan.{$detail->id}");
        $qtyInput = (float) ($detail->jumlah_input ?? 0);

        $hargaTotal = null;
        $hargaSatuan = null;

        if ($hargaTotalInput !== null && $hargaTotalInput !== '') {
            $hargaTotal = (float) $hargaTotalInput;
        }
        if ($hargaSatuanInput !== null && $hargaSatuanInput !== '') {
            $hargaSatuan = (float) $hargaSatuanInput;
        }

        if ($hargaTotal === null && $hargaSatuan !== null && $qtyInput > 0) {
            $hargaTotal = round($hargaSatuan * $qtyInput, 2);
        } elseif ($hargaSatuan === null && $hargaTotal !== null && $qtyInput > 0) {
            $hargaSatuan = round($hargaTotal / $qtyInput, 2);
        }

        if ($hargaTotal === null) {
            if (!is_null($detail->harga_total_snapshot)) {
                $hargaTotal = (float) $detail->harga_total_snapshot;
            } elseif (!is_null($detail->harga_satuan_snapshot) && $qtyInput > 0) {
                $hargaTotal = round((float) $detail->harga_satuan_snapshot * $qtyInput, 2);
            }
        }

        if ($hargaSatuan === null) {
            if (!is_null($detail->harga_satuan_snapshot)) {
                $hargaSatuan = (float) $detail->harga_satuan_snapshot;
            } elseif ($hargaTotal !== null && $qtyInput > 0) {
                $hargaSatuan = round($hargaTotal / $qtyInput, 2);
            }
        }

        if ($hargaTotal === null || $hargaTotal < 0) {
            $namaBarang = $detail->nama_barang ?? $detail->barang?->nama_barang ?? 'Barang';
            throw new \Exception("Harga total untuk barang cash {$namaBarang} wajib valid dan tidak boleh minus.");
        }

        return [
            'harga_total' => round($hargaTotal, 2),
            'harga_satuan' => round($hargaSatuan ?? 0, 2),
        ];
    }

    private function assertDetailMatchesOrderType($order, $detail): void
    {
        if ($order->jenis_order === 'cash') {
            return;
        }

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

    private function upsertBahan($order, $detail, float $hargaTotal, string $category): void
    {
        $baseUnitName = $detail->barang?->baseUnit?->nama ?? ($detail->satuanModel?->nama ?? ($detail->satuan ?? '-'));

        if ($category === 'pembangunan_kawasan') {
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
        } else {
            $bahan = PembangunanProyekBahan::where('pembangunan_proyek_id', $order->pembangunan_proyek_id)
                ->where('barang_id', $detail->barang_id)
                ->first();

            if ($bahan) {
                $bahan->update([
                    'jumlah_pakai' => (float) $bahan->jumlah_pakai + (float) $detail->jumlah_base,
                    'harga_total_snapshot' => (float) $bahan->harga_total_snapshot + $hargaTotal,
                ]);
                return;
            }

            PembangunanProyekBahan::create([
                'pembangunan_proyek_id' => $order->pembangunan_proyek_id,
                'barang_id' => $detail->barang_id,
                'nama_barang' => $detail->nama_barang ?? $detail->barang?->nama_barang ?? '-',
                'satuan' => $baseUnitName,
                'jumlah_pakai' => (float) $detail->jumlah_base,
                'harga_total_snapshot' => $hargaTotal,
            ]);
        }
    }

    public function destroy(Request $request, $id)
    {
        $category = $request->get('category', 'pembangunan_unit');
        $config   = $this->getOrderConfig($category);

        $order = $config['model']::with(['details'])->findOrFail($id);

        if ($order->status_order === 'selesai') {
            return back()->with('error', 'Permintaan barang yang sudah di-ACC (Selesai) tidak dapat dihapus.');
        }

        try {
            DB::beginTransaction();

            $order->details()->delete();
            $order->delete();

            DB::commit();

            return back()->with('success', 'Permintaan barang berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus permintaan barang: ' . $e->getMessage());
        }
    }

    public function updateTanggal(Request $request, $id)
    {
        $request->validate([
            'tanggal_diajukan' => 'required|date',
            'category' => 'required|string',
        ]);

        $category = $request->category;
        $config = $this->getOrderConfig($category);
        
        $order = $config['model']::findOrFail($id);

        if ($order->status_order !== 'diproses') {
            return back()->with('error', 'Hanya permintaan barang dengan status menunggu yang dapat diubah tanggalnya.');
        }

        $order->update([
            'tanggal_diajukan' => $request->tanggal_diajukan,
        ]);

        return back()->with('success', 'Tanggal permintaan barang berhasil diperbarui.');
    }

    public function cetakBulkNbk(Request $request)
    {
        $category = $request->input('category', 'pembangunan_unit');
        $rawOrderIds = $request->input('order_ids', []);

        // Parse order_ids if passed as comma separated string or array
        if (is_string($rawOrderIds)) {
            $orderIds = array_filter(explode(',', $rawOrderIds));
        } elseif (is_array($rawOrderIds)) {
            $orderIds = array_filter($rawOrderIds);
        } else {
            $orderIds = [];
        }

        if (empty($orderIds)) {
            return back()->with('error', 'Pilih minimal satu permintaan barang untuk dicetak.');
        }

        if (count($orderIds) > 50) {
            return back()->with('error', 'Maksimal 50 nota per sekali cetak untuk menjaga performa sistem.');
        }

        $config = $this->getOrderConfig($category);
        $orders = $config['model']::with($config['with'])
            ->whereIn('id', $orderIds)
            ->where('status_order', 'selesai')
            ->orderBy('id', 'desc')
            ->get();

        if ($orders->isEmpty()) {
            return back()->with('error', 'Tidak ada nota yang berstatus selesai dari data yang dipilih.');
        }

        $viewMap = [
            'pembangunan_unit' => 'gudang.permintaan-barang.nota-keluar-bulk-pdf',
            'pembangunan_kawasan' => 'gudang.permintaan-barang.nota-keluar-bulk-pdf',
            'pembangunan_proyek_mangoon' => 'gudang.permintaan-barang.nota-keluar-bulk-pdf',
        ];

        $viewName = $viewMap[$category] ?? 'gudang.permintaan-barang.nota-keluar-bulk-pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewName, [
            'orders' => $orders,
            'category' => $category,
        ])->setPaper('a4', 'portrait');

        $timestamp = now()->format('YmdHis');
        return $pdf->stream("Bulk-NBK-{$category}-{$timestamp}.pdf");
    }
}

