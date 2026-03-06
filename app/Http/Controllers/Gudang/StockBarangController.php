<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\MasterBarang;
use App\Models\Ubs;
use Illuminate\Http\Request;

class StockBarangController extends Controller
{
    private function getStockBarang(Request $request)
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

    public function stockIndex()
    {
        $ubsData = Ubs::all();

        $stocks = MasterBarang::with([
            'stockHub',
            'notaDetails' => function ($q) {
                $q->where('jumlah_sisa', '>', 0)
                    ->with('nota')
                    ->orderBy('created_at', 'asc'); // FIFO order
            }
        ])
            ->whereHas('stockHub', function ($q) {
                $q->where('jumlah_stock', '>', 0);
            })
            ->orderBy('kode_barang')
            ->get();

        return view('gudang.stock-barang.index', [
            'breadcrumbs' => [
                [
                    'label' => 'Stock Barang',
                    'url' => route('gudang.stockBarang.index'),
                ],
            ],
            'ubsData' => $ubsData,
            'stocks' => $stocks,
        ]);
    }

}
