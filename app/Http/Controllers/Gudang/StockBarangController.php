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
use App\Services\NotificationGroupService;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class StockBarangController extends Controller
{
    protected NotificationGroupService $notificationGroup;

    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }

    private function getStockBarangData(Request $request)
    {
        $cariMaterial = $request->get('cariMaterial');
        $ubsData = Ubs::orderBy('id')->get();
        $allUbsCodes = $ubsData->pluck('kode_ubs')->toArray();

        // Ambil ubs_id dari request (bisa string tunggal atau array)
        $rawUbs = $request->get('ubs_id');

        if ($rawUbs === null) {
            // Default: Semua gudang aktif
            $selectedUbsCodes = $allUbsCodes;
        } elseif (is_string($rawUbs)) {
            if ($rawUbs === 'all') {
                $selectedUbsCodes = $allUbsCodes;
            } else {
                $selectedUbsCodes = in_array($rawUbs, $allUbsCodes) ? [$rawUbs] : $allUbsCodes;
            }
        } elseif (is_array($rawUbs)) {
            if (in_array('all', $rawUbs)) {
                $selectedUbsCodes = $allUbsCodes;
            } else {
                $selectedUbsCodes = array_values(array_intersect($allUbsCodes, $rawUbs));
            }
        } else {
            $selectedUbsCodes = $allUbsCodes;
        }

        $isAll = count($selectedUbsCodes) === count($allUbsCodes) && count($allUbsCodes) > 0;

        // Penentuan Judul Gudang
        if ($isAll) {
            $titleGudang = 'Semua Gudang';
        } elseif (empty($selectedUbsCodes)) {
            $titleGudang = 'Tidak Ada Gudang Dipilih';
        } elseif (count($selectedUbsCodes) === 1) {
            $currentUbs = $ubsData->firstWhere('kode_ubs', $selectedUbsCodes[0]);
            $titleGudang = $currentUbs ? $currentUbs->nama_ubs : $selectedUbsCodes[0];
        } else {
            $titleGudang = implode(' + ', $selectedUbsCodes);
        }

        // Query Utama MasterBarang
        $query = MasterBarang::query()->with(['baseUnit', 'satuanKonversi.satuan']);

        // Eager Load Nota Details untuk FIFO
        $query->with([
            'notaDetails' => function ($q) {
                $q->where('jumlah_sisa', '>', 0)
                    ->whereHas('nota', function ($queryNota) {
                        $queryNota->where('status', 'posted');
                    })
                    ->with('nota.supplier')
                    ->orderBy('created_at', 'asc'); // FIFO
            }
        ]);

        // Filter Pencarian Nama/Kode Barang
        if ($cariMaterial) {
            $query->where(function ($q) use ($cariMaterial) {
                $q->where('nama_barang', 'like', '%' . $cariMaterial . '%')
                    ->orWhere('kode_barang', 'like', '%' . $cariMaterial . '%');
            });
        }

        // Filter Berdasarkan Gudang Terpilih
        $selectedDbUbs = $ubsData->whereIn('kode_ubs', $selectedUbsCodes);
        $selectedDbUbsIds = $selectedDbUbs->pluck('id')->toArray();

        $query->with(['stock' => function ($q) use ($selectedDbUbsIds) {
            $q->where('stock_type', 'UBS')->whereIn('ubs_id', $selectedDbUbsIds)->with('ubs');
        }]);

        $stocks = $query->orderBy('kode_barang')->get();

        return [$stocks, $ubsData, $titleGudang, $selectedUbsCodes, $cariMaterial, $selectedDbUbs, $isAll];
    }

    public function stockIndex(Request $request)
    {
        [$stocks, $ubsData, $titleGudang, $selectedUbsCodes, $cariMaterial, $selectedDbUbs, $isAll] = $this->getStockBarangData($request);

        return view('gudang.stock-barang.index', [
            'breadcrumbs' => [
                [
                    'label' => 'Stock Barang',
                    'url' => route('gudang.stockBarang.index'),
                ],
            ],
            'ubsData' => $ubsData,
            'stocks' => $stocks,
            'selectedUbs' => $selectedUbsCodes,
            'selectedDbUbs' => $selectedDbUbs,
            'titleGudang' => $titleGudang,
            'isAll' => $isAll,
        ]);
    }

    public function exportExcel(Request $request)
    {
        [$stocks, $ubsData, $titleGudang, $selectedUbsCodes, $cariMaterial, $selectedDbUbs, $isAll] = $this->getStockBarangData($request);

        $ubsFileName = str_replace([' ', '+'], '_', $titleGudang);
        
        $tanggal = now()->format('Y-m-d');
        $filename = 'Stock_Barang_' . $ubsFileName . '_' . $tanggal . '.xlsx';

        return Excel::download(
            new StockBarangExport($stocks, $selectedDbUbs, $titleGudang, $cariMaterial),
            $filename
        );
    }


    public function exportPdf(Request $request)
    {
        [$stocks, $ubsData, $titleGudang, $selectedUbsCodes, $cariMaterial, $selectedDbUbs, $isAll] = $this->getStockBarangData($request);

        $ubsFileName = str_replace([' ', '+'], '_', $titleGudang);
        
        $tanggal = now()->format('Y-m-d');
        $filename = 'Stock_Barang_' . $ubsFileName . '_' . $tanggal . '.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('gudang.stock-barang.export.pdf', [
            'stocks' => $stocks,
            'titleGudang' => $titleGudang,
            'selectedDbUbs' => $selectedDbUbs,
            'cariMaterial' => $cariMaterial,
            'isAll' => $isAll,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    public function toggleFreezeState(Request $request)
    {
        $current = \App\Models\AppSetting::isFreeze();
        $newStatus = $current ? '0' : '1';
        \App\Models\AppSetting::setValue('is_freeze_stok', $newStatus);

        $user = Auth::user();
        $userName = $user ? $user->name : 'Sistem/Admin';
        $waktu = now()->translatedFormat('d F Y H:i');

        if ($newStatus === '1') {
            $msg = 'Mode Freeze Pencocokan Stok Opname berhasil diaktifkan! Seluruh transaksi barang, order, retur, dan edit stok sementara dibekukan.';
            $waMessage = "*PEMBERITAHUAN FREEZE STOK OPNAME* ❄️\n\n"
                       . "Perhatian Tim Gudang & Operasional,\n\n"
                       . "Status Stok Barang telah *DIFREEZE (DIBEKUKAN)* untuk persiapan pencocokan *Stok Opname* dalam waktu dekat.\n\n"
                       . "⚠️ *Seluruh transaksi barang (order, retur, pengeluaran, penerimaan, & edit stok) sementara DITOLAK / DIBEKUKAN.*\n\n"
                       . "• Oleh: *{$userName}*\n"
                       . "• Waktu: *{$waktu} WIB*\n\n"
                       . "Mohon kerjasamanya. Terima kasih! 🙏";
        } else {
            $msg = 'Mode Freeze Pencocokan Stok Opname berhasil dinonaktifkan! Transaksi barang telah dibuka kembali.';
            $waMessage = "*PEMBERITAHUAN UNFREEZE STOK BARANG* 🔓\n\n"
                       . "Perhatian Tim Gudang & Operasional,\n\n"
                       . "Status Stok Barang telah *UNFREEZE (DIBUKA KEMBALI)*.\n\n"
                       . "✅ *Seluruh transaksi barang & mutasi stok gudang dapat dilakukan kembali seperti biasa.*\n\n"
                       . "• Oleh: *{$userName}*\n"
                       . "• Waktu: *{$waktu} WIB*\n\n"
                       . "Terima kasih atas kerjasamanya! 🙏";
        }

        $groupId = env('FONNTE_ID_GROUP_GUDANG_STOCK');
        if ($groupId) {
            $this->notificationGroup->send($groupId, $waMessage);
        }

        return redirect()->back()->with('success', $msg);
    }

}
