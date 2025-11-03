<?php
namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Perumahaan;
use App\Models\PpjbMutuBatch;
use App\Models\PpjbMutuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingMutuPpjbController extends Controller
{
    /**
     * Mendapatkan current perumahaan id
     */
    private function currentPerumahaanId()
    {
        return Auth::user()->hasGlobalAccess()
            ? session('current_perumahaan_id')
            : Auth::user()->perumahaan_id;
    }

    /**
     * Menampilkan halaman Mutu PPJB
     */
    public function edit()
    {
        $perumahaanId = $this->currentPerumahaanId();

        // Mutu Aktif (ACC + Aktif)
        $mutuActive = PpjbMutuBatch::with(['items', 'penyetuju'])
            ->where('status_aktif', true)
            ->where('status_pengajuan', 'acc')
            ->where('perumahaan_id', $perumahaanId)
            ->first();

        // Mutu Pending
        $mutuPending = PpjbMutuBatch::with(['items', 'pengaju'])
            ->where('status_pengajuan', 'pending')
            ->where('perumahaan_id', $perumahaanId)
            ->first();

        return view('marketing.setting.mutu-kelola', [
            'mutuActive'  => $mutuActive,
            'mutuPending' => $mutuPending,
            'breadcrumbs' => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Mutu PPJB', 'url' => ''],
            ],
        ]);
    }

    // pengajuan mutu baru
    public function pengajuanUpdate(Request $request)
    {
        $request->validate([
            'nama_mutu'      => 'required|array|min:1',
            'nama_mutu.*'    => 'required|string|max:255',
            'nominal_mutu'   => 'required|array|min:1',
            'nominal_mutu.*' => 'required|numeric|min:0',
        ]);

        $perumahaanId = $this->currentPerumahaanId();

        // cek jika ada batch pending sebelumnya
        $pendingBatch = PpjbMutuBatch::where('status_pengajuan', 'pending')
            ->where('perumahaan_id', $perumahaanId)
            ->first();

        if ($pendingBatch) {
            return redirect()
                ->back()
                ->with('error', 'Pengajuan mutu gagal. Harap tunggu batch pending sebelumnya disetujui atau ditolak.');
        }

        // buat batch baru
        $batch = PpjbMutuBatch::create([
            'perumahaan_id'     => $perumahaanId,
            'status_aktif'      => false,
            'status_pengajuan'  => 'pending',
            'diajukan_oleh'     => Auth::id(),
            'tanggal_pengajuan' => now(),
        ]);

        // buat items mutu
        foreach ($request->nama_mutu as $index => $namaMutu) {
            PpjbMutuItem::create([
                'batch_id'     => $batch->id,
                'nama_mutu'    => $namaMutu,
                'nominal_mutu' => $request->nominal_mutu[$index] ?? 0,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Pengajuan Mutu PPJB baru berhasil diajukan dan menunggu persetujuan.');
    }

    /**
     * Nonaktifkan batch Mutu Aktif
     */
    public function nonAktifMutu(PpjbMutuBatch $batch)
    {
        if (! $batch->status_aktif) {
            return redirect()->back()->with('error', 'Hanya batch aktif yang bisa dinonaktifkan.');
        }

        $batch->update(['status_aktif' => false]);

        return redirect()->back()->with('success', 'Batch mutu berhasil dinonaktifkan.');
    }

    /**
     * Batalkan pengajuan Mutu Pending
     */
    public function cancelPengajuanMutu(PpjbMutuBatch $batch)
    {
        if ($batch->status_pengajuan !== 'pending') {
            return redirect()->back()->with('error', 'Hanya batch pending yang bisa dibatalkan.');
        }

        $batch->items()->delete();
        $batch->delete();

        return redirect()->back()->with('success', 'Pengajuan mutu berhasil dibatalkan.');
    }

    // Mutu history nonaktif dan tolak
    public function history()
    {
        $perumahaanId = $this->currentPerumahaanId();

        // 1. Batch ACC tapi nonaktif
        $nonAktif = PpjbMutuBatch::where('status_pengajuan', 'acc')
            ->where('status_aktif', false)
            ->where('perumahaan_id', $perumahaanId)
            ->with(['items', 'penyetuju', 'pengaju'])
            ->latest()
            ->take(10)
            ->get();

        // 2. Batch yang ditolak
        $ditolak = PpjbMutuBatch::where('status_pengajuan', 'tolak')
            ->where('perumahaan_id', $perumahaanId)
            ->with(['items', 'penyetuju', 'pengaju'])
            ->latest()
            ->take(10)
            ->get();

        $editRoute = route('settingPPJB.mutu.edit');

        return view('marketing.setting.mutu-history', [
            'nonAktif'    => $nonAktif,
            'ditolak'     => $ditolak,
            'editRoute'   => $editRoute,
            'breadcrumbs' => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Mutu PPJB', 'url' => $editRoute],
                ['label' => 'Riwayat Mutu', 'url' => ''],
            ],
        ]);
    }

}
