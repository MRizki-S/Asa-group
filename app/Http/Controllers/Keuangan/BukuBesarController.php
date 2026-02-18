<?php

namespace App\Http\Controllers\Keuangan;

use App\Models\Jurnal;
use App\Models\AkunKeuangan;
use App\Models\JurnalDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\PeriodeKeuangan;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\BukuBesarExport;
use App\Http\Controllers\Controller;
use App\Models\KategoriAkunKeuangan;
use Maatwebsite\Excel\Facades\Excel;

class BukuBesarController extends Controller
{
    private function getBukuBesarData(Request $request)
    {
        if (!$request->filled('akun_id') || !$request->filled('periode_id')) {
            return [collect(), 0, null, null];
        }

        $akunId = $request->akun_id;
        $periode = PeriodeKeuangan::find($request->periode_id);

        if (!$periode) {
            return [collect(), 0, null, null];
        }

        $tanggalMulai = Carbon::parse($periode->tanggal_mulai)->startOfDay();
        $tanggalSelesai = Carbon::parse($periode->tanggal_selesai)->endOfDay();

        $saldoAwal = JurnalDetail::where('akun_id', $akunId)
            ->whereHas('jurnal', function ($q) use ($tanggalMulai) {
                $q->where('status', 'posted')
                    ->whereDate('tanggal', '<', $tanggalMulai);
            })
            ->selectRaw('COALESCE(SUM(debit - kredit),0) as saldo')
            ->value('saldo');
        // dd($saldoAwal);

        $details = JurnalDetail::with(['jurnal'])
            ->where('akun_id', $akunId)
            ->whereHas('jurnal', function ($q) use ($tanggalMulai, $tanggalSelesai) {
                $q->where('status', 'posted')
                    ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);
            })
            ->orderBy(
                Jurnal::select('tanggal')
                    ->whereColumn('jurnal.id', 'jurnal_detail.jurnal_id')
            )
            ->get();

        $rows = collect();
        $runningSaldo = $saldoAwal;

        foreach ($details as $detail) {

            $runningSaldo += ($detail->debit - $detail->kredit);

            $rows->push((object) [
                'tanggal' => $detail->jurnal->tanggal,
                'nomor_jurnal' => $detail->jurnal->nomor_jurnal,
                'keterangan' => $detail->jurnal->keterangan,
                'debit' => $detail->debit,
                'kredit' => $detail->kredit,
                'saldo' => $runningSaldo,
            ]);
        }

        return [$rows, $saldoAwal, $periode, $runningSaldo];
    }

    public function index(Request $request)
    {
        $periodes = PeriodeKeuangan::orderByDesc('tanggal_mulai')->get();

        $akunKeuangan = AkunKeuangan::with('parent')
            ->where('is_leaf', true)
            ->orderBy('kode_akun')
            ->get()
            ->groupBy(function ($item) {
                return $item->parent
                    ? $item->parent->nama_akun
                    : 'Lainnya';
            });

        [$rows, $saldoAwal, $periodeAktif, $saldoAkhir] = $this->getBukuBesarData($request);
        return view('keuangan.buku-besar.index', [
            'breadcrumbs' => [
                ['label' => 'Buku Besar', 'url' => route('keuangan.bukuBesar.index')],
            ],
            'periodes' => $periodes,
            'akunKeuangan' => $akunKeuangan,
            'rows' => $rows,
            'saldoAwal' => $saldoAwal,  
            'saldoAkhir' => $saldoAkhir,
            'periodeAktif' => $periodeAktif,
        ]);
    }

    // export excel 
    public function exportExcel(Request $request)
    {
        [$rows, $saldoAwal, $periodeAktif, $saldoAkhir] = $this->getBukuBesarData($request);
        $akun = AkunKeuangan::find($request->akun_id);

        // Penamaan file lebih informatif
        $namaPeriode = $periodeAktif ? $periodeAktif->nama_periode : '-';
        $filename = 'BukuBesar_' . str_replace(' ', '_', ($akun->nama_akun ?? 'Semua')) . '_' . $namaPeriode . '.xlsx';

        return Excel::download(
            new BukuBesarExport($rows, $saldoAwal, $periodeAktif, $saldoAkhir, $akun),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        [$rows, $saldoAwal, $periodeAktif, $saldoAkhir] = $this->getBukuBesarData($request);

        $akun = AkunKeuangan::find($request->akun_id);

        $totalDebit = $rows->sum('debit');
        $totalKredit = $rows->sum('kredit');

        // Penamaan file lebih informatif
        $namaPeriode = $periodeAktif ? $periodeAktif->nama_periode : '-';
        $filename = 'BukuBesar_' . str_replace(' ', '_', ($akun->nama_akun ?? 'Semua')) . '_' . $namaPeriode . '.pdf';

        $pdf = Pdf::loadView('keuangan.buku-besar.export.pdf', [
            'rows' => $rows,
            'saldoAwal' => $saldoAwal,
            'totalDebit' => $totalDebit,
            'totalKredit' => $totalKredit,
            'saldoAkhir' => $saldoAkhir,
            'periodeAktif' => $namaPeriode,
            'tanggalStart' => $periodeAktif->tanggal_mulai,
            'tanggalEnd' => $periodeAktif->tanggal_selesai,
            'akun' => $akun,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

}
