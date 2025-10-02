<?php
namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\PpjbCaraBayar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingCaraBayarController extends Controller
{
    public function editCaraBayar()
    {
        $user = Auth::user();

        // Tentukan perumahaan saat ini
        $currentPerumahaanId = $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;

        // Ambil Cara Bayar aktif
        $caraBayarActive = PpjbCaraBayar::with(['pengaju', 'approver'])
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('status_aktif', 1)
            ->first();

        // Ambil pending (pengajuan)
        $caraBayarPending = PpjbCaraBayar::with(['pengaju'])
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('status_pengajuan', 1)
            ->where('status_aktif', 0)
            ->first();

        return view('marketing.setting.cara-bayar-kelola', [
            'caraBayarActive'  => $caraBayarActive,
            'caraBayarPending' => $caraBayarPending,
            'breadcrumbs'      => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Kelola Cara Bayar', 'url' => route('settingPPJB.caraBayar.edit')],
            ],
        ]);
    }

    public function updatePengajuan(Request $request)
    {
        $request->validate([
            'perumahaan_id'  => 'required|integer',
            'jumlah_cicilan' => 'required|integer|min:0',
            'minimal_dp'     => 'required|integer|min:0',
        ]);

        $perumahaanId = $request->perumahaan_id;

        // cek jika ada pengajuan pending sebelumnya
        $pendingCaraBayar = PpjbCaraBayar::where('perumahaan_id', $perumahaanId)
            ->where('status_pengajuan', 'pending')
            ->first();

        if ($pendingCaraBayar) {
            return redirect()
                ->back()
                ->with('error', 'Pengajuan cara bayar gagal. Harap tunggu pengajuan pending sebelumnya disetujui atau ditolak.');
        }

        // buat pengajuan baru
        PpjbCaraBayar::create([
            'perumahaan_id'    => $perumahaanId,
            'jumlah_cicilan'   => $request->jumlah_cicilan,
            'minimal_dp'       => $request->minimal_dp,
            'status_aktif'     => 0,
            'status_pengajuan' => 'pending',
            'diajukan_oleh'    => Auth::id(),
            'disetujui_oleh'   => null,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Pengajuan Cara Bayar baru berhasil diajukan dan menunggu persetujuan.');
    }

    // nonaktifkan cara bayar yang aktif
    public function nonAktifCaraBayar(PpjbCaraBayar $caraBayar)
    {
        if (! $caraBayar->status_aktif) {
            return redirect()->back()->with('error', 'Hanya cara bayar aktif yang bisa dinonaktifkan.');
        }

        $caraBayar->update(['status_aktif' => false]);

        return redirect()->back()->with('success', 'Cara bayar berhasil dinonaktifkan.');
    }

    /**
     * Batalkan Pengajuan Cara Bayar Pending
     */
    public function cancelPengajuanCaraBayar(PpjbCaraBayar $caraBayar)
    {
        if ($caraBayar->status_pengajuan !== 'pending') {
            return redirect()->back()->with('error', 'Hanya pengajuan cara bayar dengan status pending yang bisa dibatalkan.');
        }

        // karena cara bayar pending belum dipakai, langsung hapus saja
        $caraBayar->delete();

        return redirect()->back()->with('success', 'Pengajuan cara bayar berhasil dibatalkan.');
    }
}
