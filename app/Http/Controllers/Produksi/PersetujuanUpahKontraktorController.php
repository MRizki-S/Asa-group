<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\PembangunanProyekUpahPengajuan;
use App\Models\PembangunanProyekUpah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PersetujuanUpahKontraktorController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'menunggu');

        $query = PembangunanProyekUpahPengajuan::with(['proyek'])->latest();

        if ($filter === 'disetujui') {
            $query->whereNotNull('disetujui_mgr_produksi');
        } elseif ($filter === 'ditolak') {
            $query->where('status_pengajuan', 'ditolak_mgr_produksi');
        } else {
            $query->where('status_pengajuan', 'req_mgr_produksi');
        }

        $allUpahPengajuan = $query->get();

        return view('produksi.persetujuan_upah_kontraktor.index', [
            'allUpahPengajuan' => $allUpahPengajuan,
            'filter'           => $filter,
            'breadcrumbs'      => [
                ['label' => 'Persetujuan Upah Kontraktor', 'url' => route('produksi.persetujuanUpahKontraktor.index')]
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
            $pengajuan->disetujui_mgr_produksi = now();
            $pengajuan->status_pengajuan = 'req_mgr_dukungan';
        } else {
            $pengajuan->status_pengajuan = 'ditolak_mgr_produksi';
            $pengajuan->alasan_ditolak = $request->alasan_ditolak;
            $pengajuan->ditolak_pada = now();
        }

        $pengajuan->save();

        return redirect()->back()->with('success', 'Status pengajuan upah kontraktor berhasil diperbarui.');
    }
}