<?php

namespace App\Http\Controllers\Gudang;

use App\Exports\StockBarangExport;
use App\Http\Controllers\Controller;
use App\Models\Jurnal;
use App\Models\MasterBarang;
use App\Models\PeriodeKeuangan;
use App\Models\StockGudang;
use App\Models\Ubs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StockBarangController extends Controller
{
    private function getStockBarangData(Request $request)
    {
        $ubsId = $request->get('ubs_id', 'all');
        $cariMaterial = $request->get('cariMaterial');

        $ubsData = Ubs::all();

        // Query Utama MasterBarang
        $query = MasterBarang::query()->with(['baseUnit', 'satuanKonversi.satuan']);

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

        return [$stocks, $ubsData, $titleGudang, $ubsId, $cariMaterial];
    }

    public function stockIndex(Request $request)
    {
        [$stocks, $ubsData, $titleGudang, $ubsId, $cariMaterial] = $this->getStockBarangData($request);

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

    public function exportExcel(Request $request)
    {
        [$stocks, $ubsData, $titleGudang, $ubsId, $cariMaterial] = $this->getStockBarangData($request);

        $ubsFileName = $ubsId === 'all' ? 'Semua_Gudang' : ($ubsId === 'hub' ? 'HUB_Pusat' : str_replace(' ', '_', $titleGudang));
        
        $tanggal = now()->format('Y-m-d');
        $filename = 'Stock_Barang_' . $ubsFileName . '_' . $tanggal . '.xlsx';

        return Excel::download(
            new StockBarangExport($stocks, $ubsData, $titleGudang, $ubsId, $cariMaterial),
            $filename
        );
    }


    public function exportPdf(Request $request)
    {
        [$stocks, $ubsData, $titleGudang, $ubsId, $cariMaterial] = $this->getStockBarangData($request);

        $ubsFileName = $ubsId === 'all' ? 'Semua_Gudang' : ($ubsId === 'hub' ? 'HUB_Pusat' : str_replace(' ', '_', $titleGudang));
        
        $tanggal = now()->format('Y-m-d');
        $filename = 'Stock_Barang_' . $ubsFileName . '_' . $tanggal . '.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('gudang.stock-barang.export.pdf', [
            'stocks' => $stocks,
            'titleGudang' => $titleGudang,
            'ubsId' => $ubsId
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

}
