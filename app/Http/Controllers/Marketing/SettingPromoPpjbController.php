<?php
namespace App\Http\Controllers\Marketing;

use Log;
use App\Models\Perumahaan;
use Illuminate\Http\Request;
use App\Models\PpjbPromoItem;
use App\Models\PpjbPromoBatch;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationGroupService;

class SettingPromoPpjbController extends Controller
{


    protected NotificationGroupService $notificationGroup;

    // Notifikasi Group
    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }

    /**
     * Helper untuk dapatkan perumahaan_id sesuai user
     */
    protected function currentPerumahaanId()
    {
        $user = Auth::user();
        return $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;
    }

    public function editCash()
    {
        $perumahaanId = $this->currentPerumahaanId();

        $promoCashActive = PpjbPromoBatch::with(['items', 'penyetuju'])
            ->where('tipe', 'cash')
            ->where('status_aktif', true)
            ->where('perumahaan_id', $perumahaanId)
            ->first();

        $promoCashPending = PpjbPromoBatch::with(['items', 'pengaju'])
            ->where('tipe', 'cash')
            ->where('status_pengajuan', 'pending')
            ->where('perumahaan_id', $perumahaanId)
            ->first();

        return view('marketing.setting.promo-cash-edit', [
            'promoCashActive' => $promoCashActive,
            'promoCashPending' => $promoCashPending,
            'breadcrumbs' => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Promo Cash - Kelola', 'url' => ''],
            ],
        ]);
    }

    public function updateCash(Request $request)
    {
        $request->validate([
            'nama_promo' => 'required|array|min:1',
            'nama_promo.*' => 'required|string|max:255',
        ]);

        $perumahaanId = $this->currentPerumahaanId();

        $pendingBatch = PpjbPromoBatch::where('tipe', 'cash')
            ->where('status_pengajuan', 'pending')
            ->where('perumahaan_id', $perumahaanId)
            ->first();

        if ($pendingBatch) {
            return redirect()
                ->route('settingPPJB.promoCash.edit')
                ->with('error', 'Pengajuan promo cash gagal. Harap tunggu batch pending sebelumnya disetujui atau ditolak.');
        }

        $batch = PpjbPromoBatch::create([
            'tipe' => 'cash',
            'status_aktif' => false,
            'status_pengajuan' => 'pending',
            'diajukan_oleh' => Auth::id(),
            'perumahaan_id' => $request->perumahaan_id ?? $perumahaanId,
            'tanggal_pengajuan' => now(),
        ]);

        foreach ($request->nama_promo as $promo) {
            PpjbPromoItem::create([
                'batch_id' => $batch->id,
                'nama_promo' => $promo,
            ]);
        }

        // Notifikasi group
        $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

        $listPromo = collect($request->nama_promo)
            ->map(callback: fn($promo) => "• {$promo}")
            ->implode("\n");

        $message =
            "🔔 Pengajuan Promo CASH\n" .
            "```\n" .
            "Perumahaan   : {$batch->perumahaan->nama_perumahaan}\n" .
            "Jenis Promo  : CASH\n" .
            "Diajukan oleh: " . Auth::user()->nama_lengkap . "\n" .
            "Status       : Pending\n" .
            "```\n" .
            "📋 Daftar Promo:\n" .
            "{$listPromo}\n\n" .
            "⏳ Menunggu persetujuan";

        $this->notificationGroup->send($groupId, $message);

        return redirect()
            ->route('settingPPJB.promoCash.edit')
            ->with('success', 'Pengajuan promo cash baru berhasil diajukan dan menunggu persetujuan.');
    }

    public function editKpr()
    {
        $perumahaanId = $this->currentPerumahaanId();

        $promoKprActive = PpjbPromoBatch::with(['items', 'penyetuju'])
            ->where('tipe', 'kpr')
            ->where('status_aktif', true)
            ->where('perumahaan_id', $perumahaanId)
            ->first();

        $promoKprPending = PpjbPromoBatch::with(['items', 'pengaju'])
            ->where('tipe', 'kpr')
            ->where('status_pengajuan', 'pending')
            ->where('perumahaan_id', $perumahaanId)
            ->first();

        return view('marketing.setting.promo-kpr-edit', [
            'promoKprActive' => $promoKprActive,
            'promoKprPending' => $promoKprPending,
            'breadcrumbs' => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Promo KPR - Kelola', 'url' => ''],
            ],
        ]);
    }

    public function updateKpr(Request $request)
    {
        $request->validate([
            'nama_promo' => 'required|array|min:1',
            'nama_promo.*' => 'required|string|max:255',
        ]);

        $perumahaanId = $this->currentPerumahaanId();

        $pendingBatch = PpjbPromoBatch::where('tipe', 'kpr')
            ->where('status_pengajuan', 'pending')
            ->where('perumahaan_id', $perumahaanId)
            ->first();

        if ($pendingBatch) {
            return redirect()
                ->route('settingPPJB.promoKpr.edit')
                ->with('error', 'Pengajuan promo kpr gagal. Harap tunggu batch pending sebelumnya disetujui atau ditolak.');
        }

        $batch = PpjbPromoBatch::create([
            'tipe' => 'kpr',
            'status_aktif' => false,
            'status_pengajuan' => 'pending',
            'diajukan_oleh' => Auth::id(),
            'perumahaan_id' => $request->perumahaan_id ?? $perumahaanId,
            'tanggal_pengajuan' => now(),
        ]);

        foreach ($request->nama_promo as $promo) {
            PpjbPromoItem::create([
                'batch_id' => $batch->id,
                'nama_promo' => $promo,
            ]);
        }

        // Notifikasi group
        $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

        $listPromo = collect($request->nama_promo)
            ->map(callback: fn($promo) => "• {$promo}")
            ->implode("\n");

        $message =
            "🔔 Pengajuan Promo KPR\n" .
            "```\n" .
            "Perumahaan   : {$batch->perumahaan->nama_perumahaan}\n" .
            "Jenis Promo  : KPR\n" .
            "Diajukan oleh: " . Auth::user()->nama_lengkap . "\n" .
            "Status       : Pending\n" .
            "```\n" .
            "📋 Daftar Promo:\n" .
            "{$listPromo}\n\n" .
            "⏳ Menunggu persetujuan";

        $this->notificationGroup->send($groupId, $message);

        return redirect()
            ->route('settingPPJB.promoKpr.edit')
            ->with('success', 'Pengajuan promo kpr baru berhasil diajukan dan menunggu persetujuan.');
    }

    public function cancelPengajuanPromo(PpjbPromoBatch $batch)
    {
        if ($batch->status_pengajuan !== 'pending') {
            return redirect()->back()->with('error', 'Hanya batch pending yang bisa dibatalkan.');
        }

        // Snapshot data sebelum dihapus
        $listPromo = $batch->items
            ->map(fn($item) => "• {$item->nama_promo}")
            ->implode("\n");

        $tipePromo = strtoupper($batch->tipe);
        $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

        // Aksi delete untuk batalkan pengajuan promo
        $batch->items()->delete();
        $batch->delete();

        // NOTIFIKASI (TAMBAHAN)
        try {
            $message =
                "🚫 Pembatalan Pengajuan Promo {$tipePromo}\n" .
                "```\n" .
                "Perumahaan   : {$batch->perumahaan->nama_perumahaan}\n" .
                "Jenis Promo  : {$tipePromo}\n" .
                "Dibatalkan oleh: " . Auth::user()->nama_lengkap . "\n" .
                "Status       : Dibatalkan\n" .
                "```\n" .
                "📋 Daftar Promo:\n" .
                "{$listPromo}\n\n" .
                "❌ Pengajuan promo telah dibatalkan";

            $this->notificationGroup->send($groupId, $message);
        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi pembatalan promo', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Pengajuan promo berhasil dibatalkan.');
    }


    public function nonAktifPromo(PpjbPromoBatch $batch)
    {
        if ($batch->status_aktif != 1) {
            return redirect()->back()->with('error', 'Hanya batch aktif yang bisa dinonaktifkan.');
        }

        $batch->update([
            'status_aktif' => false,
        ]);


        // Notifikasi Nonaktif ke group
        $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

        $tipePromo = strtoupper($batch->tipe);

        $listPromo = $batch->items
            ->pluck('nama_promo')
            ->map(fn($promo) => "• {$promo}")
            ->implode("\n");

        // Notifikasi Ke Group Pesan
        $message =
            "🔕 Promo {$tipePromo} Telah Dinonaktifkan\n" .
            "```\n" .
            "Perumahaan   : {$batch->perumahaan->nama_perumahaan}\n" .
            "Jenis Promo  : {$tipePromo}\n" .
            "Dinonaktifkan oleh: " . Auth::user()->nama_lengkap . "\n" .
            "Status       : Tidak Aktif\n" .
            "```\n" .
            "📋 Daftar Promo:\n" .
            "{$listPromo}\n\n" .
            "⛔ Promo {$tipePromo} ini sudah tidak aktif dan tidak dapat digunakan";

        // Kirim ke service
        $this->notificationGroup->send($groupId, $message);

        return redirect()->back()->with('success', 'Batch promo berhasil dinonaktifkan.');
    }

    public function history(string $type)
    {
        $perumahaanId = $this->currentPerumahaanId();

        $nonAktif = PpjbPromoBatch::where('tipe', $type)
            ->where('status_pengajuan', 'acc')
            ->where('status_aktif', false)
            ->where('perumahaan_id', $perumahaanId)
            ->with(['items', 'penyetuju', 'pengaju'])
            ->latest()
            ->take(10)
            ->get();

        $ditolak = PpjbPromoBatch::where('tipe', $type)
            ->where('status_pengajuan', 'tolak')
            ->where('perumahaan_id', $perumahaanId)
            ->with(['items', 'penyetuju', 'pengaju'])
            ->latest()
            ->take(10)
            ->get();

        $editRoute = $type === 'cash'
            ? route('settingPPJB.promoCash.edit')
            : route('settingPPJB.promoKpr.edit');

        return view('marketing.setting.promo-history', [
            'type' => $type,
            'nonAktif' => $nonAktif,
            'ditolak' => $ditolak,
            'breadcrumbs' => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Promo ' . $type, 'url' => $editRoute],
                ['label' => 'Riwayat Promo', 'url' => ''],
            ],
        ]);
    }

    // Manager Keuangan aksi untuk approve dan tolak pengajuan cara bayar baru
    public function approvePengajuan(PpjbPromoBatch $promoBatch)
    {
        // SNAPSHOT DATA UNTUK NOTIFIKASI
        $tipePromo = strtoupper($promoBatch->tipe);
        $namaPerumahan = $promoBatch->perumahaan->nama_perumahaan;
        $listPromo = $promoBatch->items
            ->pluck('nama_promo')
            ->map(fn($promo) => "• {$promo}")
            ->implode("\n");

        try {
            DB::transaction(function () use ($promoBatch) {

                if ($promoBatch->status_pengajuan !== 'pending') {
                    throw new \DomainException(
                        'Hanya pengajuan cara bayar dengan status pending yang bisa disetujui.'
                    );
                }

                PpjbPromoBatch::where('perumahaan_id', $promoBatch->perumahaan_id)
                    ->where('tipe', $promoBatch->tipe)
                    ->where('status_aktif', 1)
                    ->update(['status_aktif' => 0]);

                $promoBatch->update([
                    'status_aktif' => 1,
                    'status_pengajuan' => 'acc',
                    'disetujui_oleh' => Auth::id(),
                ]);
            });

            // NOTIFIKASI (TAMBAHAN)
            try {
                $message =
                    "✅ Persetujuan Promo {$tipePromo}\n" .
                    "```\n" .
                    "Perumahaan   : {$namaPerumahan}\n" .
                    "Jenis Promo  : {$tipePromo}\n" .
                    "Disetujui oleh: " . Auth::user()->nama_lengkap . "\n" .
                    "Status       : Aktif\n" .
                    "```\n" .
                    "📋 Daftar Promo:\n" .
                    "{$listPromo}\n\n" .
                    "🔄 Promo {$tipePromo} sebelumnya dinonaktifkan\n" .
                    "Promo {$tipePromo} ini resmi AKTIF";

                $this->notificationGroup->send(
                    env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN'),
                    $message
                );
            } catch (\Throwable $e) {
                \Log::error('Gagal kirim notifikasi approve promo', [
                    'batch_id' => $promoBatch->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $redirectRoute = match (strtolower($promoBatch->tipe)) {
                'kpr' => route('settingPPJB.promoKpr.edit'),
                'cash' => route('settingPPJB.promoCash.edit'),
                default => route('settingPPJB.index'),
            };

            return redirect($redirectRoute)
                ->with('success', 'Pengajuan cara bayar berhasil disetujui dan diaktifkan.');

        } catch (\Throwable $e) {

            $redirectRoute = match (strtolower($promoBatch->tipe)) {
                'kpr' => route('settingPPJB.promoKpr.edit'),
                'cash' => route('settingPPJB.promoCash.edit'),
                default => route('settingPPJB.index'),
            };

            return redirect($redirectRoute)
                ->with('error', $e->getMessage());
        }
    }


    public function rejectPengajuan(PpjbPromoBatch $promoBatch)
    {
        if ($promoBatch->status_pengajuan !== 'pending') {
            return redirect()->back()
                ->with('error', 'Hanya pengajuan promo dengan status pending yang bisa ditolak.');
        }

        // SNAPSHOT DATA
        $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');
        $tipePromo = strtoupper($promoBatch->tipe);
        $namaPerumahan = $promoBatch->perumahaan->nama_perumahaan;

        $listPromo = $promoBatch->items
            ->pluck('nama_promo')
            ->map(fn($promo) => "• {$promo}")
            ->implode("\n");

        // AKSI UTAMA
        $promoBatch->update([
            'status_pengajuan' => 'tolak',
            'ditolak_oleh' => Auth::id(), // kalau ada field-nya
        ]);

        // NOTIFIKASI (TAMBAHAN)
        try {
            $message =
                "❌ Penolakan Pengajuan Promo {$tipePromo}\n" .
                "```\n" .
                "Perumahaan   : {$namaPerumahan}\n" .
                "Jenis Promo  : {$tipePromo}\n" .
                "Ditolak oleh : " . Auth::user()->nama_lengkap . "\n" .
                "Status       : Ditolak\n" .
                "```\n" .
                "📋 Daftar Promo:\n" .
                "{$listPromo}\n\n" .
                "🚫 Pengajuan promo {$tipePromo} tidak disetujui";

            $this->notificationGroup->send($groupId, $message);
        } catch (\Throwable $e) {
            \Log::error('Gagal kirim notifikasi reject promo', [
                'batch_id' => $promoBatch->id,
                'error' => $e->getMessage(),
            ]);
        }

        $redirectRoute = match (strtolower($promoBatch->tipe)) {
            'kpr' => route('settingPPJB.promoKpr.edit'),
            'cash' => route('settingPPJB.promoCash.edit'),
            default => route('settingPPJB.index'),
        };

        return redirect($redirectRoute)
            ->with('success', 'Pengajuan promo berhasil ditolak.');
    }


}
