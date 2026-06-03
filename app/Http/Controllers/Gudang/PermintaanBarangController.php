<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\PembangunanUnitBarangOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermintaanBarangController extends Controller
{
    private function orderQuery()
    {
        return PembangunanUnitBarangOrder::with([
            'details',
            'user',
            'qc',
            'pembangunanUnit.unit.blok',
            'pembangunanUnit.unit.type',
            'pembangunanUnit.tahap.perumahaan',
            'pembangunanUnit.pengawas',
        ])
            ->withCount('details')
            ->latest('tanggal_diajukan');
    }

    private function statusOptions(bool $includeMenunggu = true): array
    {
        $options = [
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
            'pengembalian' => 'Pengembalian',
        ];

        return $includeMenunggu
            ? ['diproses' => 'Menunggu'] + $options
            : $options;
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'diproses');
        $jenisOrder = $request->get('jenis_order', 'all');

        $query = $this->orderQuery();

        if ($status !== 'all') {
            $query->where('status_order', $status);
        }

        if ($jenisOrder !== 'all') {
            $query->where('jenis_order', $jenisOrder);
        }

        $orders = $query->get();

        return view('gudang.permintaan-barang.index', [
            'orders' => $orders,
            'status' => $status,
            'jenisOrder' => $jenisOrder,
            'statusOptions' => $this->statusOptions(),
            'isHistory' => false,
            'titlePage' => 'Daftar Permintaan Barang Proyek',
            'breadcrumbs' => [
                [
                    'label' => 'Permintaan Barang',
                    'url' => route('gudang.permintaanBarang.index'),
                ],
            ],
        ]);
    }

    public function history(Request $request)
    {
        $status = $request->get('status', 'all');
        $jenisOrder = $request->get('jenis_order', 'all');

        $query = $this->orderQuery();

        if ($status === 'all') {
            $query->where('status_order', '!=', 'diproses');
        } else {
            $query->where('status_order', $status);
        }

        if ($jenisOrder !== 'all') {
            $query->where('jenis_order', $jenisOrder);
        }

        $orders = $query
            ->get();

        return view('gudang.permintaan-barang.index', [
            'orders' => $orders,
            'status' => $status,
            'jenisOrder' => $jenisOrder,
            'statusOptions' => $this->statusOptions(false),
            'isHistory' => true,
            'titlePage' => 'Riwayat Permintaan Barang',
            'breadcrumbs' => [
                [
                    'label' => 'Permintaan Barang',
                    'url' => route('gudang.permintaanBarang.index'),
                ],
                [
                    'label' => 'Riwayat Permintaan Barang',
                    'url' => route('gudang.permintaanBarang.history'),
                ],
            ],
        ]);
    }

    public function show($id)
    {
        $order = PembangunanUnitBarangOrder::with([
            'details.barang',
            'details.rapBahan',
            'user',
            'qc',
            'pembangunanUnit.unit.blok',
            'pembangunanUnit.unit.type',
            'pembangunanUnit.tahap.perumahaan',
            'pembangunanUnit.pengawas',
        ])->findOrFail($id);

        return view('gudang.permintaan-barang.show', [
            'order' => $order,
            'breadcrumbs' => [
                [
                    'label' => 'Permintaan Barang',
                    'url' => route('gudang.permintaanBarang.index'),
                ],
                [
                    'label' => 'Detail Permintaan - REQ-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'url' => route('gudang.permintaanBarang.show', $order->id),
                ],
            ],
        ]);
    }

    public function acc($id)
    {
        $order = PembangunanUnitBarangOrder::with('details')->findOrFail($id);

        if ($order->status_order !== 'diproses') {
            return back()->with('error', 'Permintaan barang ini sudah tidak dalam status menunggu.');
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status_order' => 'selesai',
                'tanggal_selesai' => now(),
            ]);

            $order->details()->update([
                'konfirmasi' => true,
            ]);
        });

        return redirect()
            ->route('gudang.permintaanBarang.history')
            ->with('success', 'Permintaan barang berhasil di-ACC.');
    }
}
