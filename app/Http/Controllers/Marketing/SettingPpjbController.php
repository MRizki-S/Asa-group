<?php
namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\PpjbCaraBayar;
use App\Models\PpjbKeterlambatan;
use App\Models\PpjbMutuBatch;
use App\Models\PpjbPembatalan;
use App\Models\PpjbPromoBatch;
use Illuminate\Support\Facades\Auth;

class SettingPpjbController extends Controller
{
    public function listSettingPPJB()
    {
        $user = Auth::user();

        // Tentukan perumahaan saat ini
        $currentPerumahaanId = $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;

        // Ambil Promo Cash
        $promoCash = PpjbPromoBatch::with('items')
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('tipe', 'cash')
            ->where('status_aktif', 1)
            ->first();

        // Ambil Promo KPR
        $promoKpr = PpjbPromoBatch::with('items')
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('tipe', 'kpr')
            ->where('status_aktif', 1)
            ->first();

        // Ambil Mutu aktif
        $mutu = PpjbMutuBatch::with('items')
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('status_aktif', 1)
            ->first();

        // Ambil Cara Bayar aktif
         $caraBayarKpr = PpjbCaraBayar::with(['pengaju', 'approver'])
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('jenis_pembayaran', 'KPR')
            ->where('status_aktif', 1)
            ->first();

        $caraBayarCash = PpjbCaraBayar::with(['pengaju', 'approver'])
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('jenis_pembayaran', 'CASH')
            ->where('status_aktif', 1)
            ->get();
        // dd($caraBayar);

        $keterlambatanPPJB = PpjbKeterlambatan::with(['pengaju', 'approver'])
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('status_aktif', 1)
            ->first();
        // dd($keterlambatanPPJB);

        $pembatalanPPJB = PpjbPembatalan::with(['pengaju', 'approver'])
            ->where('perumahaan_id', $currentPerumahaanId)
            ->where('status_aktif', 1)
            ->first();
        // dd($pembatalanPPJB);

        return view('marketing.setting.settingPPJB', [
            'promoCash'         => $promoCash,
            'promoKpr'          => $promoKpr,
            'mutu'              => $mutu,
            'caraBayarCash'         => $caraBayarCash,
            'caraBayarKpr'       => $caraBayarKpr,
            'keterlambatanPPJB' => $keterlambatanPPJB,
            'pembatalanPPJB'=> $pembatalanPPJB,
            'breadcrumbs'       => [
                ['label' => 'Setting PPJB', 'url' => route('settingPPJB.index')],
            ],
        ]);
    }

}
