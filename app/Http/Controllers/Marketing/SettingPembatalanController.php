<?php
namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\PpjbPembatalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettingPembatalanController extends Controller
{
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
            'pembatalanActive'  => $pembatalanActive,
            'pembatalanPending' => $pembatalanPending,
            'breadcrumbs'       => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Kelola Pembatalan', 'url' => route('settingPPJB.pembatalan.edit')],
            ],
        ]);
    }
    public function updatePengajuan(Request $request)
    {
        //  Validasi input
        $request->validate([
            'perumahaan_id'         => 'required|integer',
            'persentase_potongan'   => 'required|integer|min:0',
            'nominal_potongan_kpr'  => 'required|integer|min:0',
            'nominal_potongan_cash' => 'required|integer|min:0',
        ]);

        $perumahaanId = $request->perumahaan_id;

        //  Cek apakah sudah ada pengajuan dengan status pending
        $pending = PpjbPembatalan::where('perumahaan_id', $perumahaanId)
            ->where('status_pengajuan', 'pending')
            ->first();

        if ($pending) {
            return redirect()
                ->back()
                ->with('error', 'Pengajuan pembatalan gagal. Harap tunggu pengajuan pending sebelumnya disetujui atau ditolak.');
        }

        // 📝 Buat pengajuan baru
        PpjbPembatalan::create([
            'perumahaan_id'         => $perumahaanId,
            'persentase_potongan'   => $request->persentase_potongan,
            'nominal_potongan_kpr'  => $request->nominal_potongan_kpr,
            'nominal_potongan_cash' => $request->nominal_potongan_cash,
            'status_aktif'          => 0,
            'status_pengajuan'      => 'pending',
            'diajukan_oleh'         => Auth::id(),
            'disetujui_oleh'        => null,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Pengajuan pembatalan baru berhasil diajukan dan menunggu persetujuan.');
    }

    // nonaktifkan pembatalan yang aktif
    public function nonAktifPembatalan(PpjbPembatalan $pembatalan)
    {
        // dd($pembatalan);
        if (! $pembatalan->status_aktif) {
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
        // dd($pembatalan);
        if ($pembatalan->status_pengajuan !== 'pending') {
            return redirect()->back()->with('error', 'Hanya pengajuan Pembatalan dengan status pending yang bisa dibatalkan.');
        }

        // karena Pembatalan pending belum dipakai, langsung hapus saja
        $pembatalan->delete();

        return redirect()->back()->with('success', 'Pengajuan Pembatalan berhasil dibatalkan.');
    }

    // Approve Pengajuan Pembatalan
    public function approvePengajuanPembatalan(PpjbPembatalan $pembatalan)
    {
        try {
            DB::transaction(function () use ($pembatalan) {
                // ✅ Validasi status
                if ($pembatalan->status_pengajuan !== 'pending') {
                    throw new \Exception('Hanya pengajuan pembatalan dengan status pending yang bisa disetujui.');
                }

                // 🟡 Nonaktifkan semua pembatalan aktif sebelumnya
                PpjbPembatalan::where('perumahaan_id', $pembatalan->perumahaan_id)
                    ->where('status_aktif', 1)
                    ->update(['status_aktif' => 0]);

                // ✅ Set pengajuan ini menjadi aktif
                $pembatalan->update([
                    'status_aktif'     => 1,
                    'status_pengajuan' => 'acc',
                    'approved_by'      => Auth::id(),
                    'approved_at'      => now(),
                ]);
            });

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

        return back()->with('success', 'Pengajuan pembatalan berhasil ditolak.');
    }

}
