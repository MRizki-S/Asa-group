<?php
namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\PpjbPembatalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        // dd($request->all());
        $request->validate([
            'perumahaan_id'    => 'required|integer',
            'persentase_potongan' => 'required|integer|min:0',
        ]);

        $perumahaanId = $request->perumahaan_id;

        // cek jika ada pengajuan pending sebelumnya
        $pendingCaraBayar = PpjbPembatalan::where('perumahaan_id', $perumahaanId)
            ->where('status_pengajuan', 'pending')
            ->first();

        if ($pendingCaraBayar) {
            return redirect()
                ->back()
                ->with('error', 'Pengajuan Pembatalan gagal. Harap tunggu pengajuan pending sebelumnya disetujui atau ditolak.');
        }

        // buat pengajuan baru
        PpjbPembatalan::create([
            'perumahaan_id'    => $perumahaanId,
            'persentase_potongan' => $request->persentase_potongan,
            'status_aktif'     => 0,
            'status_pengajuan' => 'pending',
            'diajukan_oleh'    => Auth::id(),
            'disetujui_oleh'   => null,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Pengajuan Keterlambaran baru berhasil diajukan dan menunggu persetujuan.');
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

}
