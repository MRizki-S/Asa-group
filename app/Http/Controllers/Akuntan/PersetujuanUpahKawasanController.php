<?php

namespace App\Http\Controllers\Akuntan;

use App\Http\Controllers\Controller;
use App\Models\PembangunanKawasanUpahPengajuan;
use App\Models\PembangunanKawasanUpah;
use Illuminate\Http\Request;

class PersetujuanUpahKawasanController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'menunggu');

        $query = PembangunanKawasanUpahPengajuan::with(['kawasan'])->latest();

        if ($filter === 'disetujui') {
            $query->whereNotNull('disetujui_akuntan');
        } elseif ($filter === 'ditolak') {
            $query->where('status_pengajuan', 'ditolak_akuntan');
        } else {
            $query->where('status_pengajuan', 'req_akuntan');
        }

        $allUpahPengajuan = $query->get();

        return view('akuntan.persetujuan_upah_kawasan.index', [
            'allUpahPengajuan' => $allUpahPengajuan,
            'filter'           => $filter,
            'breadcrumbs'      => [
                ['label' => 'Persetujuan Upah Kawasan', 'url' => route('akuntan.persetujuanUpahKawasan.index')]
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'alasan_ditolak' => 'required_if:action,reject'
        ]);

        $pengajuan = PembangunanKawasanUpahPengajuan::findOrFail($id);
        $action = $request->action;
        
        if ($action === 'approve') {
            $pengajuan->disetujui_akuntan = now();
            $pengajuan->status_pengajuan = 'disetujui';

            $existingUpah = PembangunanKawasanUpah::where('pembangunan_kawasan_id', $pengajuan->pembangunan_kawasan_id)
                ->where('nama_upah', $pengajuan->nama_upah)
                ->first();
                
            if (!$existingUpah) {
                PembangunanKawasanUpah::create([
                    'pembangunan_kawasan_id' => $pengajuan->pembangunan_kawasan_id,
                    'nama_upah' => $pengajuan->nama_upah,
                    'total_nominal' => $pengajuan->nominal_diajukan
                ]);
            } else {
                $existingUpah->increment('total_nominal', $pengajuan->nominal_diajukan);
            }
        } else {
            $pengajuan->status_pengajuan = 'ditolak_akuntan';
            $pengajuan->alasan_ditolak = $request->alasan_ditolak;
            $pengajuan->ditolak_pada = now();
        }

        $pengajuan->save();

        return redirect()->back()->with('success', 'Status pengajuan upah kawasan berhasil diperbarui.');
    }
}
