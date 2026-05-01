<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\PembangunanUnitUpah;
use App\Models\PembangunanUnitUpahPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PersetujuanUpahController extends Controller
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

        return view('produksi.persetujuan-upah.index', [
            'allUpahPengajuan' => $allUpahPengajuan,
            'filter'           => $filter,
            'breadcrumbs'      => [
                ['label' => 'Persetujuan Upah', 'url' => route('produksi.persetujuanUpah.index')]
            ],
        ]);
    }

    public function indexKeuangan(Request $request)
    {
        $filter = $request->query('filter', 'menunggu');
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = PembangunanUnitUpahPengajuan::with([
            'pembangunanUnit.unit',
            'pembangunanUnit.qcContainer',
            'pembangunanUnitQc'
        ])->latest();

        if ($filter === 'disetujui') {
            if ($user->hasRole('Manager Dukungan & Layanan')) {
                $query->whereNotNull('disetujui_mgr_dukungan');
            } elseif ($user->hasRole('Staff Akuntansi')) {
                $query->whereNotNull('disetujui_akuntan');
            } elseif ($user->hasRole('Superadmin')) {
                $query->whereNotNull('disetujui_mgr_dukungan')->whereNotNull('disetujui_akuntan');
            }
        } elseif ($filter === 'ditolak') {
            if ($user->hasRole('Manager Dukungan & Layanan')) {
                $query->where('status_pengajuan', 'ditolak_mgr_dukungan');
            } elseif ($user->hasRole('Staff Akuntansi')) {
                $query->where('status_pengajuan', 'ditolak_akuntan');
            } elseif ($user->hasRole('Superadmin')) {
                $query->whereIn('status_pengajuan', ['ditolak_mgr_dukungan', 'ditolak_akuntan']);
            }
        } else {
            if ($user->hasRole('Manager Dukungan & Layanan')) {
                $query->where('status_pengajuan', 'req_mgr_dukungan');
            } elseif ($user->hasRole('Staff Akuntansi')) {
                $query->where('status_pengajuan', 'req_akuntan');
            } elseif ($user->hasRole('Superadmin')) {
                $query->whereIn('status_pengajuan', ['req_akuntan', 'req_mgr_dukungan']);
            } else {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses.');
            }
        }

        $allUpahPengajuan = $query->get();

        return view('keuangan.persetujuan-upah.index', [
            'allUpahPengajuan' => $allUpahPengajuan,
            'filter'           => $filter,
            'breadcrumbs'      => [
                ['label' => 'Persetujuan Upah', 'url' => route('keuangan.persetujuanUpah.index')]
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
        $now = now();

        if ($action === 'reject') {
            $statusDitolak = match ($pengajuan->status_pengajuan) {
                'req_mgr_produksi' => 'ditolak_mgr_produksi',
                'req_mgr_dukungan' => 'ditolak_mgr_dukungan',
                'req_akuntan'      => 'ditolak_akuntan',
                default            => $pengajuan->status_pengajuan
            };

            $pengajuan->update([
                'status_pengajuan' => $statusDitolak,
                'alasan_ditolak'   => $request->alasan_ditolak,
                'ditolak_pada'     => $now
            ]);

            return back()->with('success', 'Pengajuan berhasil ditolak.');
        }

        if ($action === 'approve') {
            $updateData = [];
            $isFinalApproval = false;
            if ($pengajuan->status_pengajuan === 'req_mgr_produksi') {
                $updateData = ['status_pengajuan' => 'req_mgr_dukungan', 'disetujui_mgr_produksi' => $now];
            } elseif ($pengajuan->status_pengajuan === 'req_mgr_dukungan') {
                $updateData = ['status_pengajuan' => 'req_akuntan', 'disetujui_mgr_dukungan' => $now];
            } elseif ($pengajuan->status_pengajuan === 'req_akuntan') {
                $updateData = ['status_pengajuan' => 'disetujui', 'disetujui_akuntan' => $now];
                $isFinalApproval = true;
            }

            $pengajuan->update($updateData);
            if ($isFinalApproval) {
                PembangunanUnitUpah::create([
                    'pembangunan_unit_id'    => $pengajuan->pembangunan_unit_id,
                    'pembangunan_unit_qc_id' => $pengajuan->pembangunan_unit_qc_id,
                    'nama_upah'              => $pengajuan->nama_upah,
                    'total_nominal'          => $pengajuan->nominal_diajukan,
                ]);
            }

            return back()->with('success', 'Pengajuan berhasil disetujui.');
        }
    }
}
