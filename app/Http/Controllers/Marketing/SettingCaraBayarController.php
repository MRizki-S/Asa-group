<?php
namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\PpjbCaraBayar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettingCaraBayarController extends Controller
{
    public function editCaraBayar()
    {
        $user = Auth::user();

        // Tentukan perumahaan saat ini
        $currentPerumahaanId = $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;

        // =========================
        // KPR
        // =========================

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

        // =========================
        // CASH
        // =========================

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
            'caraBayarActiveKpr'   => $caraBayarActiveKpr,
            'caraBayarPendingKpr'  => $caraBayarPendingKpr,
            // CASH
            'caraBayarActiveCash'  => $caraBayarActiveCash,
            'caraBayarPendingCash' => $caraBayarPendingCash,
            'breadcrumbs'          => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Kelola Cara Bayar', 'url' => route('settingPPJB.caraBayar.edit')],
            ],
        ]);
    }

    public function updatePengajuan(Request $request)
    {
        $request->validate([
            'perumahaan_id'    => 'required|integer',
            'jenis_pembayaran' => 'required|in:KPR,CASH',
            'nama_cara_bayar'  => 'required|string|max:255',
            'jumlah_cicilan'   => 'required|integer|min:1',
            'minimal_dp'       => 'required|integer|min:0',
        ]);
        // dd($request->all());
        $perumahaanId    = $request->perumahaan_id;
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
        PpjbCaraBayar::create([
            'perumahaan_id'    => $perumahaanId,
            'jenis_pembayaran' => $jenisPembayaran,
            'nama_cara_bayar'  => $request->nama_cara_bayar,
            'jumlah_cicilan'   => $request->jumlah_cicilan,
            'minimal_dp'       => $request->minimal_dp,
            'status_aktif'     => 0,
            'status_pengajuan' => 'pending',
            'diajukan_oleh'    => Auth::id(),
            'disetujui_oleh'   => null,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Pengajuan Cara Bayar ' . $jenisPembayaran . ' berhasil diajukan dan menunggu persetujuan.')
            ->with('tab', $jenisPembayaran); // kirim tab aktif

    }

    // nonaktifkan cara bayar yang aktif
    public function nonAktifCaraBayar(PpjbCaraBayar $caraBayar)
    {
        if (! $caraBayar->status_aktif) {
            return redirect()->back()->with('error', 'Hanya cara bayar aktif yang bisa dinonaktifkan.');
        }

        $caraBayar->update(['status_aktif' => false]);
        $jenisPembayaran = $caraBayar->jenis_pembayaran;
        return redirect()->back()->with('success', 'Cara bayar berhasil dinonaktifkan.')
            ->with('tab', $jenisPembayaran);
    }

    /**
     * Batalkan Pengajuan Cara Bayar Pending
     */
    public function cancelPengajuanCaraBayar(PpjbCaraBayar $caraBayar)
    {
        if ($caraBayar->status_pengajuan !== 'pending') {
            return redirect()->back()->with('error', 'Hanya pengajuan cara bayar dengan status pending yang bisa dibatalkan.');
        }
        $jenisPembayaran = $caraBayar->jenis_pembayaran;
        // karena cara bayar pending belum dipakai, langsung hapus saja
        $caraBayar->delete();

        return redirect()->back()->with('success', 'Pengajuan cara bayar berhasil dibatalkan.')
            ->with('tab', $jenisPembayaran);
    }

    // Manager Keuangan aksi untuk approven dan tolak pengajuan cara bayar baru
    public function approvePengajuanCaraBayar(PpjbCaraBayar $caraBayar)
    {
        DB::transaction(function () use ($caraBayar) {
            // dd($caraBayar);
            // ✅ Hanya boleh ACC pengajuan yang statusnya pending
            if ($caraBayar->status_pengajuan !== 'pending') {
                throw new \Exception('Hanya pengajuan dengan status pending yang bisa di-ACC.');
            }

            // 🏦 Jika jenis pembayaran KPR
            if ($caraBayar->jenis_pembayaran === 'KPR') {
                // Nonaktifkan semua KPR aktif lain di perumahaan yang sama
                PpjbCaraBayar::where('perumahaan_id', $caraBayar->perumahaan_id)
                    ->where('jenis_pembayaran', 'KPR')
                    ->where('status_aktif', 1)
                    ->update(['status_aktif' => 0]);

                // Set pengajuan ini jadi aktif & disetujui
                $caraBayar->update(attributes: [
                    'status_aktif'     => 1,
                    'status_pengajuan' => 'acc',
                    'disetujui_oleh'   => Auth::id(),
                ]);
            }
            // 💰 Jika jenis pembayaran Cash (masih kosong)
            else if ($caraBayar->jenis_pembayaran === 'CASH') {
                $caraBayar->update([
                    'status_aktif'     => 1,
                    'status_pengajuan' => 'acc',
                    'disetujui_oleh'   => Auth::id(),
                ]);
            }
        });

        return redirect()->back()
            ->with('success', 'Pengajuan cara bayar berhasil di-ACC dan diaktifkan.')
            ->with('tab', $caraBayar->jenis_pembayaran);
    }

    public function rejectPengajuanCaraBayar(PpjbCaraBayar $caraBayar)
    {
        // dd($caraBayar);
        if ($caraBayar->status_pengajuan !== 'pending') {
            return redirect()->back()->with('error', 'Hanya pengajuan cara bayar dengan status pending yang bisa ditolak.');
        }

        $jenisPembayaran = $caraBayar->jenis_pembayaran;

        // Hapus data karena belum digunakan (sama seperti cancel)
        $caraBayar->delete();

        return redirect()->back()
            ->with('success', 'Pengajuan cara bayar berhasil ditolak dan dihapus.')
            ->with('tab', $jenisPembayaran);
    }
}
