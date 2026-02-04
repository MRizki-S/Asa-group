<?php

namespace App\Http\Controllers\Marketing;

use Illuminate\Http\Request;
use App\Models\PpjbBonusKprItem;
use App\Models\PpjbBonusKprBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationGroupService;

class SettingBonusKprController extends Controller
{
    // Service Notifikasi To Wa Group
    protected NotificationGroupService $notificationGroup;

    // Notifikasi Group
    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }

    /**
     * Mendapatkan current perumahaan id
     */
    protected function currentPerumahaanId()
    {
        $user = Auth::user();
        return $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;
    }
    /**
     * Menampilkan halaman Bonus KPR PPJB
     */
    public function edit()
    {
        $perumahaanId = $this->currentPerumahaanId();

        // Bonus KPR Aktif (ACC + Aktif)
        $bonusKprActive = PpjbBonusKprBatch::with(['items', 'penyetuju'])
            ->where('status_aktif', true)
            ->where('status_pengajuan', 'acc')
            ->where('perumahaan_id', $perumahaanId)
            ->first();

        // Bonus KPR Pending
        $bonusKprPending = PpjbBonusKprBatch::with(['items', 'pengaju'])
            ->where('status_pengajuan', 'pending')
            ->where('perumahaan_id', $perumahaanId)
            ->first();

        // dd( $bonusKprActive,$bonusKprPending);
        return view('marketing.setting.bonus-kpr-kelola', [
            'bonusKprActive' => $bonusKprActive,
            'bonusKprPending' => $bonusKprPending,
            'breadcrumbs' => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Bonus KPR PPJB', 'url' => ''],
            ],
        ]);
    }

    // pengajuan bonus KPR baru
    public function pengajuanUpdate(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'nama_bonus' => 'required|array|min:1',
            'nama_bonus.*' => 'required|string|max:255',
        ]);

        $perumahaanId = $this->currentPerumahaanId();

        // cek jika ada batch pending sebelumnya
        $pendingBatch = PpjbBonusKPRBatch::where('status_pengajuan', 'pending')
            ->where('perumahaan_id', $perumahaanId)
            ->first();

        if ($pendingBatch) {
            return redirect()
                ->back()
                ->with('error', 'Pengajuan Bonus KPR gagal. Harap tunggu batch pending sebelumnya disetujui atau ditolak.');
        }

        // buat batch baru
        $batch = PpjbBonusKprBatch::create([
            'perumahaan_id' => $perumahaanId,
            'status_aktif' => false,
            'status_pengajuan' => 'pending',
            'diajukan_oleh' => Auth::id(),
            'tanggal_pengajuan' => now(),
        ]);

        // buat items Bonus
        foreach ($request->nama_bonus as $index => $namaBonus) {
            PpjbBonusKprItem::create([
                'batch_id' => $batch->id,
                'nama_bonus' => $namaBonus,
            ]);
        }

        // Load relasi yang dibutuhkan untuk notifikasi
        $batch->load(['items', 'perumahaan']);

        // List Bonus KPR
        $listBonus = $batch->items
            ->pluck('nama_bonus')
            ->map(fn($bonus) => "• {$bonus}")
            ->implode("\n");

        // Kirim Notifikasi WA Group
        $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

        $message =
            "🔔 Pengajuan BONUS KPR\n" .
            "```\n" .
            "Perumahaan   : {$batch->perumahaan->nama_perumahaan}\n" .
            "Diajukan oleh: " . Auth::user()->nama_lengkap . "\n" .
            "Status       : Pending\n" .
            "```\n" .
            "📋 Daftar Bonus KPR:\n" .
            "{$listBonus}\n\n" .
            "⏳ Menunggu persetujuan";

        // Kirim Notifikasi WA Group
        try {
            // $this->notificationGroup->send($groupId, $message);
        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi pengajuan bonus Kpr', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Pengajuan Bonus KPR PPJB baru berhasil diajukan dan menunggu persetujuan.');
    }

    // nonaktifkan Bonus KPR yang aktif
    public function nonAktif(PpjbBonusKprBatch $batch)
    {
        if (!$batch->status_aktif) {
            return redirect()->back()
                ->with('error', 'Hanya Bonus KPR aktif yang bisa dinonaktifkan.');
        }

        // SNAPSHOT DATA
        $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');
        $namaPerumahan = $batch->perumahaan->nama_perumahaan;

        $listBonus = $batch->items
            ->pluck('nama_bonus')
            ->map(fn($bonus) => "• {$bonus}")
            ->implode("\n");

        // AKSI UTAMA
        $batch->update(['status_aktif' => false]);

        // NOTIFIKASI (TAMBAHAN)
        try {
            $message =
                "🔕 Bonus KPR Telah Dinonaktifkan\n" .
                "```\n" .
                "Perumahaan   : {$namaPerumahan}\n" .
                "Dinonaktifkan oleh: " . Auth::user()->nama_lengkap . "\n" .
                "Status       : Tidak Aktif\n" .
                "```\n" .
                "📋 Daftar Bonus KPR:\n" .
                "{$listBonus}\n\n" .
                "⛔ Bonus KPR ini sudah tidak aktif dan tidak dapat digunakan";

            // $this->notificationGroup->send($groupId, $message);
        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi nonaktif bonus kpr', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Bonus KPR berhasil dinonaktifkan.');
    }

    // cancel pengajuan bonus KPR
    public function cancelPengajuan(PpjbBonusKprBatch $batch)
    {
        if ($batch->status_pengajuan !== 'pending') {
            return redirect()->back()
                ->with('error', 'Hanya pengajuan Bonus KPR dengan status pending yang bisa dibatalkan.');
        }

        // SNAPSHOT DATA
        $batch->load('items', 'perumahaan');

        $namaPerumahan = $batch->perumahaan->nama_perumahaan;

        $listBonus = $batch->items->isEmpty()
            ? '- (Tidak ada bonus)'
            : $batch->items
            ->map(fn($item) => "• {$item->nama_bonus}")
            ->implode("\n");

        // AKSI UTAMA (WAJIB JALAN)`
        $batch->delete();

        // NOTIFIKASI (TAMBAHAN)
        try {
            $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

            $message =
                "🚫 Pembatalan Pengajuan Bonus KPR\n" .
                "```\n" .
                "Perumahaan   : {$namaPerumahan}\n" .
                "Dibatalkan oleh: " . Auth::user()->nama_lengkap . "\n" .
                "Status       : Dibatalkan\n" .
                "```\n" .
                "📋 Daftar Bonus KPR:\n" .
                "{$listBonus}\n\n" .
                "❌ Pengajuan Bonus KPR telah dibatalkan";

            // $this->notificationGroup->send($groupId, $message);
        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi pembatalan bonus KPR', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Pengajuan Bonus KPR berhasil dibatalkan.');
    }


    // Bonus KPR history nonaktif dan tolak
    public function history()
    {
        $perumahaanId = $this->currentPerumahaanId();

        // 1. Batch ACC tapi nonaktif
        $nonAktif = PpjbBonusKprBatch::where('status_pengajuan', 'acc')
            ->where('status_aktif', false)
            ->where('perumahaan_id', $perumahaanId)
            ->with(['items', 'penyetuju', 'pengaju'])
            ->latest()
            ->take(10)
            ->get();

        // 2. Batch yang ditolak
        $ditolak = PpjbBonusKprBatch::where('status_pengajuan', 'tolak')
            ->where('perumahaan_id', $perumahaanId)
            ->with(['items', 'penyetuju', 'pengaju'])
            ->latest()
            ->take(10)
            ->get();

        $editRoute = route('settingPPJB.bonusKpr.edit');

        return view('marketing.setting.bonus-kpr-history', [
            'nonAktif' => $nonAktif,
            'ditolak' => $ditolak,
            'editRoute' => $editRoute,
            'breadcrumbs' => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Bonus KPR PPJB', 'url' => $editRoute],
                ['label' => 'Riwayat Bonus KPR', 'url' => ''],
            ],
        ]);
    }

    // Manager Keuangan aksi untuk approve dan tolak pengajuan Bonus KPR baru
    public function approvePengajuan(PpjbBonusKprBatch $bonusKpr)
    {
        try {
            // TRANSAKSI INTI
            DB::transaction(function () use ($bonusKpr) {

                // Validasi status
                if ($bonusKpr->status_pengajuan !== 'pending') {
                    throw new \Exception(
                        'Hanya pengajuan Bonus KPR dengan status pending yang bisa disetujui.'
                    );
                }

                // Nonaktifkan Bonus Kpr aktif sebelumnya
                PpjbBonusKprBatch::where('perumahaan_id', $bonusKpr->perumahaan_id)
                    ->where('status_aktif', true)
                    ->update(['status_aktif' => false]);

                // Aktifkan Bonus Kpr ini
                $bonusKpr->update([
                    'status_aktif' => true,
                    'status_pengajuan' => 'acc',
                    'disetujui_oleh' => Auth::id(),
                    'tanggal_acc' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            // GAGAL LOGIC / DB
            return redirect()->back()
                ->with('error', $e->getMessage());
        }

        // NOTIFIKASI
        try {
            $bonusKpr->load(['items', 'perumahaan']);

            $listBonus = $bonusKpr->items->isEmpty()
                ? '- (Tidak ada bonus)'
                : $bonusKpr->items
                ->pluck('nama_bonus')
                ->map(fn($bonus) => "• {$bonus}")
                ->implode("\n");

            $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

            $message =
                "✅ Persetujuan BONUS KPR\n" .
                "```\n" .
                "Perumahaan   : {$bonusKpr->perumahaan->nama_perumahaan}\n" .
                "Disetujui oleh: " . Auth::user()->nama_lengkap . "\n" .
                "Status       : Aktif\n" .
                "```\n" .
                "📋 Daftar Bonus KPR:\n" .
                "{$listBonus}\n\n" .
                "🔄 Bonus Kpr sebelumnya dinonaktifkan & Bonus Kpr ini resmi AKTIF";

            // $this->notificationGroup->send($groupId, $message);

        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi ACC Bonus KPR', [
                'batch_id' => $bonusKpr->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Pengajuan Bonus KPR berhasil disetujui dan diaktifkan.');
    }

    // Manager Keuangan aksi untuk tolak pengajuan Bonus Kpr baru
    public function rejectPengajuan(PpjbBonusKprBatch $bonusKpr)
    {
        try {
            // TRANSAKSI INTI
            DB::transaction(function () use ($bonusKpr) {

                // Validasi status
                if ($bonusKpr->status_pengajuan !== 'pending') {
                    throw new \Exception(
                        'Hanya pengajuan Bonus KPR dengan status pending yang bisa ditolak.'
                    );
                }

                // Update status menjadi ditolak
                $bonusKpr->update([
                    'status_pengajuan' => 'tolak',
                    'ditolak_oleh' => Auth::id(),
                    'tanggal_tolak' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }

        // NOTIFIKASI
        try {
            $bonusKpr->load(['items', 'perumahaan']);

            $listBonus = $bonusKpr->items->isEmpty()
                ? '- (Tidak ada bonus)'
                : $bonusKpr->items
                ->pluck('nama_bonus')
                ->map(fn($bonus) => "• {$bonus}")
                ->implode("\n");

            $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

            $message =
                "❌ Penolakan BONUS KPR\n" .
                "```\n" .
                "Perumahaan   : {$bonusKpr->perumahaan->nama_perumahaan}\n" .
                "Ditolak oleh : " . Auth::user()->nama_lengkap . "\n" .
                "Status       : Ditolak\n" .
                "```\n" .
                "📋 Daftar Bonus KPR:\n" .
                "{$listBonus}\n\n" .
                "🚫 Pengajuan Bonus KPR tidak disetujui";

            // $this->notificationGroup->send($groupId, $message);
        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi penolakan Bonus KPR', [
                'batch_id' => $bonusKpr->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Pengajuan Bonus KPR berhasil ditolak.');
    }
}
