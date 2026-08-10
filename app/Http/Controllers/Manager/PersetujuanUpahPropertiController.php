<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\PembangunanUnitUpahPengajuan;
use Illuminate\Http\Request;

class PersetujuanUpahPropertiController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'menunggu');

        $query = PembangunanUnitUpahPengajuan::with([
            'pembangunanUnit.unit.tahap.perumahaan',
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
            $query->whereNotNull('disetujui_mgr_dukungan');
        } elseif ($filter === 'ditolak') {
            $query->where('status_pengajuan', 'ditolak_mgr_dukungan');
        } else {
            $query->where('status_pengajuan', 'req_mgr_dukungan');
        }

        $allUpahPengajuan = $query->get();

        return view('manager.persetujuan_upah_properti.index', [
            'allUpahPengajuan' => $allUpahPengajuan,
            'filter'           => $filter,
            'breadcrumbs'      => [
                ['label' => 'Konfirmasi Upah Borongan', 'url' => route('manager.persetujuanUpahProperti.index')]
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
        $roleName = 'Manager Dukungan';

        $processedIds = [];
        foreach ($pengajuans as $pengajuan) {
            if ($action === 'approve') {
                $pengajuan->disetujui_mgr_dukungan = $now;
                $pengajuan->status_pengajuan = 'req_akuntan';
            } else {
                $pengajuan->status_pengajuan = 'ditolak_mgr_dukungan';
                $pengajuan->alasan_ditolak = $request->alasan_ditolak;
                $pengajuan->ditolak_pada = $now;
            }
            $pengajuan->save();
            $processedIds[] = $pengajuan->id;
        }

        if (!empty($processedIds)) {
            $this->sendWaNotificationResponseBulk($processedIds, $action === 'approve', $roleName);
        }

        return redirect()->back()->with('success', 'Status pengajuan upah berhasil diperbarui.');
    }

    private function sendWaNotificationResponseBulk(array $ids, bool $isApprove, string $roleName): void
    {
        try {
            $groupId = env('FONNTE_ID_GROUP_UPAH_BORONGAN');
            if (!$groupId) return;

            $items = PembangunanUnitUpahPengajuan::with(['pembangunanUnit.unit.tahap.perumahaan', 'pembangunanUnitQc'])
                ->whereIn('id', $ids)
                ->get();

            if ($items->isEmpty()) return;

            $msg = view('notifications.whatsapp.pembangunan_unit.persetujuan_upah', [
                'statusAction' => 'konfirmasi',
                'isApprove' => $isApprove,
                'items' => $items,
                'penyetuju' => auth()->user()->nama_lengkap ?? auth()->user()->name ?? 'Manager',
                'rolePenyetuju' => $roleName,
                'tanggal' => now()->format('d/m/Y H:i') . ' WIB'
            ])->render();

            app(\App\Services\NotificationGroupService::class)->send($groupId, $msg);
        } catch (\Exception $ex) {
            \Illuminate\Support\Facades\Log::error('WA Upah Exception: ' . $ex->getMessage());
        }
    }
}
