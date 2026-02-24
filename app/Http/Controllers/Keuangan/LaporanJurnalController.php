<?php

namespace App\Http\Controllers\Keuangan;

use App\Models\Ubs;
use App\Models\Jurnal;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\PeriodeKeuangan;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LaporanJurnalExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class LaporanJurnalController extends Controller
{
    private function getJurnalRows(Request $request)
    {
        $today = Carbon::today();

        $tanggalStart = $request->filled('tanggalStart')
            ? Carbon::parse($request->tanggalStart)->startOfDay()
            : null;

        $tanggalEnd = $request->filled('tanggalEnd')
            ? Carbon::parse($request->tanggalEnd)->endOfDay()
            : null;

        $periodeAktif = PeriodeKeuangan::where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->first();

        if (!$periodeAktif) {
            return [collect(), 0, 0, null];
        }

        $jurnals = Jurnal::posted()
            ->where('jenis_jurnal', 'umum')
            ->where('periode_id', $periodeAktif->id)
            ->when($request->filled('ubs_id') && $request->ubs_id != 'all', function ($q) use ($request) {
                $q->where('ubs_id', $request->ubs_id);
            })
            ->when($tanggalStart && $tanggalEnd, function ($q) use ($tanggalStart, $tanggalEnd) {
                $q->whereBetween('tanggal', [$tanggalStart, $tanggalEnd]);
            })
            ->when($tanggalStart && !$tanggalEnd, function ($q) use ($tanggalStart) {
                $q->where('tanggal', '>=', $tanggalStart);
            })
            ->when(!$tanggalStart && $tanggalEnd, function ($q) use ($tanggalEnd) {
                $q->where('tanggal', '<=', $tanggalEnd);
            })
            ->with([
                'ubs:id,nama_ubs,kode_ubs',
                'details' => function ($q) {
                    $q->where(function ($x) {
                        $x->where('debit', '>', 0)
                            ->orWhere('kredit', '>', 0);
                    })->with('akun:id,kode_akun,nama_akun');
                }
            ])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $rows = collect();

        foreach ($jurnals as $jurnal) {
            foreach ($jurnal->details as $detail) {
                $rows->push((object) [
                    'jurnal_id' => $jurnal->id,
                    'nomor_jurnal' => $jurnal->nomor_jurnal,
                    'tanggal' => $jurnal->tanggal,
                    'ubs_abbr' => $jurnal->ubs ? $jurnal->ubs->kode_ubs : 'HUB',
                    'kode_akun' => $detail->akun->kode_akun,
                    'nama_akun' => $detail->akun->nama_akun,
                    'debit' => $detail->debit,
                    'kredit' => $detail->kredit,
                    'keterangan' => $jurnal->keterangan,
                ]);
            }
        }

        return [
            $rows,
            $rows->sum('debit'),
            $rows->sum('kredit'),
            $periodeAktif
        ];
    }

    public function index(Request $request)
    {
        [$rows, $totalDebit, $totalKredit, $periodeAktif] = $this->getJurnalRows($request);

        $ubsData = Ubs::all();

        $tanggalStart = $request->filled('tanggalStart')
            ? Carbon::parse($request->tanggalStart)
            : null;

        $tanggalEnd = $request->filled('tanggalEnd')
            ? Carbon::parse($request->tanggalEnd)
            : null;

        $titlePeriode = null;

        // ✅ Jika search aktif (pakai filter tanggal)
        if ($tanggalStart || $tanggalEnd) {

            if ($tanggalStart && $tanggalEnd) {
                $titlePeriode = $tanggalStart->translatedFormat('d F Y')
                    . ' - ' .
                    $tanggalEnd->translatedFormat('d F Y');
            } elseif ($tanggalStart) {
                $titlePeriode = 'Dari ' . $tanggalStart->translatedFormat('d F Y');
            } elseif ($tanggalEnd) {
                $titlePeriode = 'Sampai ' . $tanggalEnd->translatedFormat('d F Y');
            }

        }
        // ✅ Jika tidak ada search → pakai periode aktif
        elseif ($periodeAktif) {
            $titlePeriode = Carbon::parse($periodeAktif->tanggal_mulai)
                ->translatedFormat('F Y');
        }

        // ✅ Tambahkan HUB/UBS ke title Periode
        $ubsName = 'HUB';
        if ($request->filled('ubs_id') && $request->ubs_id !== 'all') {
            $selectedUbs = Ubs::find($request->ubs_id);
            $ubsName = $selectedUbs ? $selectedUbs->nama_ubs : 'HUB';
        }
        $titlePeriode = $titlePeriode ? $titlePeriode . ' (' . $ubsName . ')' : '(' . $ubsName . ')';

        $isHub = !$request->filled('ubs_id') || $request->ubs_id === 'all';

        return view('keuangan.laporan-jurnal.index', [
            'breadcrumbs' => [
                ['label' => 'Laporan Jurnal', 'url' => route('keuangan.laporanJurnal.index')],
            ],
            'periodeAktif' => $periodeAktif,
            'rows' => $rows,
            'totalDebit' => $totalDebit,
            'totalKredit' => $totalKredit,
            'isBalanced' => $totalDebit === $totalKredit,
            'titlePeriode' => $titlePeriode,
            'ubsData' => $ubsData,
            'isHub' => $isHub,
        ]);
    }


    // export excel
    public function exportExcel(Request $request)
    {
        [$rows, $totalDebit, $totalKredit, $periodeAktif] = $this->getJurnalRows($request);

        // Logic ubsName & ubsAbbr
        $ubsName = 'HUB';
        $ubsAbbr = 'HUB';
        if ($request->filled('ubs_id') && $request->ubs_id !== 'all') {
            $selectedUbs = Ubs::find($request->ubs_id);
            if ($selectedUbs) {
                $ubsName = $selectedUbs->nama_ubs;
                $ubsAbbr = $selectedUbs->kode_ubs ?? 'HUB';
            }
        }

        // Logika Penamaan File
        $filename = 'Laporan_Jurnal_' . $ubsAbbr;
        if ($request->filled('tanggalStart') && $request->filled('tanggalEnd')) {
            $filename .= '_' . $request->tanggalStart . '_s_d_' . $request->tanggalEnd;
        } elseif ($periodeAktif) {
            $filename .= '_Periode_' . str_replace(' ', '_', $periodeAktif->nama_periode);
        }
        $filename .= '.xlsx';

        return Excel::download(
            new LaporanJurnalExport($rows, $totalDebit, $totalKredit, $request->all(), $periodeAktif, $ubsName),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        [$rows, $totalDebit, $totalKredit, $periodeAktif] = $this->getJurnalRows($request);

        // Logic ubsName & ubsAbbr
        $ubsName = 'HUB';
        $ubsAbbr = 'HUB';
        if ($request->filled('ubs_id') && $request->ubs_id !== 'all') {
            $selectedUbs = Ubs::find($request->ubs_id);
            if ($selectedUbs) {
                $ubsName = $selectedUbs->nama_ubs;
                $ubsAbbr = $selectedUbs->kode_ubs ?? 'HUB';
            }
        }

        // Logika Penamaan File
        $filename = 'Laporan_Jurnal_' . $ubsAbbr;
        if ($request->filled('tanggalStart') && $request->filled('tanggalEnd')) {
            $filename .= '_' . $request->tanggalStart . '_sd_' . $request->tanggalEnd;
        } elseif ($periodeAktif) {
            $filename .= '_Periode_' . str_replace(' ', '_', $periodeAktif->nama_periode);
        }
        $filename .= '.pdf';

        $pdf = Pdf::loadView('keuangan.laporan-jurnal.export.pdf', [
            'rows' => $rows,
            'totalDebit' => $totalDebit,
            'totalKredit' => $totalKredit,
            'periodeAktif' => $periodeAktif,
            'tanggalStart' => $request->tanggalStart,
            'tanggalEnd' => $request->tanggalEnd,
            'ubsName' => $ubsName,
            'ubs_id' => $request->ubs_id ?? 'all',
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}
