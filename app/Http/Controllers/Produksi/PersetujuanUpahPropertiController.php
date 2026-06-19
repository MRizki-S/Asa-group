<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\PembangunanUnitUpahPengajuan;
use App\Models\PembangunanUnitUpah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PersetujuanUpahPropertiController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'menunggu');

        $query = PembangunanUnitUpahPengajuan::with([
            'pembangunanUnit.unit',
            'pembangunanUnit.qcContainer',
            'pembangunanUnitQc'
        ])->latest();

        if ($filter === 'disetujui') {
            $query->whereNotNull('disetujui_mgr_produksi');
        } elseif ($filter === 'ditolak') {
            $query->where('status_pengajuan', 'ditolak_mgr_produksi');
        } else {
            $query->where('status_pengajuan', 'req_mgr_produksi');
        }

        $allUpahPengajuan = $query->get();

        return view('produksi.persetujuan_upah_properti.index', [
            'allUpahPengajuan' => $allUpahPengajuan,
            'filter'           => $filter,
            'breadcrumbs'      => [
                ['label' => 'Persetujuan Upah Properti', 'url' => route('produksi.persetujuanUpahProperti.index')]
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
        
        // Asumsi login sebagai Manager Produksi
        if ($action === 'approve') {
            $pengajuan->disetujui_mgr_produksi = now();
            $pengajuan->status_pengajuan = 'req_mgr_dukungan';
            
            // Generate PembangunanUnitUpah record if it doesn't exist yet
            $existingUpah = PembangunanUnitUpah::where('pembangunan_unit_id', $pengajuan->pembangunan_unit_id)
                ->where('pembangunan_unit_qc_id', $pengajuan->pembangunan_unit_qc_id)
                ->where('nama_upah', $pengajuan->nama_upah)
                ->first();
                
            if (!$existingUpah) {
                PembangunanUnitUpah::create([
                    'pembangunan_unit_id' => $pengajuan->pembangunan_unit_id,
                    'pembangunan_unit_qc_id' => $pengajuan->pembangunan_unit_qc_id,
                    'pembangunan_unit_rap_upah_id' => $pengajuan->pembangunan_unit_rap_upah_id,
                    'nama_upah' => $pengajuan->nama_upah,
                    'total_nominal' => $pengajuan->nominal_diajukan
                ]);
            } else {
                $existingUpah->increment('total_nominal', $pengajuan->nominal_diajukan);
            }
        } else {
            $pengajuan->status_pengajuan = 'ditolak_mgr_produksi';
            $pengajuan->alasan_ditolak = $request->alasan_ditolak;
            $pengajuan->ditolak_pada = now();
        }

        $pengajuan->save();

        return redirect()->back()->with('success', 'Status pengajuan upah properti berhasil diperbarui.');
    }
}