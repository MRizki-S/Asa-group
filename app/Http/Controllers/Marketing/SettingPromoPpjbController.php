<?php
namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Perumahaan;
use App\Models\PpjbPromoBatch;
use App\Models\PpjbPromoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingPromoPpjbController extends Controller
{
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
            'promoCashActive'  => $promoCashActive,
            'promoCashPending' => $promoCashPending,
            'breadcrumbs'      => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Promo Cash - Kelola', 'url' => ''],
            ],
        ]);
    }

    public function updateCash(Request $request)
    {
        $request->validate([
            'nama_promo'   => 'required|array|min:1',
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
            'tipe'              => 'cash',
            'status_aktif'      => false,
            'status_pengajuan'  => 'pending',
            'diajukan_oleh'     => Auth::id(),
            'perumahaan_id'     => $request->perumahaan_id ?? $perumahaanId,
            'tanggal_pengajuan' => now(),
        ]);

        foreach ($request->nama_promo as $promo) {
            PpjbPromoItem::create([
                'batch_id'   => $batch->id,
                'nama_promo' => $promo,
            ]);
        }

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
            'promoKprActive'  => $promoKprActive,
            'promoKprPending' => $promoKprPending,
            'breadcrumbs'     => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Promo KPR - Kelola', 'url' => ''],
            ],
        ]);
    }

    public function updateKpr(Request $request)
    {
        $request->validate([
            'nama_promo'   => 'required|array|min:1',
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
            'tipe'              => 'kpr',
            'status_aktif'      => false,
            'status_pengajuan'  => 'pending',
            'diajukan_oleh'     => Auth::id(),
            'perumahaan_id'     => $request->perumahaan_id ?? $perumahaanId,
            'tanggal_pengajuan' => now(),
        ]);

        foreach ($request->nama_promo as $promo) {
            PpjbPromoItem::create([
                'batch_id'   => $batch->id,
                'nama_promo' => $promo,
            ]);
        }

        return redirect()
            ->route('settingPPJB.promoKpr.edit')
            ->with('success', 'Pengajuan promo kpr baru berhasil diajukan dan menunggu persetujuan.');
    }

    public function cancelPengajuanPromo(PpjbPromoBatch $batch)
    {
        if ($batch->status_pengajuan !== 'pending') {
            return redirect()->back()->with('error', 'Hanya batch pending yang bisa dibatalkan.');
        }

        $batch->items()->delete();
        $batch->delete();

        return redirect()->back()->with('success', 'Pengajuan promo berhasil dibatalkan.');
    }

    public function nonAktifPromo(PpjbPromoBatch $batch)
    {
        if (! $batch->status_aktif == 1) {
            return redirect()->back()->with('error', 'Hanya batch aktif yang bisa dinonaktifkan.');
        }

        $batch->update([
            'status_aktif' => false,
        ]);

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
            'type'        => $type,
            'nonAktif'    => $nonAktif,
            'ditolak'     => $ditolak,
            'breadcrumbs' => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
                ['label' => 'Promo ' . $type, 'url' => $editRoute],
                ['label' => 'Riwayat Promo', 'url' => ''],
            ],
        ]);
    }
}
