<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\PembangunanUnitQc;
use App\Models\PembangunanUnitQcTask;
use App\Models\PembangunanUnitRapBahan;
use App\Models\PembangunanUnitRapUpah;
use App\Models\PengajuanPembangunanUnit;
use App\Services\NotificationGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KonfirmasiPembangunanController extends Controller
{
    protected NotificationGroupService $notificationGroup;

    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }

    public function sendAcceptNotification($pembangunan)
    {
        $pembangunan->load(['unit.tahap.perumahaan', 'pengawas']);

        $unit = $pembangunan->unit;
        $namaPerumahan = $unit->tahap->perumahaan->nama_perumahaan ?? '-';

        $groupId = "Ada Grup sendiri";

        $messageGroup = "✅ *PEMBANGUNAN UNIT DIMULAI*\n\n" . "Kabar baik! Pengajuan pembangunan unit berikut telah disetujui dan statusnya kini *Dalam Proses Pembangunan*.\n\n" . "```\n" . "📍 Perumahan : {$namaPerumahan}\n" . '🏠 Tahap     : ' . ($unit->tahap->nama_tahap ?? '-') . "\n" . '🔑 Unit      : ' . ($unit->nama_unit ?? '-') . "\n" . '👷 Pengawas  : ' . ($pembangunan->pengawas->nama_lengkap ?? '-') . "\n" . '📅 Estimasi  : ' . \Carbon\Carbon::parse($pembangunan->tanggal_mulai)->format('d/m/Y') . ' s/d ' . \Carbon\Carbon::parse($pembangunan->tanggal_selesai)->format('d/m/Y') . "\n" . "```\n\n" . 'Instruksi kerja telah diteruskan ke Pengawas terkait. Semangat untuk tim lapangan! 🏗️✨';

        if ($groupId) {
            try {
                $this->notificationGroup->send($groupId, $messageGroup);
            } catch (\Exception $e) {
            }
        }
    }

    public function konfirmasi(Request $request)
    {
        $validated = $request->validate([
            'pengawas_id' => 'required|integer|exists:users,id',
            'qc_container_id' => 'required|exists:master_qc_container,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'pengajuan_id' => 'required|integer|exists:pengajuan_pembangunan_unit,id',
        ]);

        try {
            DB::beginTransaction();

            // Update Pengajuan
            $pengajuan = PengajuanPembangunanUnit::findOrFail($validated['pengajuan_id']);

            $pengajuan->update([
                'direspon_oleh' => Auth::user()->id,
                'status_pengajuan' => 'dibangun',
                'tanggal_direspon' => now(),
            ]);

            // Update Pembangunan
            $pembangunan = $pengajuan->pembangunanUnit;
            $pembangunan->update([
                'pengawas_id' => $validated['pengawas_id'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'qc_container_id' => $validated['qc_container_id'],
                'status_pembangunan' => 'proses',
            ]);

            $qcUrutan = $pembangunan->qcContainer->urutan;

            foreach ($qcUrutan as $key => $urutan) {
                $pembangunanUnitQc = PembangunanUnitQc::create([
                    'pembangunan_unit_id' => $pembangunan->id,
                    'master_qc_urutan_id' => $urutan->id,
                    'qc_urutan_ke' => $urutan->qc_ke,
                    'nama_qc' => $urutan->nama_qc,
                    'tanggal_mulai' => $key == 0 ? now() : null,
                    'tanggal_selesai' => null,
                ]);

                foreach ($urutan->tugas as $key => $task) {
                    PembangunanUnitQcTask::create([
                        'pembangunan_unit_qc_id' => $pembangunanUnitQc->id,
                        'tugas' => $task->tugas,
                        'selesai' => false,
                    ]);
                }

                foreach ($urutan->rapBahan as $key => $bahan) {
                    PembangunanUnitRapBahan::create([
                        'pembangunan_unit_id' => $pembangunan->id,
                        'pembangunan_unit_qc_id' => $pembangunanUnitQc->id,
                        'master_rap_bahan_id' => $bahan->id,
                        'barang_id' => $bahan->master_barang_id,
                        'nama_barang' => $bahan->barang->nama_barang,
                        'satuan_id' => $bahan->satuan->id,
                        'satuan' => $bahan->satuan->nama,
                        'jumlah_standar' => $bahan->jumlah_kebutuhan_standar,
                    ]);
                }

                foreach ($urutan->rapUpah as $key => $upah) {
                    PembangunanUnitRapUpah::create([
                        'pembangunan_unit_id' => $pembangunan->id,
                        'pembangunan_unit_qc_id' => $pembangunanUnitQc->id,
                        'master_rap_upah_id' => $upah->id,
                        'nama_upah' => $upah->masterUpah->nama_upah,
                        'nominal_standar' => $upah->nominal_standar,
                    ]);
                }
            }

            DB::commit();

            // $this->sendAcceptNotification($pembangunan);

            return redirect()->route('produksi.pembangunanUnit.index')->with('success', 'Data Pengajuan Pembangunan Unit berhasil diassign!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }
}
