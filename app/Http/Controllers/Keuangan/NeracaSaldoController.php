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

        // Ambil semua akun leaf
        $akuns = AkunKeuangan::where('is_leaf', true)
            ->orderBy('kode_akun')
            ->get();

        $data = collect();

        foreach ($akuns as $akun) {

            // saldo awal
            $saldoAwal = JurnalDetail::where('akun_id', $akun->id)
                ->whereHas('jurnal', function ($q) use ($tanggalMulai) {
                    $q->where('status', 'posted')
                        ->whereDate('tanggal', '<', $tanggalMulai);
                })
                ->selectRaw('COALESCE(SUM(debit - kredit),0) as saldo')
                ->value('saldo');

            // total mutasi pe rakun
            $mutasi = JurnalDetail::where('akun_id', $akun->id)
                ->whereHas('jurnal', function ($q) use ($tanggalMulai, $tanggalSelesai) {
                    $q->where('status', 'posted')
                        ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);
                })
                ->selectRaw('
                COALESCE(SUM(debit),0) as total_debit,
                COALESCE(SUM(kredit),0) as total_kredit
            ')
                ->first();

            $totalDebit = $mutasi->total_debit ?? 0;
            $totalKredit = $mutasi->total_kredit ?? 0;

            // saldo akhir
            $saldoAkhir = $saldoAwal + ($totalDebit - $totalKredit);

            $data->push((object) [
                'kode_akun' => $akun->kode_akun,
                'nama_akun' => $akun->nama_akun,

                'saldo_awal' => $saldoAwal,
                'mutasi_debit' => $totalDebit,
                'mutasi_kredit' => $totalKredit,
                'saldo_akhir' => $saldoAkhir,
            ]);
        }

        return [$data, $tanggalMulai, $tanggalSelesai, $labelPeriode];
    }


    public function index(Request $request)
    {
        // dd($request->all());
        [$rows, $tanggalMulai, $tanggalSelesai, $labelPeriode] = $this->getNeracaSaldoData($request);

        $periodes = PeriodeKeuangan::orderByDesc('tanggal_mulai')->get();

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
        ]);

    }

    // export excel 
    public function exportExcel(Request $request)
    {
        [$rows, $tanggalMulai, $tanggalSelesai, $labelPeriode] = $this->getNeracaSaldoData($request);

        // Penamaan file lebih informatif
        $namaPeriode = $labelPeriode;
        $filename = 'NeracaSaldo_' . str_replace(' ', '_', $namaPeriode) . '.xlsx';

        return Excel::download(
            new NeracaSaldoExport($rows, $tanggalMulai, $tanggalSelesai, $labelPeriode),
            $filename
        );
    }


    public function exportPdf(Request $request)
    {
        [$rows, $tanggalMulai, $tanggalSelesai, $labelPeriode] = $this->getNeracaSaldoData($request);

        // Penamaan file lebih informatif
        $namaPeriode = $labelPeriode;
        $filename = 'NeracaSaldo_' . str_replace(' ', '_', $namaPeriode) . '.pdf';

        $pdf = Pdf::loadView('keuangan.neraca-saldo.export.pdf', [
            'rows' => $rows,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'labelPeriode' => $labelPeriode,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}
