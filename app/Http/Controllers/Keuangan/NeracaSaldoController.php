<?php

namespace App\Http\Controllers\Keuangan;

use App\Models\AkunKeuangan;
use App\Models\JurnalDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\PeriodeKeuangan;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\NeracaSaldoExport;
use App\Http\Controllers\Controller;
use App\Models\Ubs;
use Maatwebsite\Excel\Facades\Excel;

class NeracaSaldoController extends Controller
{
    private function getNeracaSaldoData(Request $request)
    {
        $tipe = $request->tipe;
        $labelPeriode = '-';

        if ($tipe === 'bulan' && $request->filled('periode_id')) {

            $periode = PeriodeKeuangan::find($request->periode_id);
            if (!$periode)
                return [collect(), null, null, null];

            $tanggalMulai = Carbon::parse($periode->tanggal_mulai)->startOfDay();
            $tanggalSelesai = Carbon::parse($periode->tanggal_selesai)->endOfDay();
            $labelPeriode = $periode->nama_periode; // e.g April 2026

        } elseif ($tipe === 'tahun' && $request->filled('tahun')) {

            $tahun = $request->tahun;

            $tanggalMulai = Carbon::create($tahun, 1, 1)->startOfDay();
            $tanggalSelesai = Carbon::create($tahun, 12, 31)->endOfDay();
            $labelPeriode = 'Tahun ' . $tahun; // e.g Tahun 2024

        } else {
            return [collect(), null, null, null];
        }

        // Ambil semua akun leaf dengan kategory untuk normal_balance
        $akuns = AkunKeuangan::with('kategori')
            ->where('is_leaf', true)
            ->orderBy('kode_akun')
            ->get();

        $data = collect();
        $ubsId = $request->ubs_id;

        foreach ($akuns as $akun) {

            // saldo awal
            $saldoAwal = JurnalDetail::where('akun_id', $akun->id)
                ->whereHas('jurnal', function ($q) use ($tanggalMulai, $ubsId) {
                    $q->where('status', 'posted')
                        ->whereDate('tanggal', '<', $tanggalMulai);
                    if ($ubsId && $ubsId !== 'all') {
                        $q->where('ubs_id', $ubsId);
                    }
                })
                ->selectRaw('COALESCE(SUM(debit),0) as total_debit, COALESCE(SUM(kredit),0) as total_kredit')
                ->first();

            $saDebit = $saldoAwal->total_debit ?? 0;
            $saKredit = $saldoAwal->total_kredit ?? 0;

            $normalBalance = $akun->kategori->normal_balance ?? 'D';

            if ($normalBalance === 'D') {
                $saNet = $saDebit - $saKredit;
                $saTampilDebit = $saNet > 0 ? $saNet : 0;
                $saTampilKredit = $saNet < 0 ? abs($saNet) : 0;
            } else { // K
                $saNet = $saKredit - $saDebit;
                $saTampilKredit = $saNet > 0 ? $saNet : 0;
                $saTampilDebit = $saNet < 0 ? abs($saNet) : 0;
            }

            // total mutasi per akun
            $mutasi = JurnalDetail::where('akun_id', $akun->id)
                ->whereHas('jurnal', function ($q) use ($tanggalMulai, $tanggalSelesai, $ubsId) {
                    $q->where('status', 'posted')
                        ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);
                    if ($ubsId && $ubsId !== 'all') {
                        $q->where('ubs_id', $ubsId);
                    }
                })
                ->selectRaw('
                COALESCE(SUM(debit),0) as total_debit,
                COALESCE(SUM(kredit),0) as total_kredit
            ')
                ->first();

            $mutDebit = $mutasi->total_debit ?? 0;
            $mutKredit = $mutasi->total_kredit ?? 0;

            // saldo akhir
            $sakDebitCalc = $saTampilDebit + $mutDebit;
            $sakKreditCalc = $saTampilKredit + $mutKredit;

            if ($normalBalance === 'D') {
                $sakNet = $sakDebitCalc - $sakKreditCalc;
                $sakTampilDebit = $sakNet > 0 ? $sakNet : 0;
                $sakTampilKredit = $sakNet < 0 ? abs($sakNet) : 0;
            } else {
                $sakNet = $sakKreditCalc - $sakDebitCalc;
                $sakTampilKredit = $sakNet > 0 ? $sakNet : 0;
                $sakTampilDebit = $sakNet < 0 ? abs($sakNet) : 0;
            }

            $data->push((object) [
                'kode_akun' => $akun->kode_akun,
                'nama_akun' => $akun->nama_akun,

                'sa_debit' => $saTampilDebit,
                'sa_kredit' => $saTampilKredit,
                'mutasi_debit' => $mutDebit,
                'mutasi_kredit' => $mutKredit,
                'sak_debit' => $sakTampilDebit,
                'sak_kredit' => $sakTampilKredit,
            ]);
        }

        return [$data, $tanggalMulai, $tanggalSelesai, $labelPeriode];
    }


    public function index(Request $request)
    {
        // dd($request->all());
        [$rows, $tanggalMulai, $tanggalSelesai, $labelPeriode] = $this->getNeracaSaldoData($request);

        $periodes = PeriodeKeuangan::orderByDesc('tanggal_mulai')->get();

        $ubsData = Ubs::all();

        // Ambil akun leaf lalu grouping berdasarkan parent
        $akunKeuangan = AkunKeuangan::with('parent')
            ->where('is_leaf', true)
            ->orderBy('kode_akun')
            ->get()
            ->groupBy(function ($item) {
                return $item->parent
                    ? $item->parent->nama_akun
                    : 'Lainnya';
            });


        $ubsName = 'HUB (Pusat)';
        $isHub = true;
        if ($request->filled('ubs_id') && $request->ubs_id !== 'all') {
            $selectedUbs = Ubs::find($request->ubs_id);
            if ($selectedUbs) {
                $ubsName = $selectedUbs->nama_ubs;
                $isHub = false;
            }
        }

        return view('keuangan.neraca-saldo.index', [
            'breadcrumbs' => [
                ['label' => 'Neraca Saldo', 'url' => route('keuangan.neracaSaldo.index')],
            ],
            'periodes' => $periodes,
            'akunKeuangan' => $akunKeuangan,
            'rows' => $rows,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'labelPeriode' => $labelPeriode,
            'ubsData' => $ubsData,
            'ubsName' => $ubsName,
            'isHub' => $isHub,
        ]);

    }

    // export excel 
    public function exportExcel(Request $request)
    {
        [$rows, $tanggalMulai, $tanggalSelesai, $labelPeriode] = $this->getNeracaSaldoData($request);

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
        $namaPeriode = $labelPeriode;
        $filename = 'NeracaSaldo_' . $ubsFileName . '_' . str_replace(' ', '_', subject: $namaPeriode) . '.xlsx';

        return Excel::download(
            new NeracaSaldoExport($rows, $tanggalMulai, $tanggalSelesai, $labelPeriode, $ubsName),
            $filename
        );
    }


    public function exportPdf(Request $request)
    {
        [$rows, $tanggalMulai, $tanggalSelesai, $labelPeriode] = $this->getNeracaSaldoData($request);

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
        $namaPeriode = $labelPeriode;
        $filename = 'NeracaSaldo_' . $ubsFileName . '_' . str_replace(' ', '_', $namaPeriode) . '.pdf';

        $pdf = Pdf::loadView('keuangan.neraca-saldo.export.pdf', [
            'rows' => $rows,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'labelPeriode' => $labelPeriode,
            'ubsName' => $ubsName,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}
