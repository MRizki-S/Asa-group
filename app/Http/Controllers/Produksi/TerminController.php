<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;

class TerminController extends Controller
{
    public function laporanUpah(string $id)
    {
        $unit = \App\Models\PembangunanUnit::with([
            'unit',
            'pembangunanUnitQc',
            'pembangunanUnitRapUpah',
            'pembangunanUnitUpah'
        ])->findOrFail($id);

        $laporan = $unit->pembangunanUnitQc->map(function ($qc) use ($unit) {
            $rap = $unit->pembangunanUnitRapUpah->where('pembangunan_unit_qc_id', $qc->id);
            $real = $unit->pembangunanUnitUpah->where('pembangunan_unit_qc_id', $qc->id);

            return [
                'nama_qc'    => $qc->nama_qc,
                'total_rap'  => $rap->sum('nominal_standar'),
                'total_real' => $real->sum('total_nominal'),
                'details'    => $rap->map(function ($r) use ($real) {
                    $totalRealPerItem = $real->where('nama_upah', $r->nama_upah)->sum('total_nominal');

                    return [
                        'nama_upah'    => $r->nama_upah,
                        'nominal_rap'  => $r->nominal_standar,
                        'nominal_real' => $totalRealPerItem,
                    ];
                })
            ];
        });

        return view('produksi.pembangunan-unit.laporan.upah', compact('unit', 'laporan'));
    }
}
