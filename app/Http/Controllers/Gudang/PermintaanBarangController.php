<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\BarangSatuanKonversi;
use App\Models\MasterBarang;
use App\Models\NotaBarangMasukDetail;
use App\Models\PembangunanKawasanBahan;
use App\Models\PembangunanKawasanBarangOrder;
use App\Models\PembangunanProyekBahan;
use App\Models\PembangunanProyekBarangOrder;
use App\Models\PembangunanUnitBahan;
use App\Models\PembangunanUnitBarangOrder;
use App\Models\StockGudang;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermintaanBarangController extends Controller
{
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
                ],
                'title' => 'Permintaan Barang Kawasan',
                'bahanModel' => PembangunanKawasanBahan::class,
                'parent_id_field' => 'pembangunan_kawasan_id',
                'qc_id_field' => null, // Kawasan might not have QC termin?
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
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
        ];

        return $includeMenunggu
            ? ['diproses' => 'Menunggu'] + $options
            : $options;
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
            $filterJenis = in_array($rawJenisOrder, ['stock', 'direct']) ? $rawJenisOrder : 'all';
        }

        $config = $this->getOrderConfig($category);
        $query = $this->orderQuery($category);

        if ($status !== 'all') {
            $query->where('status_order', $status);
        }

        if ($filterJenis !== 'all') {
            $query->where('jenis_order', $filterJenis);
        }

        $orders = $query->get();

        return view('gudang.permintaan-barang.index', [
            'orders' => $orders,
            'status' => $status,
            'category' => $category,
            'jenisOrder' => $filterJenis,
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
            $filterJenis = in_array($rawJenisOrder, ['stock', 'direct']) ? $rawJenisOrder : 'all';
        }

        $config = $this->getOrderConfig($category);
        $query = $this->orderQuery($category);

        if ($status === 'all') {
            $query->where('status_order', '!=', 'diproses');
        } else {
            $query->where('status_order', $status);
        }

        if ($filterJenis !== 'all') {
            $query->where('jenis_order', $filterJenis);
        }

        $orders = $query->get();

        return view('gudang.permintaan-barang.index', [
            'orders' => $orders,
            'status' => $status,
            'category' => $category,
            'jenisOrder' => $filterJenis,
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
            $ubsCode = \App\Models\Ubs::where('id', $ubsId)->value('kode_ubs');
            $stocks = \App\Models\StockGudang::where('stock_type', 'UBS')
                ->where('ubs_id', $ubsId)
                ->whereIn('barang_id', $order->details->pluck('barang_id'))
                ->get()
                ->pluck('jumlah_stock', 'barang_id')
                ->toArray();
        }

        $conversions = [];
        foreach ($order->details as $detail) {
            $factor = \App\Models\BarangSatuanKonversi::where('barang_id', $detail->barang_id)
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
}
