<?php
namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\MasterKprDokumen;
use App\Models\PemesananUnit;
use Illuminate\Support\Facades\Auth;

class ManagePemesananController extends Controller
{
    protected function currentPerumahaanId()
    {
        $user = Auth::user();
        return $user->is_global
            ? session('current_perumahaan_id', null)
            : $user->perumahaan_id;
    }

    public function index()
    {
        $user         = Auth::user();
        $perumahaanId = $this->currentPerumahaanId();

        // ==============================
        // 🔹 PEMESANAN UNIT KPR
        // ==============================
        $pemesananKpr = PemesananUnit::with([
            'customer',
            'sales',
            'unit.blok',
            'kpr.dokumen',
            'kpr.bank',
            'kpr.pemesananUnit',
        ])
            ->where('cara_bayar', 'kpr')
            ->where('status_pengajuan', 'acc')
            ->where('perumahaan_id', $perumahaanId);

        if ($user->hasRole('Sales')) {
            $pemesananKpr->where('sales_id', $user->id);
        }

        $pemesananKpr = $pemesananKpr->get();

        // ==============================
        // 🔹 PEMESANAN UNIT CASH
        // ==============================
        $pemesananCash = PemesananUnit::with([
            'customer',
            'sales',
            'unit.blok',
            'cash.dokumen',
            'cash.pemesananUnit',
        ])
            ->where('cara_bayar', 'cash')
            ->where('status_pengajuan', 'acc')
            ->where('perumahaan_id', $perumahaanId);

        if ($user->hasRole('Sales')) {
            $pemesananCash->where('sales_id', $user->id);
        }

        $pemesananCash = $pemesananCash->get();

        // ==============================
        // 🔹 Hitung kelengkapan berkas
        // ==============================
        foreach ($pemesananKpr as $item) {
            $item->kelengkapan_berkas = '-';
            $item->total_dokumen      = 0;
            $item->dokumen_lengkap    = 0;

            if ($item->kpr && $item->kpr->bank_id) {
                $bankId = $item->kpr->bank_id;

                $total   = MasterKprDokumen::where('bank_id', $bankId)->count();
                $lengkap = $item->kpr->dokumen->where('status', 1)->count();

                $item->total_dokumen      = $total;
                $item->dokumen_lengkap    = $lengkap;
                $item->kelengkapan_berkas = "{$lengkap} dari {$total}";
            }
        }

        foreach ($pemesananCash as $item) {
            $item->kelengkapan_berkas = '-';
            $item->total_dokumen      = 0;
            $item->dokumen_lengkap    = 0;

            if ($item->cash) {
                $total   = $item->cash->dokumen->count();
                $lengkap = $item->cash->dokumen->where('status', 1)->count();

                $item->total_dokumen      = $total;
                $item->dokumen_lengkap    = $lengkap;
                $item->kelengkapan_berkas = "{$lengkap} dari {$total}";
            }
        }
        // dd($pemesananKpr);
        // ==============================
        // 🔹 Return ke View
        // ==============================
        return view('marketing.manage-pemesanan.index', [
            'pemesananKpr'  => $pemesananKpr,
            'pemesananCash' => $pemesananCash,
            'breadcrumbs'   => [
                ['label' => 'Manage Pemesanan', 'url' => route('marketing.managePemesanan.index')],
            ],
        ]);
    }

}
