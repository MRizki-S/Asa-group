<?php

namespace App\Http\Controllers\Produksi\PembangunanUnit;

use App\Http\Controllers\Controller;
use App\Models\PembangunanUnitUpahPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PembangunanUnitPengajuanUpahController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'pembangunan_unit_id' => 'required',
            'pembangunan_unit_qc_id' => 'required',
            'items' => 'required|array|min:1',
            'items.*.nominal_pengajuan' => 'required|numeric|min:1',
            'items.*.catatan_pengawas' => 'nullable|string',
        ]);

        $unit = \App\Models\PembangunanUnit::findOrFail($request->pembangunan_unit_id);
        if (in_array($unit->status_pembangunan, ['selesai', 'selesai dengan catatan'])) {
            return response()->json(['message' => 'Unit ini sudah selesai dibangun, tidak dapat mengajukan upah lagi.'], 403);
        }

        try {
            DB::beginTransaction();

            foreach ($request->items as $item) {
                $rapUpah = \App\Models\PembangunanUnitRapUpah::findOrFail($item['pembangunan_unit_rap_upah_id']);

                // Calculate cumulative wages already requested (excluding rejected)
                $alreadyRequested = PembangunanUnitUpahPengajuan::where('pembangunan_unit_rap_upah_id', $rapUpah->id)
                    ->whereNull('ditolak_pada')
                    ->sum('nominal_diajukan');

                $newNominal = (float) $item['nominal_pengajuan'];
                $limit = (float) $rapUpah->nominal_standar;

                if (($alreadyRequested + $newNominal) > ($limit + 0.01)) {
                    if (empty($item['catatan_pengawas'])) {
                        throw new \Exception("Pengajuan upah {$rapUpah->nama_upah} melebihi RAP. Harap masukkan catatan pengawas.");
                    }
                }

                $tglPrefix = 'UBT-' . now()->format('ymd') . '-';
                $lastItem = PembangunanUnitUpahPengajuan::where('nomor_pengajuan', 'like', $tglPrefix . '%')
                    ->orderBy('id', 'desc')
                    ->first();
                $seq = 1;
                if ($lastItem && $lastItem->nomor_pengajuan) {
                    $lastSeq = (int) substr($lastItem->nomor_pengajuan, strlen($tglPrefix));
                    $seq = $lastSeq + 1;
                }
                $nomorPengajuan = $tglPrefix . str_pad($seq, 4, '0', STR_PAD_LEFT);

                PembangunanUnitUpahPengajuan::create([
                    'nomor_pengajuan' => $nomorPengajuan,
                    'pembangunan_unit_id' => $request->pembangunan_unit_id,
                    'pembangunan_unit_qc_id' => $request->pembangunan_unit_qc_id,
                    'pembangunan_unit_rap_upah_id' => $item['pembangunan_unit_rap_upah_id'],
                    'nama_upah' => $item['nama_upah'],
                    'nominal_diajukan' => $newNominal,
                    'catatan_pengawas' => $item['catatan_pengawas'] ?? null,
                    'tanggal_diajukan' => now(),
                ]);
            }

            DB::commit();

            // Send WA Notification for new upah pengajuan
            try {
                $firstPengajuanId = PembangunanUnitUpahPengajuan::where('pembangunan_unit_id', $request->pembangunan_unit_id)->latest()->value('id');
                $pengajuanSample = PembangunanUnitUpahPengajuan::with(['pembangunanUnit.unit.tahap.perumahaan', 'pembangunanUnitQc'])->find($firstPengajuanId);
                if ($pengajuanSample) {
                    $groupId = env('FONNTE_ID_GROUP_PENGAJUAN_UPAH_UNIT', env('FONNTE_ID_GROUP_PERSETUJUAN_UPAH_UNIT', env('FONNTE_ID_GROUP_KONFIRMASI_PEMBANGUNAN')));
                    if ($groupId) {
                        $unit = $pengajuanSample->pembangunanUnit->unit;
                        $msg = view('notifications.whatsapp.pembangunan_unit.persetujuan_upah', [
                            'statusAction' => 'pengajuan',
                            'pengajuan' => $pengajuanSample,
                            'namaPerumahan' => $unit->tahap->perumahaan->nama_perumahaan ?? '-',
                            'namaTahap' => $unit->tahap->nama_tahap ?? '-',
                            'namaUnit' => $unit->nama_unit ?? '-',
                            'namaQc' => $pengajuanSample->pembangunanUnitQc->nama_qc ?? '-',
                            'pengaju' => Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'Pengawas',
                            'tanggal' => now()->format('d/m/Y H:i') . ' WIB'
                        ])->render();
                        app(\App\Services\NotificationGroupService::class)->send($groupId, $msg);
                    }
                }
            } catch (\Exception $ex) {
            }

            return response()->json(['message' => 'Pengajuan upah berhasil dikirim.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $upah = PembangunanUnitUpahPengajuan::findOrFail($id);

        if (!is_null($upah->disetujui_mgr_produksi) || !is_null($upah->disetujui_mgr_dukungan) || !is_null($upah->disetujui_akuntan) || !is_null($upah->ditolak_pada)) {
            return redirect()->back()->with('error', 'Gagal membatalkan pengajuan upah! Data sudah diproses.');
        }

        try {
            $upah->delete();
            return redirect()->back()->with('success', 'Pengajuan upah berhasil dibatalkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
