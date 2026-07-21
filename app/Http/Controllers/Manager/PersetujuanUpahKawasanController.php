<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\PembangunanKawasanUpahPengajuan;
use Illuminate\Http\Request;

class PersetujuanUpahKawasanController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'menunggu');

        $query = PembangunanKawasanUpahPengajuan::with(['kawasan'])->latest();

        if ($filter === 'disetujui') {
            $query->whereNotNull('disetujui_mgr_dukungan');
        } elseif ($filter === 'ditolak') {
            $query->where('status_pengajuan', 'ditolak_mgr_dukungan');
        } else {
            $query->where('status_pengajuan', 'req_mgr_dukungan');
        }

        $allUpahPengajuan = $query->get();

        return view('manager.persetujuan_upah_kawasan.index', [
            'allUpahPengajuan' => $allUpahPengajuan,
            'filter'           => $filter,
            'breadcrumbs'      => [
                ['label' => 'Persetujuan Upah Pemb. Kawasan', 'url' => route('manager.persetujuanUpahKawasan.index')]
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
            $pengajuan->disetujui_mgr_dukungan = now();
            $pengajuan->status_pengajuan = 'req_akuntan';
        } else {
            $pengajuan->status_pengajuan = 'ditolak_mgr_dukungan';
            $pengajuan->alasan_ditolak = $request->alasan_ditolak;
            $pengajuan->ditolak_pada = now();
        }

        $pengajuan->save();

        return redirect()->back()->with('success', 'Status pengajuan upah kawasan berhasil diperbarui.');
    }
}
