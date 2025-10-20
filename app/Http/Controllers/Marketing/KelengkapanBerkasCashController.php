<?php
namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\PemesananUnit;
use App\Models\PemesananUnitCashDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KelengkapanBerkasCashController extends Controller
{
    public function editCash($id)
    {
        $pemesanan = PemesananUnit::with([
            'cash.dokumen.updatedBy',
            'dataDiri',
            'customer',
            'sales', // tambahkan relasi sales
            'unit',  // tambahkan relasi perumahan di dalam unit'
            'perumahaan',
        ])->findOrFail($id);
        // dd($pemesanan);
        $cash = $pemesanan->cash;

        $dokumenList = $cash
            ? PemesananUnitCashDokumen::where('pemesanan_unit_cash_id', $cash->id)
            ->orderBy('nama_dokumen')
            ->get()
            : collect();
        return view('marketing.manage-pemesanan.kelengkapan-berkas.berkas-cash', [
            'pemesanan'   => $pemesanan,
            'dokumenList' => $dokumenList,
            'breadcrumbs' => [
                ['label' => 'Manage Pemesanan', 'url' => route('marketing.managePemesanan.index')],
                ['label' => 'Kelengkapan Berkas Cash', 'url' => ''],
            ],
        ]);
    }

    public function updateCash(Request $request, $id)
    {
        // 🧩 Ambil data pemesanan + relasi cash
        $pemesanan = PemesananUnit::with('cash')->findOrFail($id);
        $cash      = $pemesanan->cash;

        if (! $cash) {
            return back()->with('error', 'Data cash belum tersedia untuk pemesanan ini.');
        }

        // 🗂️ Ambil daftar dokumen yang terkait dengan pemesanan cash ini
        $dokumenList = PemesananUnitCashDokumen::where('pemesanan_unit_cash_id', $cash->id)->get();

        // 🔁 Update status masing-masing dokumen
        foreach ($dokumenList as $dokumen) {
            $namaDokumen = $dokumen->nama_dokumen;
            $statusBaru  = isset($request->dokumen[$namaDokumen]) ? 1 : 0;

            // Update hanya jika ada perubahan status
            if ($dokumen->status != $statusBaru) {
                $dokumen->update([
                    'status'         => $statusBaru,
                    'tanggal_update' => now(),
                    'updated_by'     => Auth::user()->id,
                ]);
            }
        }

        // ✅ Redirect kembali ke halaman berkas cash (tetap di halaman itu)
        return back()->with('success', 'Status dokumen berhasil diperbarui.');
    }

}
