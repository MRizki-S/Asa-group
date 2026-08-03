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


    public function update(Request $request, $id = null)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'alasan_ditolak' => 'required_if:action,reject',
            'ids' => 'nullable|array',
            'ids.*' => 'integer'
        ]);

        $ids = $request->input('ids', []);
        if (empty($ids) && $id) {
            $ids = [$id];
        }

        if (empty($ids)) {
            return back()->with('error', 'Tidak ada pengajuan yang dipilih.');
        }

        $pengajuans = PembangunanUnitUpahPengajuan::whereIn('id', $ids)->get();
        $action = $request->action;
        $now = now();
        $roleName = Auth::user()->roles->pluck('name')->first() ?? 'Pemeriksa';

        $processedItems = collect();
        foreach ($pengajuans as $pengajuan) {
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
            } elseif ($action === 'approve') {
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
                        'pembangunan_unit_id'          => $pengajuan->pembangunan_unit_id,
                        'pembangunan_unit_qc_id'       => $pengajuan->pembangunan_unit_qc_id,
                        'pembangunan_unit_rap_upah_id' => $pengajuan->pembangunan_unit_rap_upah_id,
                        'nama_upah'                    => $pengajuan->nama_upah,
                        'total_nominal'                => $pengajuan->nominal_diajukan,
                    ]);
                }
            }
            $processedItems->push($pengajuan);
        }

        if ($processedItems->isNotEmpty()) {
            $this->sendWaNotificationResponseBulk($processedItems, $action === 'approve', $roleName);
        }

        $msgText = count($ids) > 1 ? 'Beberapa pengajuan upah' : 'Pengajuan upah';
        $actText = $action === 'approve' ? 'berhasil disetujui.' : 'berhasil ditolak.';
        return back()->with('success', "{$msgText} {$actText}");
    }

    private function sendWaNotificationResponseBulk($pengajuans, bool $isApprove, string $roleName): void
    {
        try {
            $groupId = env('FONNTE_ID_GROUP_PERSETUJUAN_UPAH_UNIT', env('FONNTE_ID_GROUP_KONFIRMASI_PEMBANGUNAN'));
            if (!$groupId) return;

            $pengajuans->load(['pembangunanUnit.unit.tahap.perumahaan', 'pembangunanUnitQc']);

            $msg = view('notifications.whatsapp.pembangunan_unit.persetujuan_upah', [
                'statusAction' => 'konfirmasi',
                'isApprove' => $isApprove,
                'items' => $pengajuans,
                'penyetuju' => Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Pemeriksa',
                'rolePenyetuju' => $roleName,
                'tanggal' => now()->format('d/m/Y H:i') . ' WIB'
            ])->render();

            app(\App\Services\NotificationGroupService::class)->send($groupId, $msg);
        } catch (\Exception $ex) {
            \Illuminate\Support\Facades\Log::error('WA Upah Exception: ' . $ex->getMessage());
        }
    }
}
