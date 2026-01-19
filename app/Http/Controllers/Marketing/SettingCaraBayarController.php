<?php
namespace App\Http\Controllers\Marketing;

use Illuminate\Http\Request;
use App\Models\PpjbCaraBayar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationGroupService;

class SettingCaraBayarController extends Controller
{

    protected NotificationGroupService $notificationGroup;

    // Notifikasi Group
    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }
    public function editCaraBayar()
    {
        $user = Auth::user();

        // Tentukan perumahaan saat ini
        $currentPerumahaanId = $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;

        // KPR
        $caraBayarActiveKpr = PpjbCaraBayar::with(['pengaju', 'approver'])
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('jenis_pembayaran', 'KPR')
            ->where('status_aktif', 1)
            ->first();

        $caraBayarPendingKpr = PpjbCaraBayar::with(['pengaju'])
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('jenis_pembayaran', 'KPR')
            ->where('status_pengajuan', 1)
            ->where('status_aktif', 0)
            ->first();

        // CASH
        $caraBayarActiveCash = PpjbCaraBayar::with(['pengaju', 'approver'])
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('jenis_pembayaran', 'CASH')
            ->where('status_aktif', 1)
            ->get();

        $caraBayarPendingCash = PpjbCaraBayar::with(['pengaju'])
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('jenis_pembayaran', 'CASH')
            ->where('status_pengajuan', 1)
            ->where('status_aktif', 0)
            ->get();

        // dd($caraBayarPendingCash);

        return view('marketing.setting.cara-bayar-kelola', [
            // KPR
            'caraBayarActiveKpr' => $caraBayarActiveKpr,
            'caraBayarPendingKpr' => $caraBayarPendingKpr,
            // CASH
            'caraBayarActiveCash' => $caraBayarActiveCash,
            'caraBayarPendingCash' => $caraBayarPendingCash,
            'breadcrumbs' => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Kelola Cara Bayar', 'url' => route('settingPPJB.caraBayar.edit')],
            ],
        ]);
    }

    public function updatePengajuan(Request $request)
    {
        $request->validate([
            'perumahaan_id' => 'required|integer',
            'jenis_pembayaran' => 'required|in:KPR,CASH',
            'nama_cara_bayar' => 'required|string|max:255',
            'jumlah_cicilan' => 'required|integer|min:1',
            'minimal_dp' => 'required|integer|min:0',
        ]);

        $perumahaanId = $request->perumahaan_id;
        $jenisPembayaran = $request->jenis_pembayaran;

        if ($jenisPembayaran === 'KPR') {
            // Cek pengajuan KPR pending
            $pending = PpjbCaraBayar::where('perumahaan_id', $perumahaanId)
                ->where('jenis_pembayaran', 'KPR')
                ->where('status_pengajuan', 'pending')
                ->where('status_aktif', 0)
                ->first();

            if ($pending) {
                return redirect()
                    ->back()
                    ->with('error', 'Pengajuan cara bayar KPR gagal. Harap tunggu pengajuan pending sebelumnya disetujui atau ditolak.');
            }
        } else {
            // CASH: batasi maksimal 5 pengajuan pending
            $pendingCount = PpjbCaraBayar::where('perumahaan_id', $perumahaanId)
                ->where('jenis_pembayaran', 'CASH')
                ->where('status_pengajuan', 'pending')
                ->where('status_aktif', 0)
                ->count();

            if ($pendingCount >= 5) {
                return redirect()
                    ->back()
                    ->with('error', 'Pengajuan cara bayar Cash gagal. Maksimal 5 pengajuan pending.');
            }
        }

        // Buat pengajuan baru
        $caraBayar = PpjbCaraBayar::create([
            'perumahaan_id' => $perumahaanId,
            'jenis_pembayaran' => $jenisPembayaran,
            'nama_cara_bayar' => $request->nama_cara_bayar,
            'jumlah_cicilan' => $request->jumlah_cicilan,
            'minimal_dp' => $request->minimal_dp,
            'status_aktif' => 0,
            'status_pengajuan' => 'pending',
            'diajukan_oleh' => Auth::id(),
            'disetujui_oleh' => null,
        ]);

        // NOTIFIKASI WA
        try {
            $caraBayar->load('perumahaan');

            $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

            $message =
                "🔔 Pengajuan Cara Bayar {$jenisPembayaran}\n" .
                "```\n" .
                "Perumahaan   : {$caraBayar->perumahaan->nama_perumahaan}\n" .
                "Jenis Bayar  : {$jenisPembayaran}\n" .
                "Nama Skema   : {$caraBayar->nama_cara_bayar}\n" .
                "Cicilan      : {$caraBayar->jumlah_cicilan}x\n" .
                "Minimal DP   : Rp " . number_format($caraBayar->minimal_dp, 0, ',', '.') . "\n" .
                "Diajukan oleh: " . Auth::user()->nama_lengkap . "\n" .
                "Status       : Pending\n" .
                "```\n" .
                "⏳ Menunggu persetujuan";

            $this->notificationGroup->send($groupId, $message);
        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi pengajuan cara bayar', [
                'cara_bayar_id' => $caraBayar->id,
                'jenis' => $jenisPembayaran,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Pengajuan Cara Bayar ' . $jenisPembayaran . ' berhasil diajukan dan menunggu persetujuan.')
            ->with('tab', $jenisPembayaran); // kirim tab aktif

    }

    // nonaktifkan cara bayar yang aktif
    public function nonAktifCaraBayar(PpjbCaraBayar $caraBayar)
    {
        if (!$caraBayar->status_aktif) {
            return redirect()->back()
                ->with('error', 'Hanya cara bayar aktif yang bisa dinonaktifkan.');
        }

        $caraBayar->update(['status_aktif' => false]);

        // NOTIFIKASI WA (TAMBAHAN)
        try {
            $caraBayar->load('perumahaan');

            $jenis = strtoupper($caraBayar->jenis_pembayaran);
            $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

            $message =
                "⛔ Nonaktif Cara Bayar {$jenis}\n" .
                "```\n" .
                "Perumahaan   : {$caraBayar->perumahaan->nama_perumahaan}\n" .
                "Jenis Bayar  : {$jenis}\n" .
                "Nama Skema  : {$caraBayar->nama_cara_bayar}\n" .
                "Cicilan     : {$caraBayar->jumlah_cicilan}x\n" .
                "Minimal DP : Rp " . number_format($caraBayar->minimal_dp, 0, ',', '.') . "\n" .
                "Dinonaktifkan oleh: " . Auth::user()->nama_lengkap . "\n" .
                "Status      : Nonaktif\n" .
                "```\n" .
                "🔕 Cara bayar {$jenis} ini telah dinonaktifkan";

            $this->notificationGroup->send($groupId, $message);

        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi nonaktif cara bayar', [
                'cara_bayar_id' => $caraBayar->id,
                'jenis' => $caraBayar->jenis_pembayaran,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Cara bayar berhasil dinonaktifkan.')
            ->with('tab', $caraBayar->jenis_pembayaran);
    }

    // cancel pengajuan cara bayar yang pending
    public function cancelPengajuanCaraBayar(PpjbCaraBayar $caraBayar)
    {
        if ($caraBayar->status_pengajuan !== 'pending') {
            return redirect()->back()->with('error', 'Hanya pengajuan cara bayar dengan status pending yang bisa dibatalkan.');
        }
        $jenisPembayaran = $caraBayar->jenis_pembayaran;
        // karena cara bayar pending belum dipakai, langsung hapus saja
        $caraBayar->delete();

        // NOTIFIKASI WA
        try {
            $caraBayar->load('perumahaan');

            $jenis = strtoupper($caraBayar->jenis_pembayaran);
            $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

            $message =
                "🚫 Pembatalan Pengajuan Cara Bayar {$jenis}\n" .
                "```\n" .
                "Perumahaan     : {$caraBayar->perumahaan->nama_perumahaan}\n" .
                "Jenis Bayar    : {$jenis}\n" .
                "Nama Skema     : {$caraBayar->nama_cara_bayar}\n" .
                "Cicilan        : {$caraBayar->jumlah_cicilan}x\n" .
                "Minimal DP     : Rp " . number_format($caraBayar->minimal_dp, 0, ',', '.') . "\n" .
                "Dibatalkan oleh: " . Auth::user()->nama_lengkap . "\n" .
                "Status         : Dibatalkan\n" .
                "```\n" .
                "❌ Pengajuan cara bayar {$jenis} ini telah dibatalkan";

            $this->notificationGroup->send($groupId, $message);

        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi pembatalan cara bayar', [
                'cara_bayar_id' => $caraBayar->id,
                'jenis' => $caraBayar->jenis_pembayaran,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()->with('success', 'Pengajuan cara bayar berhasil dibatalkan.')
            ->with('tab', $jenisPembayaran);
    }

    // Manager Keuangan aksi untuk approve dan tolak pengajuan cara bayar baru
    public function approvePengajuanCaraBayar(PpjbCaraBayar $caraBayar)
    {
        try {
            DB::transaction(function () use ($caraBayar) {
                // Hanya boleh ACC pengajuan yang statusnya pending
                if ($caraBayar->status_pengajuan !== 'pending') {
                    throw new \Exception('Hanya pengajuan cara bayar dengan status pending yang bisa disetujui.');
                }

                // Jika jenis pembayaran KPR
                if ($caraBayar->jenis_pembayaran === 'KPR') {
                    // Nonaktifkan semua KPR aktif lain di perumahaan yang sama
                    PpjbCaraBayar::where('perumahaan_id', $caraBayar->perumahaan_id)
                        ->where('jenis_pembayaran', 'KPR')
                        ->where('status_aktif', 1)
                        ->update(['status_aktif' => 0]);

                    // Set pengajuan ini jadi aktif & disetujui
                    $caraBayar->update([
                        'status_aktif' => 1,
                        'status_pengajuan' => 'acc',
                        'disetujui_oleh' => Auth::id(),
                    ]);
                }

                // Jika jenis pembayaran Cash
                else if ($caraBayar->jenis_pembayaran === 'CASH') {
                    $caraBayar->update([
                        'status_aktif' => 1,
                        'status_pengajuan' => 'acc',
                        'disetujui_oleh' => Auth::id(),
                    ]);
                }
            });

            // NOTIFIKASI WA (TAMBAHAN)
            try {
                $caraBayar->load('perumahaan');

                $jenis = strtoupper($caraBayar->jenis_pembayaran);
                $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

                $message =
                    "✅ Persetujuan Cara Bayar {$jenis}\n" .
                    "```\n" .
                    "Perumahaan   : {$caraBayar->perumahaan->nama_perumahaan}\n" .
                    "Jenis Bayar  : {$jenis}\n" .
                    "Nama Skema  : {$caraBayar->nama_cara_bayar}\n" .
                    "Cicilan     : {$caraBayar->jumlah_cicilan}x\n" .
                    "Minimal DP : Rp " . number_format($caraBayar->minimal_dp, 0, ',', '.') . "\n" .
                    "Disetujui oleh: " . Auth::user()->nama_lengkap . "\n" .
                    "Status      : Aktif\n" .
                    "```\n" .
                    ($jenis === 'KPR'
                        ? "🔄 Cara bayar KPR sebelumnya dinonaktifkan\n"
                        : "") .
                    "🎉 Cara bayar {$jenis} ini resmi AKTIF";

                $this->notificationGroup->send($groupId, $message);

            } catch (\Throwable $e) {
                Log::error('Gagal kirim notifikasi ACC cara bayar', [
                    'cara_bayar_id' => $caraBayar->id,
                    'jenis' => $caraBayar->jenis_pembayaran,
                    'error' => $e->getMessage(),
                ]);
            }

            return redirect()->back()
                ->with('success', 'Pengajuan cara bayar berhasil disetujui dan diaktifkan.')
                ->with('tab', $caraBayar->jenis_pembayaran);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->with('tab', $caraBayar->jenis_pembayaran);
        }
    }

    public function rejectPengajuanCaraBayar(PpjbCaraBayar $caraBayar)
    {
        // dd($caraBayar);
        if ($caraBayar->status_pengajuan !== 'pending') {
            return redirect()->back()->with('error', 'Hanya pengajuan cara bayar dengan status pending yang bisa ditolak.');
        }

        $jenisPembayaran = $caraBayar->jenis_pembayaran;

        $caraBayar->update([
            'status_pengajuan' => 'tolak',
        ]);

        // NOTIFIKASI WA
        try {
            $caraBayar->load('perumahaan');

            $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

            $message =
                "❌ Penolakan Cara Bayar {$jenisPembayaran}\n" .
                "```\n" .
                "Perumahaan   : {$caraBayar->perumahaan->nama_perumahaan}\n" .
                "Jenis Bayar  : {$jenisPembayaran}\n" .
                "Nama Skema  : {$caraBayar->nama_cara_bayar}\n" .
                "Cicilan     : {$caraBayar->jumlah_cicilan}x\n" .
                "Minimal DP : Rp " . number_format($caraBayar->minimal_dp, 0, ',', '.') . "\n" .
                "Ditolak oleh: " . Auth::user()->nama_lengkap . "\n" .
                "Status      : Ditolak\n" .
                "```\n" .
                "🚫 Pengajuan cara bayar {$jenisPembayaran} tidak disetujui";

            $this->notificationGroup->send($groupId, $message);

        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi penolakan cara bayar', [
                'cara_bayar_id' => $caraBayar->id,
                'jenis' => $caraBayar->jenis_pembayaran,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Pengajuan cara bayar berhasil ditolak.')
            ->with('tab', $jenisPembayaran);
    }
}
