<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\PembangunanProyekUpahPengajuan;
use Illuminate\Http\Request;

class PersetujuanUpahKontraktorController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'menunggu');

        $query = PembangunanProyekUpahPengajuan::with(['proyek'])->latest();

        if ($filter === 'disetujui') {
            $query->whereNotNull('disetujui_mgr_dukungan');
        } elseif ($filter === 'ditolak') {
            $query->where('status_pengajuan', 'ditolak_mgr_dukungan');
        } else {
            $query->where('status_pengajuan', 'req_mgr_dukungan');
        }

        $allUpahPengajuan = $query->get();

        return view('manager.persetujuan_upah_kontraktor.index', [
            'allUpahPengajuan' => $allUpahPengajuan,
            'filter'           => $filter,
            'breadcrumbs'      => [
                ['label' => 'Persetujuan Upah Kontraktor', 'url' => route('manager.persetujuanUpahKontraktor.index')]
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'alasan_ditolak' => 'required_if:action,reject'
        ]);

        $pengajuan = PembangunanProyekUpahPengajuan::findOrFail($id);
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

        return redirect()->back()->with('success', 'Status pengajuan upah kontraktor berhasil diperbarui.');
    }
}
