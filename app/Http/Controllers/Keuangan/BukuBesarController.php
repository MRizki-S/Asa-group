<?php

namespace App\Http\Controllers\Keuangan;

use App\Models\Ubs;
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
            return [collect(), 0, null, null, 'debit'];
        }

        $akunId = $request->akun_id;
        $periode = PeriodeKeuangan::find($request->periode_id);

        if (!$periode) {
            return [collect(), 0, null, null, 'debit'];
        }

        $tanggalMulai = Carbon::parse($periode->tanggal_mulai)->startOfDay();
        $tanggalSelesai = Carbon::parse($periode->tanggal_selesai)->endOfDay();

        $akunKeuanganData = AkunKeuangan::with('kategori')->find($akunId);
        $normalBalance = $akunKeuanganData && $akunKeuanganData->kategori ? strtolower($akunKeuanganData->kategori->normal_balance) : 'debit';

        $saldoAwalQuery = JurnalDetail::where('akun_id', $akunId)
            ->whereHas('jurnal', function ($q) use ($tanggalMulai, $request) {
                $q->where('status', 'posted')
                    ->whereDate('tanggal', '<', $tanggalMulai)
                    ->when($request->filled('ubs_id') && $request->ubs_id != 'all', function ($q2) use ($request) {
                        $q2->where('ubs_id', $request->ubs_id);
                    });
            });

        if ($normalBalance === 'kredit') {
            $saldoAwal = $saldoAwalQuery->selectRaw('COALESCE(SUM(kredit - debit),0) as saldo')->value('saldo');
        } else {
            $saldoAwal = $saldoAwalQuery->selectRaw('COALESCE(SUM(debit - kredit),0) as saldo')->value('saldo');
        }
        // dd($saldoAwal);

        $details = JurnalDetail::with(['jurnal.ubs:id,nama_ubs,kode_ubs'])
            ->where('akun_id', $akunId)
            ->whereHas('jurnal', function ($q) use ($tanggalMulai, $tanggalSelesai, $request) {
                $q->where('status', 'posted')
                    ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
                    ->when($request->filled('ubs_id') && $request->ubs_id != 'all', function ($q2) use ($request) {
                        $q2->where('ubs_id', $request->ubs_id);
                    });
            })
            ->orderBy(
                Jurnal::select('tanggal')
                    ->whereColumn('jurnal.id', 'jurnal_detail.jurnal_id')
            )
            ->get();

        $rows = collect();
        $runningSaldo = $saldoAwal;

        foreach ($details as $detail) {

            if ($normalBalance === 'kredit') {
                $runningSaldo += ($detail->kredit - $detail->debit);
            } else {
                $runningSaldo += ($detail->debit - $detail->kredit);
            }

            $rows->push((object) [
                'tanggal' => $detail->jurnal->tanggal,
                'nomor_jurnal' => $detail->jurnal->nomor_jurnal,
                'ubs_abbr' => $detail->jurnal->ubs ? $detail->jurnal->ubs->kode_ubs : 'HUB',
                'keterangan' => $detail->jurnal->keterangan,
                'debit' => $detail->debit,
                'kredit' => $detail->kredit,
                'saldo' => $runningSaldo,
            ]);
        }

        return [$rows, $saldoAwal, $periode, $runningSaldo, $normalBalance];
    }

    public function index(Request $request)
    {
        $periodes = PeriodeKeuangan::orderByDesc('tanggal_mulai')->get();

        $ubsData = Ubs::all();

        $akunKeuangan = AkunKeuangan::with('parent')
            ->where('is_leaf', true)
            ->orderBy('kode_akun')  
            ->get()
            ->groupBy(function ($item) {
                return $item->parent
                    ? $item->parent->nama_akun
                    : 'Lainnya';
            });

        [$rows, $saldoAwal, $periodeAktif, $saldoAkhir, $normalBalance] = $this->getBukuBesarData($request);

        $ubsName = 'HUB (Pusat)';
        $isHub = true;
        if ($request->filled('ubs_id') && $request->ubs_id !== 'all') {
            $selectedUbs = Ubs::find($request->ubs_id);
            if ($selectedUbs) {
                $ubsName = $selectedUbs->nama_ubs;
                $isHub = false;
            }
        }

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
            'ubsData' => $ubsData,
            'ubsName' => $ubsName,
            'isHub' => $isHub,
            'normalBalance' => $normalBalance ?? 'debit',
        ]);
    }

    public function exportExcel(Request $request)
    {
        [$rows, $saldoAwal, $periodeAktif, $saldoAkhir, $normalBalance] = $this->getBukuBesarData($request);
        $akun = AkunKeuangan::find($request->akun_id);

        $ubsName = 'HUB (Pusat)';
        $ubsFileName = 'HUB';

        if ($request->filled('ubs_id') && $request->ubs_id !== 'all') {
            $selectedUbs = Ubs::find($request->ubs_id);
            if ($selectedUbs) {
                $ubsName = $selectedUbs->nama_ubs;
                $ubsFileName = str_replace(' ', '_', $selectedUbs->kode_ubs);
            }
        }

        // Penamaan file lebih informatif
        $namaPeriode = $periodeAktif ? $periodeAktif->nama_periode : '-';
        $akunFileName = str_replace(' ', '_', ($akun->nama_akun ?? 'Semua'));
        $filename = 'BukuBesar_' . $ubsFileName . '_' . $akunFileName . '_' . $namaPeriode . '.xlsx';

        return Excel::download(
            new BukuBesarExport($rows, $saldoAwal, $periodeAktif, $saldoAkhir, $akun, $ubsName, $normalBalance),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        [$rows, $saldoAwal, $periodeAktif, $saldoAkhir, $normalBalance] = $this->getBukuBesarData($request);

        $akun = AkunKeuangan::find($request->akun_id);

        $totalDebit = $rows->sum('debit');
        $totalKredit = $rows->sum('kredit');

        $ubsName = 'HUB (Pusat)';
        $isHub = true;
        $ubsFileName = 'HUB';

        if ($request->filled('ubs_id') && $request->ubs_id !== 'all') {
            $selectedUbs = Ubs::find($request->ubs_id);
            if ($selectedUbs) {
                $ubsName = $selectedUbs->nama_ubs;
                $ubsFileName = str_replace(' ', '_', $selectedUbs->kode_ubs);
                $isHub = false;
            }
        }

        // Penamaan file lebih informatif
        $namaPeriode = $periodeAktif ? $periodeAktif->nama_periode : '-';
        $akunFileName = str_replace(' ', '_', ($akun->nama_akun ?? 'Semua'));
        $filename = 'BukuBesar_' . $ubsFileName . '_' . $akunFileName . '_' . $namaPeriode . '.pdf';

        $pdf = Pdf::loadView('keuangan.buku-besar.export.pdf', [
            'rows' => $rows,
            'saldoAwal' => $saldoAwal,
            'totalDebit' => $totalDebit,
            'totalKredit' => $totalKredit,
            'saldoAkhir' => $saldoAkhir,
            'periodeAktif' => $namaPeriode,
            'tanggalStart' => $periodeAktif->tanggal_mulai ?? null,
            'tanggalEnd' => $periodeAktif->tanggal_selesai ?? null,
            'akun' => $akun,
            'ubsName' => $ubsName,
            'isHub' => $isHub,
            'normalBalance' => $normalBalance ?? 'debit',
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

}
