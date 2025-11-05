<?php
namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\PpjbBonusCashBatch;
use App\Models\PpjbBonusCashItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettingBonusCashController extends Controller
{
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
            'bonusCashActive'  => $bonusCashActive,
            'bonusCashPending' => $bonusCashPending,
            'breadcrumbs'      => [
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
            'nama_bonus'   => 'required|array|min:1',
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
            'perumahaan_id'     => $perumahaanId,
            'status_aktif'      => false,
            'status_pengajuan'  => 'pending',
            'diajukan_oleh'     => Auth::id(),
            'tanggal_pengajuan' => now(),
        ]);

        // buat items mutu
        foreach ($request->nama_bonus as $index => $namaMutu) {
            PpjbBonusCashItem::create([
                'batch_id'   => $batch->id,
                'nama_bonus' => $namaMutu,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Pengajuan Bonus Cash PPJB baru berhasil diajukan dan menunggu persetujuan.');
    }

    // nonaktifkan Bonus Cash yang aktif
    public function nonAktif(PpjbBonusCashBatch $batch)
    {
        // dd($batch);
        if (! $batch->status_aktif) {
            return redirect()->back()->with('error', 'Hanya Bonus Cash aktif yang bisa dinonaktifkan.');
        }

        $batch->update(['status_aktif' => false]);
        return
         redirect()->back()->with('success', 'Cara bayar berhasil dinonaktifkan.');
    }

    // cancel pengajuan bonus cash
    public function cancelPengajuan(PpjbBonusCashBatch $batch)
    {
        if ($batch->status_pengajuan !== 'pending') {
            return redirect()->back()->with('error', 'Hanya pengajuan Bonus Cash dengan status pending yang bisa dibatalkan.');
        }

        $batch->delete();

        return redirect()->back()->with('success', 'Pengajuan Bonus Cash berhasil dibatalkan.');
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
            'nonAktif'    => $nonAktif,
            'ditolak'     => $ditolak,
            'editRoute'   => $editRoute,
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
            DB::transaction(function () use ($bonusCash) {
                // ✅ Validasi status
                if ($bonusCash->status_pengajuan !== 'pending') {
                    throw new \Exception('Hanya pengajuan Bonus Cash dengan status pending yang bisa disetujui.');
                }

                // 🟡 Nonaktifkan semua bonusCash aktif sebelumnya
                PpjbBonusCashBatch::where('perumahaan_id', $bonusCash->perumahaan_id)
                    ->where('status_aktif', 1)
                    ->update(['status_aktif' => 0]);

                // ✅ Set pengajuan ini menjadi aktif
                $bonusCash->update([
                    'status_aktif'     => 1,
                    'status_pengajuan' => 'acc',
                    'disetujui_oleh'      => Auth::id(),
                    'tanggal_acc'     => now(),
                ]);
            });

            return redirect()->back()->with('success', 'Pengajuan Bonus Cash berhasil disetujui dan diaktifkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function rejectPengajuan(PpjbBonusCashBatch $bonusCash)
    {
        // dd($bonusCash);
        if ($bonusCash->status_pengajuan !== 'pending') {
            return redirect()->back()->with('error', 'Hanya pengajuan Bonus Cash dengan status pending yang bisa ditolak.');
        }

        $bonusCash->update([
            'status_pengajuan' => 'tolak',
        ]);

        return redirect()->back()
            ->with('success', 'Pengajuan Bonus Cash berhasil ditolak.');
    }
}
