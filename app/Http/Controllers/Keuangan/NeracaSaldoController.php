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
            $normalBalance = $akun->kategori->normal_balance ?? 'D';

            // 1. Saldo Awal (Carry Over dari periode sebelumnya)
            $saldoAwalQuery = JurnalDetail::where('akun_id', $akun->id)
                ->whereHas('jurnal', function ($q) use ($tanggalMulai, $ubsId) {
                    $q->where('status', 'posted')
                        ->whereDate('tanggal', '<', $tanggalMulai);
                    if ($ubsId && $ubsId !== 'all') {
                        $q->where('ubs_id', $ubsId);
                    }
                })
                ->selectRaw('COALESCE(SUM(debit),0) as total_debit, COALESCE(SUM(kredit),0) as total_kredit')
                ->first();

            $saRawDebit = $saldoAwalQuery->total_debit ?? 0;
            $saRawKredit = $saldoAwalQuery->total_kredit ?? 0;

            if ($normalBalance === 'D') {
                $saNet = $saRawDebit - $saRawKredit;
                $saTampilDebit = $saNet > 0 ? $saNet : 0;
                $saTampilKredit = $saNet < 0 ? abs($saNet) : 0;
            } else {
                $saNet = $saRawKredit - $saRawDebit;
                $saTampilKredit = $saNet > 0 ? $saNet : 0;
                $saTampilDebit = $saNet < 0 ? abs($saNet) : 0;
            }

            // 2. Mutasi Umum (Jurnal Umum)
            $mutasiUmum = JurnalDetail::where('akun_id', $akun->id)
                ->whereHas('jurnal', function ($q) use ($tanggalMulai, $tanggalSelesai, $ubsId) {
                    $q->where('status', 'posted')
                        ->where('jenis_jurnal', 'umum')
                        ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);
                    if ($ubsId && $ubsId !== 'all') {
                        $q->where('ubs_id', $ubsId);
                    }
                })
                ->selectRaw('COALESCE(SUM(debit),0) as total_debit, COALESCE(SUM(kredit),0) as total_kredit')
                ->first();

            $mutUmumDebit = $mutasiUmum->total_debit ?? 0;
            $mutUmumKredit = $mutasiUmum->total_kredit ?? 0;

            // Saldo Akhir Umum (Dibutuhkan untuk NS Penyesuaian)
            $sakUmumDCalc = $saTampilDebit + $mutUmumDebit;
            $sakUmumKCalc = $saTampilKredit + $mutUmumKredit;

            if ($normalBalance === 'D') {
                $sakUmumNet = $sakUmumDCalc - $sakUmumKCalc;
                $sakUmumTampilDebit = $sakUmumNet > 0 ? $sakUmumNet : 0;
                $sakUmumTampilKredit = $sakUmumNet < 0 ? abs($sakUmumNet) : 0;
            } else {
                $sakUmumNet = $sakUmumKCalc - $sakUmumDCalc;
                $sakUmumTampilKredit = $sakUmumNet > 0 ? $sakUmumNet : 0;
                $sakUmumTampilDebit = $sakUmumNet < 0 ? abs($sakUmumNet) : 0;
            }

            // 3. Mutasi Penyesuaian
            $mutasiAdj = JurnalDetail::where('akun_id', $akun->id)
                ->whereHas('jurnal', function ($q) use ($tanggalMulai, $tanggalSelesai, $ubsId) {
                    $q->where('status', 'posted')
                        ->where('jenis_jurnal', 'penyesuaian')
                        ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);
                    if ($ubsId && $ubsId !== 'all') {
                        $q->where('ubs_id', $ubsId);
                    }
                })
                ->selectRaw('COALESCE(SUM(debit),0) as total_debit, COALESCE(SUM(kredit),0) as total_kredit')
                ->first();

            $mutAdjDebit = $mutasiAdj->total_debit ?? 0;
            $mutAdjKredit = $mutasiAdj->total_kredit ?? 0;

            // Saldo Akhir Adj
            $sakAdjDCalc = $sakUmumTampilDebit + $mutAdjDebit;
            $sakAdjKCalc = $sakUmumTampilKredit + $mutAdjKredit;

            if ($normalBalance === 'D') {
                $sakAdjNet = $sakAdjDCalc - $sakAdjKCalc;
                $sakAdjTampilDebit = $sakAdjNet > 0 ? $sakAdjNet : 0;
                $sakAdjTampilKredit = $sakAdjNet < 0 ? abs($sakAdjNet) : 0;
            } else {
                $sakAdjNet = $sakAdjKCalc - $sakAdjDCalc;
                $sakAdjTampilKredit = $sakAdjNet > 0 ? $sakAdjNet : 0;
                $sakAdjTampilDebit = $sakAdjNet < 0 ? abs($sakAdjNet) : 0;
            }

            // 4. Saldo Akhir Final (Total Mutasi)
            $mutTotalDebit = $mutUmumDebit + $mutAdjDebit;
            $mutTotalKredit = $mutUmumKredit + $mutAdjKredit;

            $sakFinalDCalc = $saTampilDebit + $mutTotalDebit;
            $sakFinalKCalc = $saTampilKredit + $mutTotalKredit;

            if ($normalBalance === 'D') {
                $sakFinalNet = $sakFinalDCalc - $sakFinalKCalc;
                $sakFinalTampilDebit = $sakFinalNet > 0 ? $sakFinalNet : 0;
                $sakFinalTampilKredit = $sakFinalNet < 0 ? abs($sakFinalNet) : 0;
            } else {
                $sakFinalNet = $sakFinalKCalc - $sakFinalDCalc;
                $sakFinalTampilKredit = $sakFinalNet > 0 ? $sakFinalNet : 0;
                $sakFinalTampilDebit = $sakFinalNet < 0 ? abs($sakFinalNet) : 0;
            }

            $data->push((object) [
                'kode_akun' => $akun->kode_akun,
                'nama_akun' => $akun->nama_akun,

                // Sesuai permintaan View (Final Only)
                'sa_debit' => $saTampilDebit,
                'sa_kredit' => $saTampilKredit,
                'mutasi_debit' => $mutTotalDebit,
                'mutasi_kredit' => $mutTotalKredit,
                'sak_debit' => $sakFinalTampilDebit,
                'sak_kredit' => $sakFinalTampilKredit,

                // Detail untuk Excel (18 kolom)
                'ns_awal_sa_debit' => $saTampilDebit,
                'ns_awal_sa_kredit' => $saTampilKredit,
                'ns_awal_mut_debit' => $mutUmumDebit,
                'ns_awal_mut_kredit' => $mutUmumKredit,
                'ns_awal_sak_debit' => $sakUmumTampilDebit,
                'ns_awal_sak_kredit' => $sakUmumTampilKredit,

                'ns_adj_sa_debit' => $sakUmumTampilDebit,
                'ns_adj_sa_kredit' => $sakUmumTampilKredit,
                'ns_adj_mut_debit' => $mutAdjDebit,
                'ns_adj_mut_kredit' => $mutAdjKredit,
                'ns_adj_sak_debit' => $sakAdjTampilDebit,
                'ns_adj_sak_kredit' => $sakAdjTampilKredit,

                'ns_akhir_sa_debit' => $saTampilDebit,
                'ns_akhir_sa_kredit' => $saTampilKredit,
                'ns_akhir_mut_debit' => $mutTotalDebit,
                'ns_akhir_mut_kredit' => $mutTotalKredit,
                'ns_akhir_sak_debit' => $sakFinalTampilDebit,
                'ns_akhir_sak_kredit' => $sakFinalTampilKredit,
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
