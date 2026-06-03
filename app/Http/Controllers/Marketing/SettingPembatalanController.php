<?php
namespace App\Http\Controllers\Marketing;

use Illuminate\Http\Request;
use App\Models\PpjbPembatalan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationGroupService;

class SettingPembatalanController extends Controller
{


    protected NotificationGroupService $notificationGroup;

    // Notifikasi Group
    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }

    public function editPembatalan()
    {
        $user = Auth::user();

        // Tentukan perumahaan saat ini
        $currentPerumahaanId = $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;

        // Ambil Pembatalan aktif
        $pembatalanActive = PpjbPembatalan::with(['pengaju', 'approver'])
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('status_aktif', 1)
            ->first();

        // Ambil pending (pengajuan)
        $pembatalanPending = PpjbPembatalan::with(['pengaju'])
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('status_pengajuan', 1)
            ->where('status_aktif', 0)
            ->first();

        return view('marketing.setting.pembatalan-kelola', [
            'pembatalanActive' => $pembatalanActive,
            'pembatalanPending' => $pembatalanPending,
            'breadcrumbs' => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Kelola Pembatalan', 'url' => route('settingPPJB.pembatalan.edit')],
            ],
        ]);
    }
    public function updatePengajuan(Request $request)
    {
        // Validasi input
        $request->validate([
            'perumahaan_id' => 'required|integer',
            'persentase_potongan' => 'required|integer|min:0',
            'nominal_potongan_kpr' => 'required|integer|min:0',
            'nominal_potongan_cash' => 'required|integer|min:0',
        ]);

        $perumahaanId = $request->perumahaan_id;

        // Cek pending
        $pending = PpjbPembatalan::where('perumahaan_id', $perumahaanId)
            ->where('status_pengajuan', 'pending')
            ->first();

        if ($pending) {
            return redirect()->back()
                ->with('error', 'Pengajuan pembatalan gagal. Harap tunggu pengajuan pending sebelumnya disetujui atau ditolak.');
        }

        // Buat pengajuan
        $pembatalan = PpjbPembatalan::create([
            'perumahaan_id' => $perumahaanId,
            'persentase_potongan' => $request->persentase_potongan,
            'nominal_potongan_kpr' => $request->nominal_potongan_kpr,
            'nominal_potongan_cash' => $request->nominal_potongan_cash,
            'status_aktif' => 0,
            'status_pengajuan' => 'pending',
            'diajukan_oleh' => Auth::id(),
            'disetujui_oleh' => null,
        ]);

        // NOTIFIKASI WA
        try {
            $pembatalan->load('perumahaan');

            $persen = rtrim(rtrim(
                number_format($pembatalan->persentase_potongan, 2, '.', ''),
                '0'
            ), '.');

            $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

            $message =
                "🔔 Pengajuan Setting Pembatalan Pemesanan\n" .
                "```\n" .
                "Perumahaan   : {$pembatalan->perumahaan->nama_perumahaan}\n" .
                "Potongan %   : {$persen} %\n" .
                "Potongan KPR : Rp " . number_format($pembatalan->nominal_potongan_kpr, 0, ',', '.') . "\n" .
                "Potongan CASH: Rp " . number_format($pembatalan->nominal_potongan_cash, 0, ',', '.') . "\n" .
                "Diajukan oleh: " . Auth::user()->nama_lengkap . "\n" .
                "Status       : Pending\n" .
                "```\n" .
                "⏳ Menunggu persetujuan";

            $this->notificationGroup->send($groupId, $message);

        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi pengajuan pembatalan', [
                'pembatalan_id' => $pembatalan->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Pengajuan pembatalan baru berhasil diajukan dan menunggu persetujuan.');
    }


    // nonaktifkan pembatalan yang aktif
    public function nonAktifPembatalan(PpjbPembatalan $pembatalan)
    {
        // dd($pembatalan);
        if (!$pembatalan->status_aktif) {
            return redirect()->back()->with('error', 'Hanya Pembatalan aktif yang bisa dinonaktifkan.');
        }

        $pembatalan->update(['status_aktif' => false]);

        return redirect()->back()->with('success', 'Pembatalan berhasil dinonaktifkan.');
    }

    /**
     * Batalkan Pengajuan Pembatalan Pending
     */
    public function cancelPengajuanPembatalan(PpjbPembatalan $pembatalan)
    {
        // Validasi status
        if ($pembatalan->status_pengajuan !== 'pending') {
            return redirect()->back()
                ->with('error', 'Hanya pengajuan Pembatalan dengan status pending yang bisa dibatalkan.');
        }

        // Ambil data sebelum dihapus (untuk notifikasi)
        $pembatalan->load('perumahaan');

        $persen = rtrim(rtrim(
            number_format($pembatalan->persentase_potongan, 2, '.', ''),
            '0'
        ), '.');

        $nominalKpr = number_format($pembatalan->nominal_potongan_kpr, 0, ',', '.');
        $nominalCash = number_format($pembatalan->nominal_potongan_cash, 0, ',', '.');

        // Hapus data (proses utama)
        $pembatalan->delete();

        // NOTIFIKASI WA
        try {
            $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

            $message =
                "🚫 Pembatalan Pengajuan SETTING PEMBATALAN PEMESANAN\n" .
                "```\n" .
                "Perumahaan   : {$pembatalan->perumahaan->nama_perumahaan}\n" .
                "Potongan %   : {$persen} %\n" .
                "Potongan KPR : Rp {$nominalKpr}\n" .
                "Potongan CASH: Rp {$nominalCash}\n" .
                "Dibatalkan oleh: " . Auth::user()->nama_lengkap . "\n" .
                "Status       : Dibatalkan\n" .
                "```\n" .
                "❌ Pengajuan setting pembatalan telah dibatalkan";

            $this->notificationGroup->send($groupId, $message);

        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi pembatalan pengajuan pembatalan', [
                'pembatalan_id' => $pembatalan->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Pengajuan Pembatalan berhasil dibatalkan.');
    }


    // Approve Pengajuan Pembatalan
    public function approvePengajuanPembatalan(PpjbPembatalan $pembatalan)
    {
        try {
            DB::transaction(function () use ($pembatalan) {
                //  Validasi status
                if ($pembatalan->status_pengajuan !== 'pending') {
                    throw new \Exception('Hanya pengajuan pembatalan dengan status pending yang bisa disetujui.');
                }

                // Nonaktifkan semua pembatalan aktif sebelumnya
                PpjbPembatalan::where('perumahaan_id', $pembatalan->perumahaan_id)
                    ->where('status_aktif', 1)
                    ->update(['status_aktif' => 0]);

                // Set pengajuan ini menjadi aktif
                $pembatalan->update([
                    'status_aktif' => 1,
                    'status_pengajuan' => 'acc',
                    'disetujui_oleh' => Auth::id(),
                ]);
            });

            // NOTIFIKASI WA (TAMBAHAN)
            try {
                $pembatalan->load('perumahaan');

                $persen = rtrim(rtrim(
                    number_format($pembatalan->persentase_potongan, 2, '.', ''),
                    '0'
                ), '.');

                $nominalKpr = number_format($pembatalan->nominal_potongan_kpr, 0, ',', '.');
                $nominalCash = number_format($pembatalan->nominal_potongan_cash, 0, ',', '.');

                $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

                $message =
                    "✅ Persetujuan SETTING PEMBATALAN PEMESANAN\n" .
                    "```\n" .
                    "Perumahaan   : {$pembatalan->perumahaan->nama_perumahaan}\n" .
                    "Potongan %   : {$persen} %\n" .
                    "Potongan KPR : Rp {$nominalKpr}\n" .
                    "Potongan CASH: Rp {$nominalCash}\n" .
                    "Disetujui oleh: " . Auth::user()->nama_lengkap . "\n" .
                    "Status       : Aktif\n" .
                    "```\n" .
                    "🔄 Setting pembatalan sebelumnya dinonaktifkan\n" .
                    "🎉 Setting pembatalan ini resmi AKTIF";

                $this->notificationGroup->send($groupId, $message);

            } catch (\Throwable $e) {
                Log::error('Gagal kirim notifikasi ACC pengajuan pembatalan', [
                    'pembatalan_id' => $pembatalan->id,
                    'error' => $e->getMessage(),
                ]);
            }


            return redirect()->back()->with('success', 'Pengajuan pembatalan berhasil disetujui dan diaktifkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // tolak pengajuan pembatalan
    public function rejectPengajuanPembatalan(PpjbPembatalan $pembatalan)
    {
        // 🔹 Cek apakah statusnya masih pending
        if ($pembatalan->status_pengajuan !== 'pending') {
            return redirect()->back()->with('error', 'Hanya pengajuan pembatalan dengan status pending yang bisa ditolak.');
        }

        $pembatalan->update([
            'status_pengajuan' => 'tolak',
        ]);

        // NOTIFIKASI WA (TOLAK)
        try {
            $pembatalan->load('perumahaan');

            // persen tanpa .00
            $persen = rtrim(rtrim(
                number_format($pembatalan->persentase_potongan, 2, '.', ''),
                '0'
            ), '.');

            $nominalKpr = number_format($pembatalan->nominal_potongan_kpr, 0, ',', '.');
            $nominalCash = number_format($pembatalan->nominal_potongan_cash, 0, ',', '.');

            $groupId = env('FONNTE_ID_GROUP_DUKUNGAN_LAYANAN');

            $message =
                "❌ Penolakan SETTING PEMBATALAN PEMESANAN\n" .
                "```\n" .
                "Perumahaan   : {$pembatalan->perumahaan->nama_perumahaan}\n" .
                "Potongan %   : {$persen} %\n" .
                "Potongan KPR : Rp {$nominalKpr}\n" .
                "Potongan CASH: Rp {$nominalCash}\n" .
                "Ditolak oleh : " . Auth::user()->nama_lengkap . "\n" .
                "Status       : Ditolak\n" .
                "```\n" .
                "🚫 Pengajuan setting pembatalan pemesanan tidak disetujui";

            $this->notificationGroup->send($groupId, $message);

        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi penolakan pengajuan pembatalan', [
                'pembatalan_id' => $pembatalan->id,
                'error' => $e->getMessage(),
            ]);
        }


        return back()->with('success', 'Pengajuan pembatalan berhasil ditolak.');
    }

}
