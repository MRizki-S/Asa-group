<?php

namespace App\Http\Controllers\Akuntan;

use App\Http\Controllers\Controller;
use App\Models\PembangunanUnitUpahPengajuan;
use App\Models\PembangunanUnitUpah;
use Illuminate\Http\Request;

class PersetujuanUpahPropertiController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'menunggu');

        $query = PembangunanUnitUpahPengajuan::with([
            'pembangunanUnit.unit',
            'pembangunanUnit.qcContainer',
            'pembangunanUnitQc',
            'rapUpah'
        ])
        ->select('pembangunan_unit_upah_pengajuan.*')
        ->selectSub(function($q) {
            $q->selectRaw('SUM(up.nominal_diajukan)')
              ->from('pembangunan_unit_upah_pengajuan as up')
              ->whereColumn('up.pembangunan_unit_rap_upah_id', 'pembangunan_unit_upah_pengajuan.pembangunan_unit_rap_upah_id')
              ->whereNull('up.ditolak_pada')
              ->whereColumn('up.id', '<=', 'pembangunan_unit_upah_pengajuan.id');
        }, 'cumulative_requested')
        ->latest();

        if ($filter === 'disetujui') {
            $query->whereNotNull('disetujui_akuntan');
        } elseif ($filter === 'ditolak') {
            $query->where('status_pengajuan', 'ditolak_akuntan');
        } else {
            $query->where('status_pengajuan', 'req_akuntan');
        }

        $allUpahPengajuan = $query->get();

        return view('akuntan.persetujuan_upah_properti.index', [
            'allUpahPengajuan' => $allUpahPengajuan,
            'filter'           => $filter,
            'breadcrumbs'      => [
                ['label' => 'Persetujuan Upah Pemb. Unit', 'url' => route('akuntan.persetujuanUpahProperti.index')]
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'alasan_ditolak' => 'required_if:action,reject'
        ]);

        $pengajuan = PembangunanUnitUpahPengajuan::findOrFail($id);
        $action = $request->action;
        
        if ($action === 'approve') {
            $pengajuan->disetujui_akuntan = now();
            $pengajuan->status_pengajuan = 'disetujui';

            PembangunanUnitUpah::create([
                'pembangunan_unit_id'          => $pengajuan->pembangunan_unit_id,
                'pembangunan_unit_qc_id'       => $pengajuan->pembangunan_unit_qc_id,
                'pembangunan_unit_rap_upah_id' => $pengajuan->pembangunan_unit_rap_upah_id,
                'nama_upah'                    => $pengajuan->nama_upah,
                'total_nominal'                => $pengajuan->nominal_diajukan,
            ]);
        } else {
            $pengajuan->status_pengajuan = 'ditolak_akuntan';
            $pengajuan->alasan_ditolak = $request->alasan_ditolak;
            $pengajuan->ditolak_pada = now();
        }

        $pengajuan->save();

        return redirect()->back()->with('success', 'Status pengajuan upah properti berhasil diperbarui.');
    }
}
