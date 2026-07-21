<?php

namespace App\Http\Controllers\Akuntan;

use App\Http\Controllers\Controller;
use App\Models\PembangunanProyekUpahPengajuan;
use App\Models\PembangunanProyekUpah;
use Illuminate\Http\Request;

class PersetujuanUpahKontraktorController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'menunggu');

        $query = PembangunanProyekUpahPengajuan::with(['proyek'])->latest();

        if ($filter === 'disetujui') {
            $query->whereNotNull('disetujui_akuntan');
        } elseif ($filter === 'ditolak') {
            $query->where('status_pengajuan', 'ditolak_akuntan');
        } else {
            $query->where('status_pengajuan', 'req_akuntan');
        }

        $allUpahPengajuan = $query->get();

        return view('akuntan.persetujuan_upah_kontraktor.index', [
            'allUpahPengajuan' => $allUpahPengajuan,
            'filter'           => $filter,
            'breadcrumbs'      => [
                ['label' => 'Persetujuan Upah Pemb. Proyek', 'url' => route('akuntan.persetujuanUpahKontraktor.index')]
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
            $pengajuan->disetujui_akuntan = now();
            $pengajuan->status_pengajuan = 'disetujui';

            $existingUpah = PembangunanProyekUpah::where('pembangunan_proyek_id', $pengajuan->pembangunan_proyek_id)
                ->where('nama_upah', $pengajuan->nama_upah)
                ->first();
                
            if (!$existingUpah) {
                PembangunanProyekUpah::create([
                    'pembangunan_proyek_id' => $pengajuan->pembangunan_proyek_id,
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

        return redirect()->back()->with('success', 'Status pengajuan upah kontraktor berhasil diperbarui.');
    }
}
