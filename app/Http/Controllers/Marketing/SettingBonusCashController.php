<?php
namespace App\Http\Controllers\Marketing;

use Illuminate\Http\Request;
use App\Models\PpjbBonusCashItem;
use App\Models\PpjbBonusCashBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationGroupService;

class SettingBonusCashController extends Controller
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
     * Menampilkan halaman Bonus Cash PPJB
     */
    public function edit()
    {
        $perumahaanId = $this->currentPerumahaanId();

        // Bonus Cash Aktif (ACC + Aktif)
        $bonusCashActive = PpjbBonusCashBatch::with(['items', 'penyetuju'])
            ->where('status_aktif', true)
            ->where('status_pengajuan', 'acc')
            ->where('perumahaan_id', $perumahaanId)
            ->first();

        // Bonus Cash Pending
        $bonusCashPending = PpjbBonusCashBatch::with(['items', 'pengaju'])
            ->where('status_pengajuan', 'pending')
            ->where('perumahaan_id', $perumahaanId)
            ->first();

        return view('marketing.setting.bonus-cash-kelola', [
            'bonusCashActive' => $bonusCashActive,
            'bonusCashPending' => $bonusCashPending,
            'breadcrumbs' => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Bonus Cash PPJB', 'url' => ''],
            ],
        ]);
    }

    // pengajuan bonus cash baru
    public function pengajuanUpdate(Request $request)
    {
        // dd($request->    all());
        $request->validate([
            'nama_bonus' => 'required|array|min:1',
            'nama_bonus.*' => 'required|string|max:255',
        ]);

        $perumahaanId = $this->currentPerumahaanId();

        // cek jika ada batch pending sebelumnya
        $pendingBatch = PpjbBonusCashBatch::where('status_pengajuan', 'pending')
            ->where('perumahaan_id', $perumahaanId)
            ->first();

        if ($pendingBatch) {
            return redirect()
                ->back()
                ->with('error', 'Pengajuan Bonus Cash gagal. Harap tunggu batch pending sebelumnya disetujui atau ditolak.');
        }

        // buat batch baru
        $batch = PpjbBonusCashBatch::create([
            'perumahaan_id' => $perumahaanId,
            'status_aktif' => false,
            'status_pengajuan' => 'pending',
            'diajukan_oleh' => Auth::id(),
            'tanggal_pengajuan' => now(),
        ]);

        // buat items Bonus
        foreach ($request->nama_bonus as $index => $namaBonus) {
            PpjbBonusCashItem::create([
                'batch_id' => $batch->id,
                'nama_bonus' => $namaBonus,
            ]);
        }

        // Load relasi yang dibutuhkan untuk notifikasi
        $batch->load(['items', 'perumahaan']);

        // List Bonus Cash
        $listBonus = $batch->items
            ->pluck('nama_bonus')
            ->map(fn($bonus) => "• {$bonus}")
            ->implode("\n");

        // Kirim Notifikasi WA Group
        $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

        $message =
            "🔔 Pengajuan BONUS CASH\n" .
            "```\n" .
            "Perumahaan   : {$batch->perumahaan->nama_perumahaan}\n" .
            "Diajukan oleh: " . Auth::user()->nama_lengkap . "\n" .
            "Status       : Pending\n" .
            "```\n" .
            "📋 Daftar Bonus Cash:\n" .
            "{$listBonus}\n\n" .
            "⏳ Menunggu persetujuan";

        // Kirim Notifikasi WA Group
        try {
            $this->notificationGroup->send($groupId, $message);
        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi pengajuan bonus cash', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Pengajuan Bonus Cash PPJB baru berhasil diajukan dan menunggu persetujuan.');
    }

    // nonaktifkan Bonus Cash yang aktif
    public function nonAktif(PpjbBonusCashBatch $batch)
    {
        if (!$batch->status_aktif) {
            return redirect()->back()
                ->with('error', 'Hanya Bonus Cash aktif yang bisa dinonaktifkan.');
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
                "🔕 Bonus CASH Telah Dinonaktifkan\n" .
                "```\n" .
                "Perumahaan   : {$namaPerumahan}\n" .
                "Dinonaktifkan oleh: " . Auth::user()->nama_lengkap . "\n" .
                "Status       : Tidak Aktif\n" .
                "```\n" .
                "📋 Daftar Bonus CASH:\n" .
                "{$listBonus}\n\n" .
                "⛔ Bonus CASH ini sudah tidak aktif dan tidak dapat digunakan";

            $this->notificationGroup->send($groupId, $message);
        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi nonaktif bonus cash', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Bonus Cash berhasil dinonaktifkan.');
    }


    // cancel pengajuan bonus cash
    public function cancelPengajuan(PpjbBonusCashBatch $batch)
    {
        if ($batch->status_pengajuan !== 'pending') {
            return redirect()->back()
                ->with('error', 'Hanya pengajuan Bonus Cash dengan status pending yang bisa dibatalkan.');
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
                "🚫 Pembatalan Pengajuan Bonus CASH\n" .
                "```\n" .
                "Perumahaan   : {$namaPerumahan}\n" .
                "Dibatalkan oleh: " . Auth::user()->nama_lengkap . "\n" .
                "Status       : Dibatalkan\n" .
                "```\n" .
                "📋 Daftar Bonus Cash:\n" .
                "{$listBonus}\n\n" .
                "❌ Pengajuan Bonus Cash telah dibatalkan";

            $this->notificationGroup->send($groupId, $message);
        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi pembatalan bonus cash', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Pengajuan Bonus Cash berhasil dibatalkan.');
    }



    // Bonus Cash history nonaktif dan tolak
    public function history()
    {
        $perumahaanId = $this->currentPerumahaanId();

        // 1. Batch ACC tapi nonaktif
        $nonAktif = PpjbBonusCashBatch::where('status_pengajuan', 'acc')
            ->where('status_aktif', false)
            ->where('perumahaan_id', $perumahaanId)
            ->with(['items', 'penyetuju', 'pengaju'])
            ->latest()
            ->take(10)
            ->get();

        // 2. Batch yang ditolak
        $ditolak = PpjbBonusCashBatch::where('status_pengajuan', 'tolak')
            ->where('perumahaan_id', $perumahaanId)
            ->with(['items', 'penyetuju', 'pengaju'])
            ->latest()
            ->take(10)
            ->get();

        $editRoute = route('settingPPJB.bonusCash.edit');

        return view('marketing.setting.bonus-cash-history', [
            'nonAktif' => $nonAktif,
            'ditolak' => $ditolak,
            'editRoute' => $editRoute,
            'breadcrumbs' => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Bonus Cash PPJB', 'url' => $editRoute],
                ['label' => 'Riwayat Bonus Cash', 'url' => ''],
            ],
        ]);
    }

    // Manager Keuangan aksi untuk approve dan tolak pengajuan Bonus Cash baru
    public function approvePengajuan(PpjbBonusCashBatch $bonusCash)
    {
        try {
            // TRANSAKSI INTI
            DB::transaction(function () use ($bonusCash) {

                // Validasi status
                if ($bonusCash->status_pengajuan !== 'pending') {
                    throw new \Exception(
                        'Hanya pengajuan Bonus Cash dengan status pending yang bisa disetujui.'
                    );
                }

                // Nonaktifkan Bonus Cash aktif sebelumnya
                PpjbBonusCashBatch::where('perumahaan_id', $bonusCash->perumahaan_id)
                    ->where('status_aktif', true)
                    ->update(['status_aktif' => false]);

                // Aktifkan Bonus Cash ini
                $bonusCash->update([
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
            $bonusCash->load(['items', 'perumahaan']);

            $listBonus = $bonusCash->items->isEmpty()
                ? '- (Tidak ada bonus)'
                : $bonusCash->items
                    ->pluck('nama_bonus')
                    ->map(fn($bonus) => "• {$bonus}")
                    ->implode("\n");

            $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

            $message =
                "✅ Persetujuan BONUS CASH\n" .
                "```\n" .
                "Perumahaan   : {$bonusCash->perumahaan->nama_perumahaan}\n" .
                "Disetujui oleh: " . Auth::user()->nama_lengkap . "\n" .
                "Status       : Aktif\n" .
                "```\n" .
                "📋 Daftar Bonus Cash:\n" .
                "{$listBonus}\n\n" .
                "🔄 Bonus Cash sebelumnya dinonaktifkan & Bonus Cash ini resmi AKTIF";

            $this->notificationGroup->send($groupId, $message);

        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi ACC Bonus Cash', [
                'batch_id' => $bonusCash->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Pengajuan Bonus Cash berhasil disetujui dan diaktifkan.');
    }


    public function rejectPengajuan(PpjbBonusCashBatch $bonusCash)
    {
        try {
            // TRANSAKSI INTI
            DB::transaction(function () use ($bonusCash) {

                // Validasi status
                if ($bonusCash->status_pengajuan !== 'pending') {
                    throw new \Exception(
                        'Hanya pengajuan Bonus Cash dengan status pending yang bisa ditolak.'
                    );
                }

                // Update status menjadi ditolak
                $bonusCash->update([
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
            $bonusCash->load(['items', 'perumahaan']);

            $listBonus = $bonusCash->items->isEmpty()
                ? '- (Tidak ada bonus)'
                : $bonusCash->items
                    ->pluck('nama_bonus')
                    ->map(fn($bonus) => "• {$bonus}")
                    ->implode("\n");

            $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

            $message =
                "❌ Penolakan BONUS CASH\n" .
                "```\n" .
                "Perumahaan   : {$bonusCash->perumahaan->nama_perumahaan}\n" .
                "Ditolak oleh : " . Auth::user()->nama_lengkap . "\n" .
                "Status       : Ditolak\n" .
                "```\n" .
                "📋 Daftar Bonus Cash:\n" .
                "{$listBonus}\n\n" .
                "🚫 Pengajuan Bonus Cash tidak disetujui";

            $this->notificationGroup->send($groupId, $message);

        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi penolakan Bonus Cash', [
                'batch_id' => $bonusCash->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Pengajuan Bonus Cash berhasil ditolak.');
    }

}
