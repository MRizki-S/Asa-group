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
            return [
                'rowsUmum' => collect(),
                'rowsPenyesuaian' => collect(),
                'saldoAwal' => 0,
                'periode' => null,
                'saldoAkhirUmum' => 0,
                'saldoAkhirTotal' => 0,
                'normalBalance' => 'debit'
            ];
        }

        $akunId = $request->akun_id;
        $periode = PeriodeKeuangan::find($request->periode_id);

        if (!$periode) {
            return [
                'rowsUmum' => collect(),
                'rowsPenyesuaian' => collect(),
                'saldoAwal' => 0,
                'periode' => null,
                'saldoAkhirUmum' => 0,
                'saldoAkhirTotal' => 0,
                'normalBalance' => 'debit'
            ];
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

        $rowsUmum = collect();
        $rowsPenyesuaian = collect();
        $runningSaldo = $saldoAwal;

        foreach ($details as $detail) {
            if ($normalBalance === 'kredit') {
                $runningSaldo += ($detail->kredit - $detail->debit);
            } else {
                $runningSaldo += ($detail->debit - $detail->kredit);
            }

            $rowObj = (object) [
                'tanggal' => $detail->jurnal->tanggal,
                'nomor_jurnal' => $detail->jurnal->nomor_jurnal,
                'ubs_abbr' => $detail->jurnal->ubs ? $detail->jurnal->ubs->kode_ubs : 'HUB',
                'keterangan' => $detail->jurnal->keterangan,
                'debit' => $detail->debit,
                'kredit' => $detail->kredit,
                'saldo' => $runningSaldo,
                'jenis_jurnal' => $detail->jurnal->jenis_jurnal,
            ];

            if ($detail->jurnal->jenis_jurnal === 'penyesuaian') {
                $rowsPenyesuaian->push($rowObj);
            } else {
                $rowsUmum->push($rowObj);
            }
        }

        $saldoAkhirUmum = $rowsUmum->isNotEmpty() ? $rowsUmum->last()->saldo : $saldoAwal;

        return [
            'rowsUmum' => $rowsUmum,
            'rowsPenyesuaian' => $rowsPenyesuaian,
            'saldoAwal' => $saldoAwal,
            'periode' => $periode,
            'saldoAkhirUmum' => $saldoAkhirUmum,
            'saldoAkhirTotal' => $runningSaldo,
            'normalBalance' => $normalBalance
        ];
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

        $data = $this->getBukuBesarData($request);
        $rowsUmum = $data['rowsUmum'];
        $rowsPenyesuaian = $data['rowsPenyesuaian'];
        $saldoAwal = $data['saldoAwal'];
        $periodeAktif = $data['periode'];
        $saldoAkhirUmum = $data['saldoAkhirUmum'];
        $saldoAkhirTotal = $data['saldoAkhirTotal'];
        $normalBalance = $data['normalBalance'];

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
            'rowsUmum' => $rowsUmum,
            'rowsPenyesuaian' => $rowsPenyesuaian,
            'saldoAwal' => $saldoAwal,
            'saldoAkhirUmum' => $saldoAkhirUmum,
            'saldoAkhirTotal' => $saldoAkhirTotal,
            'periodeAktif' => $periodeAktif,
            'ubsData' => $ubsData,
            'ubsName' => $ubsName,
            'isHub' => $isHub,
            'normalBalance' => $normalBalance ?? 'debit',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getBukuBesarData($request);
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
        $namaPeriode = $data['periode'] ? $data['periode']->nama_periode : '-';
        $akunFileName = str_replace(' ', '_', ($akun->nama_akun ?? 'Semua'));
        $filename = 'BukuBesar_' . $ubsFileName . '_' . $akunFileName . '_' . $namaPeriode . '.xlsx';

        return Excel::download(
            new BukuBesarExport(
                $data['rowsUmum'],
                $data['rowsPenyesuaian'],
                $data['saldoAwal'],
                $data['periode'],
                $data['saldoAkhirUmum'],
                $data['saldoAkhirTotal'],
                $akun,
                $ubsName,
                $data['normalBalance']
            ),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getBukuBesarData($request);
        $akun = AkunKeuangan::find($request->akun_id);

        $totalDebit = $data['rowsUmum']->sum('debit') + $data['rowsPenyesuaian']->sum('debit');
        $totalKredit = $data['rowsUmum']->sum('kredit') + $data['rowsPenyesuaian']->sum('kredit');

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
        $namaPeriode = $data['periode'] ? $data['periode']->nama_periode : '-';
        $akunFileName = str_replace(' ', '_', ($akun->nama_akun ?? 'Semua'));
        $filename = 'BukuBesar_' . $ubsFileName . '_' . $akunFileName . '_' . $namaPeriode . '.pdf';

        $pdf = Pdf::loadView('keuangan.buku-besar.export.pdf', [
            'rowsUmum' => $data['rowsUmum'],
            'rowsPenyesuaian' => $data['rowsPenyesuaian'],
            'saldoAwal' => $data['saldoAwal'],
            'totalDebit' => $totalDebit,
            'totalKredit' => $totalKredit,
            'saldoAkhirUmum' => $data['saldoAkhirUmum'],
            'saldoAkhirTotal' => $data['saldoAkhirTotal'],
            'periodeAktif' => $namaPeriode,
            'tanggalStart' => $data['periode']->tanggal_mulai ?? null,
            'tanggalEnd' => $data['periode']->tanggal_selesai ?? null,
            'akun' => $akun,
            'ubsName' => $ubsName,
            'isHub' => $isHub,
            'normalBalance' => $data['normalBalance'] ?? 'debit',
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

}
