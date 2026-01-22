<?php
namespace App\Http\Controllers\Marketing;

use Illuminate\Http\Request;
use App\Models\PpjbKeterlambatan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationGroupService;

class SettingKeterlambatanController extends Controller
{

    protected NotificationGroupService $notificationGroup;

    // Notifikasi Group
    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }

    public function editKeterlambatan()
    {
        $user = Auth::user();

        // Tentukan perumahaan saat ini
        $currentPerumahaanId = $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;

        // Ambil Keterlambatan aktif
        $keterlambatanActive = PpjbKeterlambatan::with(['pengaju', 'approver'])
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('status_aktif', 1)
            ->first();

        // Ambil pending (pengajuan)
        $keterlambatanPending = PpjbKeterlambatan::with(['pengaju'])
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('status_pengajuan', 1)
            ->where('status_aktif', 0)
            ->first();

        return view('marketing.setting.keterlambatan-kelola', [
            'keterlambatanActive' => $keterlambatanActive,
            'keterlambatanPending' => $keterlambatanPending,
            'breadcrumbs' => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Kelola Keterlambatan', 'url' => route('settingPPJB.keterlambatan.edit')],
            ],
        ]);
    }

    public function updatePengajuan(Request $request)
    {
        $request->validate([
            'perumahaan_id' => 'required|integer',
            'persentase_denda' => 'required|integer|min:0',
        ]);

        $perumahaanId = $request->perumahaan_id;

        // cek jika ada pengajuan pending sebelumnya
        $pendingCaraBayar = PpjbKeterlambatan::where('perumahaan_id', $perumahaanId)
            ->where('status_pengajuan', 'pending')
            ->first();

        if ($pendingCaraBayar) {
            return redirect()
                ->back()
                ->with('error', 'Pengajuan Keterlambatan gagal. Harap tunggu pengajuan pending sebelumnya disetujui atau ditolak.');
        }

        // buat pengajuan baru
        $keterlambatan = PpjbKeterlambatan::create([
            'perumahaan_id' => $perumahaanId,
            'persentase_denda' => $request->persentase_denda,
            'status_aktif' => 0,
            'status_pengajuan' => 'pending',
            'diajukan_oleh' => Auth::id(),
            'disetujui_oleh' => null,
        ]);

        // NOTIFIKASI WA (TAMBAHAN)
        try {
            $keterlambatan->load('perumahaan');

            $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

            $message =
                "⏳ Pengajuan Keterlambatan\n" .
                "```\n" .
                "Perumahaan    : {$keterlambatan->perumahaan->nama_perumahaan}\n" .
                "Persentase    : {$keterlambatan->persentase_denda} %\n" .
                "Diajukan oleh : " . Auth::user()->nama_lengkap . "\n" .
                "Status        : Menunggu Persetujuan\n" .
                "```\n" .
                "📌 Pengajuan denda keterlambatan baru menunggu persetujuan";

            $this->notificationGroup->send($groupId, $message);

        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi pengajuan keterlambatan', [
                'keterlambatan_id' => $keterlambatan->id,
                'perumahaan_id' => $perumahaanId,
                'error' => $e->getMessage(),
            ]);
        }


        return redirect()
            ->back()
            ->with('success', 'Pengajuan Keterlambaran baru berhasil diajukan dan menunggu persetujuan.');
    }

    // nonaktifkan Keterlambatan yang aktif
    public function nonAktifKeterlambatan(PpjbKeterlambatan $keterlambatan)
    {
        if (!$keterlambatan->status_aktif) {
            return redirect()->back()
                ->with('error', 'Hanya Keterlambatan aktif yang bisa dinonaktifkan.');
        }

        // update status
        $keterlambatan->update(['status_aktif' => false]);

        // NOTIFIKASI WA
        try {
            $keterlambatan->load('perumahaan');

            $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

            $message =
                "⛔ Penonaktifan Keterlambatan\n" .
                "```\n" .
                "Perumahaan    : {$keterlambatan->perumahaan->nama_perumahaan}\n" .
                "Persentase    : {$keterlambatan->persentase_denda} %\n" .
                "Dinonaktifkan oleh : " . Auth::user()->nama_lengkap . "\n" .
                "Status        : Nonaktif\n" .
                "```\n" .
                "🔕 Aturan denda keterlambatan telah dinonaktifkan";

            $this->notificationGroup->send($groupId, $message);

        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi nonaktif keterlambatan', [
                'keterlambatan_id' => $keterlambatan->id,
                'perumahaan_id' => $keterlambatan->perumahaan_id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Keterlambatan berhasil dinonaktifkan.');
    }


    /**
     * Batalkan Pengajuan Keterlambatan Pending
     */
    public function cancelPengajuanKeterlambatan(PpjbKeterlambatan $keterlambatan)
    {
        if ($keterlambatan->status_pengajuan !== 'pending') {
            return redirect()->back()
                ->with('error', 'Hanya pengajuan Keterlambatan dengan status pending yang bisa dibatalkan.');
        }

        // simpan data sebelum delete (buat notifikasi)
        $keterlambatan->load('perumahaan');

        $namaPerumahan = $keterlambatan->perumahaan->nama_perumahaan;
        $persentaseDenda = $keterlambatan->persentase_denda;

        // karena pending & belum dipakai → langsung hapus
        $keterlambatan->delete();

        // NOTIFIKASI WA
        try {
            $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

            $message =
                "🚫 Pembatalan Pengajuan Keterlambatan\n" .
                "```\n" .
                "Perumahaan    : {$namaPerumahan}\n" .
                "Persentase    : {$persentaseDenda} %\n" .
                "Dibatalkan oleh : " . Auth::user()->nama_lengkap . "\n" .
                "Status        : Dibatalkan\n" .
                "```\n" .
                "❌ Pengajuan Keterlambatan telah dibatalkan";

            $this->notificationGroup->send($groupId, $message);

        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi pembatalan keterlambatan', [
                'keterlambatan_id' => $keterlambatan->id ?? null,
                'perumahaan' => $namaPerumahan,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Pengajuan Keterlambatan berhasil dibatalkan.');
    }


    // Approve Pengajuan Keterlambatan
    public function approvePengajuan(PpjbKeterlambatan $keterlambatan)
    {
        try {
            DB::transaction(function () use ($keterlambatan) {
                // Validasi status
                if ($keterlambatan->status_pengajuan !== 'pending') {
                    throw new \Exception('Hanya pengajuan Keterlambatan dengan status pending yang bisa disetujui.');
                }

                // Nonaktifkan semua Keterlambatan aktif sebelumnya
                PpjbKeterlambatan::where('perumahaan_id', $keterlambatan->perumahaan_id)
                    ->where('status_aktif', operator: 1)
                    ->update(['status_aktif' => 0])
                ;
                // Set pengajuan ini menjadi aktif
                $keterlambatan->update([
                    'status_aktif' => 1,
                    'status_pengajuan' => 'acc',
                    'disetujui_oleh' => Auth::id(),
                ]);
            });

            //  NOTIFIKASI WA
            try {
                $keterlambatan->load('perumahaan');

                // format persen (hapus .00)
                $persen = rtrim(rtrim(
                    number_format($keterlambatan->persentase_denda, 2, '.', ''),
                    '0'
                ), '.');

                $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

                $message =
                    "✅ Persetujuan KETERLAMBATAN\n" .
                    "```\n" .
                    "Perumahaan   : {$keterlambatan->perumahaan->nama_perumahaan}\n" .
                    "Denda        : {$persen} %\n" .
                    "Disetujui oleh: " . Auth::user()->nama_lengkap . "\n" .
                    "Status       : Aktif\n" .
                    "```\n" .
                    "🔄 Aturan keterlambatan sebelumnya dinonaktifkan\n" .
                    "⚠️ Aturan keterlambatan ini resmi BERLAKU";

                $this->notificationGroup->send($groupId, $message);

            } catch (\Throwable $e) {
                Log::error('Gagal kirim notifikasi ACC keterlambatan', [
                    'keterlambatan_id' => $keterlambatan->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return redirect()->back()->with('success', 'Pengajuan Keterlambatan berhasil disetujui dan diaktifkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // tolak pengajuan Keterlambatan
    public function rejectPengajuan(PpjbKeterlambatan $keterlambatan)
    {
        // 🔹 Cek apakah statusnya masih pending
        if ($keterlambatan->status_pengajuan !== 'pending') {
            return redirect()->back()->with('error', 'Hanya pengajuan Keterlambatan dengan status pending yang bisa ditolak.');
        }

        $keterlambatan->update([
            'status_pengajuan' => 'tolak',
        ]);

        try {
            $keterlambatan->load('perumahaan');

            // format persen (hapus .00)
            $persen = rtrim(rtrim(
                number_format($keterlambatan->persentase_denda, 2, '.', ''),
                '0'
            ), '.');

            $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

            $message =
                "❌ Penolakan Pengajuan KETERLAMBATAN\n" .
                "```\n" .
                "Perumahaan   : {$keterlambatan->perumahaan->nama_perumahaan}\n" .
                "Denda        : {$persen} %\n" .
                "Ditolak oleh : " . Auth::user()->nama_lengkap . "\n" .
                "Status       : Ditolak\n" .
                "```\n" .
                "🚫 Pengajuan aturan keterlambatan tidak disetujui";

            $this->notificationGroup->send($groupId, $message);

        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi penolakan keterlambatan', [
                'keterlambatan_id' => $keterlambatan->id,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Pengajuan Keterlambatan berhasil ditolak.');
    }
}
