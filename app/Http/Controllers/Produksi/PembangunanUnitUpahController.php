<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\PembangunanUnitUpah;
use App\Models\PembangunanUnitUpahPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PembangunanUnitUpahController extends Controller
{

    public function index()
    {
        $query = PembangunanUnitUpahPengajuan::with([
            'pembangunanUnit.unit',
            'pembangunanUnit.tahap',
            'pembangunanUnit.qcContainer',
            'pembangunanUnitQc'
        ])->latest();

        $allUpahPengajuan = $query->get();

        $allUpahPengajuan = $allUpahPengajuan->whereIn('status_pengajuan', ['req_mgr_produksi', 'ditolak_mgr_produksi']);

        return view('produksi.persetujuan-upah.index', [
            'allUpahPengajuan' => $allUpahPengajuan,
            'breadcrumbs'      => [
                ['label' => 'Persetujuan Upah', 'url' => route('produksi.persetujuanUpah.index')]
            ],
        ]);
    }

    public function indexKeuangan()
    {
        $query = PembangunanUnitUpahPengajuan::with([
            'pembangunanUnit.unit',
            'pembangunanUnit.tahap',
            'pembangunanUnit.qcContainer',
            'pembangunanUnitQc'
        ])->latest();

        $allUpahPengajuan = $query->get();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('Manager Dukungan & Layanan')) {
            $allUpahPengajuan = $allUpahPengajuan->whereIn('status_pengajuan', ['req_mgr_dukungan', 'ditolak_mgr_dukungan']);
        } elseif ($user->hasRole('Staff Akuntansi')) {
            $allUpahPengajuan = $allUpahPengajuan->whereIn('status_pengajuan', ['req_akuntan', 'ditolak_akuntan']);
        } elseif ($user->hasRole('Superadmin')) {
            $allUpahPengajuan = $allUpahPengajuan->whereIn('status_pengajuan', ['req_akuntan', 'ditolak_akuntan', 'req_mgr_dukungan', 'ditolak_mgr_dukungan']);
        } else {
            $allUpahPengajuan = [];
        }

        return view('keuangan.persetujuan-upah.index', [
            'allUpahPengajuan' => $allUpahPengajuan,
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

    public function store(Request $request)
    {
        $request->validate([
            'pembangunan_unit_id' => 'required',
            'pembangunan_unit_qc_id' => 'required',
            'items' => 'required|array|min:1',
            'items.*.nominal_pengajuan' => 'required|numeric|min:1',
            'items.*.catatan_pengawas' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->items as $item) {
                PembangunanUnitUpahPengajuan::create([
                    'pembangunan_unit_id' => $request->pembangunan_unit_id,
                    'pembangunan_unit_qc_id' => $request->pembangunan_unit_qc_id,
                    'pembangunan_unit_rap_upah_id' => $item['pembangunan_unit_rap_upah_id'],
                    'nama_upah' => $item['nama_upah'],
                    'nominal_diajukan' => $item['nominal_pengajuan'],
                    'catatan_pengawas' => $item['catatan_pengawas'] ?? null,
                    'tanggal_diajukan' => now(),
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Pengajuan upah berhasil dikirim.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
