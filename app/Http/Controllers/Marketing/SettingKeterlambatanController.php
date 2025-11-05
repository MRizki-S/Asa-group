<?php
namespace App\Http\Controllers\Marketing;

use Illuminate\Http\Request;
use App\Models\PpjbKeterlambatan;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SettingKeterlambatanController extends Controller
{
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
            'keterlambatanActive'  => $keterlambatanActive,
            'keterlambatanPending' => $keterlambatanPending,
            'breadcrumbs'          => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Kelola Keterlambatan', 'url' => route('settingPPJB.keterlambatan.edit')],
            ],
        ]);
    }

    public function updatePengajuan(Request $request)
    {
        $request->validate([
            'perumahaan_id'    => 'required|integer',
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
        PpjbKeterlambatan::create([
            'perumahaan_id'    => $perumahaanId,
            'persentase_denda' => $request->persentase_denda,
            'status_aktif'     => 0,
            'status_pengajuan' => 'pending',
            'diajukan_oleh'    => Auth::id(),
            'disetujui_oleh'   => null,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Pengajuan Keterlambaran baru berhasil diajukan dan menunggu persetujuan.');
    }

    // nonaktifkan Keterlambatan yang aktif
    public function nonAktifKeterlambatan(PpjbKeterlambatan $keterlambatan)
    {
        // dd($keterlambatan);
        if (! $keterlambatan->status_aktif) {
            return redirect()->back()->with('error', 'Hanya Keterlambatan aktif yang bisa dinonaktifkan.');
        }

        $keterlambatan->update(['status_aktif' => false]);

        return redirect()->back()->with('success', 'Keterlambata berhasil dinonaktifkan.');
    }

    /**
     * Batalkan Pengajuan Keterlambatan Pending
     */
    public function cancelPengajuanKeterlambatan(PpjbKeterlambatan $keterlambatan)
    {
        // dd($keterlambatan);
        if ($keterlambatan->status_pengajuan !== 'pending') {
            return redirect()->back()->with('error', 'Hanya pengajuan Keterlambatan dengan status pending yang bisa dibatalkan.');
        }

        // karena Keterlambatan pending belum dipakai, langsung hapus saja
        $keterlambatan->delete();

        return redirect()->back()->with('success', 'Pengajuan Keterlambatan berhasil dibatalkan.');
    }

    // Approve Pengajuan Pembatalan
    public function approvePengajuan(PpjbKeterlambatan $keterlambatan)
    {
        try {
            DB::transaction(function () use ($keterlambatan) {
                // ✅ Validasi status
                if ($keterlambatan->status_pengajuan !== 'pending') {
                    throw new \Exception('Hanya pengajuan Keterlambatan dengan status pending yang bisa disetujui.');
                }

                // 🟡 Nonaktifkan semua Keterlambatan aktif sebelumnya
                PpjbKeterlambatan::where('perumahaan_id', $keterlambatan->perumahaan_id)
                    ->where('status_aktif', operator: 1)
                    ->update(['status_aktif' => 0])
;
                // ✅ Set pengajuan ini menjadi aktif
                $keterlambatan->update([
                    'status_aktif'     => 1,
                    'status_pengajuan' => 'acc',
                    'disetujui_oleh'   => Auth::id(),
                ]);
            });

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

        return back()->with('success', 'Pengajuan Keterlambatan berhasil ditolak.');
    }
}
