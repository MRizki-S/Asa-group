<?php
namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\PemesananUnit;
use App\Models\Perumahaan;

class PindahUnitController extends Controller
{
    public function createPengajuan($id)
    {
        // Ambil data pemesanan unit beserta relasi
        $pemesanan = PemesananUnit::with(['customer', 'perumahaan', 'tahap', 'unit'])
            ->findOrFail($id);
        // dd($pemesanan);

        // Ambil semua perumahaan untuk dropdown
        $allPerumahaan = Perumahaan::select('id', 'nama_perumahaan', 'slug')->get();

        return view('marketing.manage-pemesanan.pengajuan-pindahUnit.create-pengajuan-pindahUnit', [
            'pemesanan'     => $pemesanan,
            'allPerumahaan' => $allPerumahaan,
            'breadcrumbs'   => [
                ['label' => 'Manajemen Pemesanan', 'url' => route('marketing.managePemesanan.index')],
                ['label' => 'Pengajuan Pindah Unit', 'url' => ''],
            ],
        ]);
    }
}
