<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\MasterBarang;
use App\Models\Ubs;
use App\Models\StockGudang;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\PeriodeKeuangan;
use App\Models\Jurnal;

class StockBarangController extends Controller
{
    public function stockIndex(Request $request)
    {
        $ubsId = $request->get('ubs_id', 'all');
        $cariMaterial = $request->get('cariMaterial');

        $ubsData = Ubs::all();

        // Query Utama MasterBarang
        $query = MasterBarang::query()->with('baseUnit');

        // Eager Load Nota Details HANYA saat filter "all" (Semua Gudang)
        if ($ubsId == 'all') {
            $query->with([
                'notaDetails' => function ($q) {
                    $q->where('jumlah_sisa', '>', 0)
                        ->whereHas('nota', function ($queryNota) {
                            $queryNota->where('status', 'posted');
                        })
                        ->with('nota')
                        ->orderBy('created_at', 'asc'); // FIFO
                }
            ]);
        }

        // Filter Pencarian Nama/Kode Barang
        if ($cariMaterial) {
            $query->where(function ($q) use ($cariMaterial) {
                $q->where('nama_barang', 'like', '%' . $cariMaterial . '%')
                    ->orWhere('kode_barang', 'like', '%' . $cariMaterial . '%');
            });
        }

        $titleGudang = 'Semua Gudang';

        // Filter Berdasarkan Gudang (Semua vs HUB vs UBS)
        if ($ubsId == 'all') {
            // Tampilkan SEMUA barang, ambil relasi stock
            $query->with('stock');
        } elseif ($ubsId == 'hub') {
            // Tampilkan SEMUA barang, ambil relasi stock HUB saja
            $query->with('stockHub');
            $titleGudang = 'HUB (Pusat)';
        } else {
            // Tampilkan barang yang punya stok di UBS tertentu berdasarkan kode_ubs
            $currentUbs = $ubsData->where('kode_ubs', $ubsId)->first();
            $dbUbsId = $currentUbs ? $currentUbs->id : null;
            $titleGudang = $currentUbs ? $currentUbs->kode_ubs : $ubsId;

            $query->with(['stock' => function ($q) use ($dbUbsId) {
                $q->where('stock_type', 'UBS')->where('ubs_id', $dbUbsId);
            }]);
        }

        $stocks = $query->orderBy('kode_barang')->get();

        return view('gudang.stock-barang.index', [
            'breadcrumbs' => [
                [
                    'label' => 'Stock Barang',
                    'url' => route('gudang.stockBarang.index'),
                ],
            ],
            'ubsData' => $ubsData,
            'stocks' => $stocks,
            'selectedUbs' => $ubsId,
            'titleGudang' => $titleGudang,
        ]);
    }

}
