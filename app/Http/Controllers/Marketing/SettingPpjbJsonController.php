<?php
namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\PpjbCaraBayar;

class SettingPpjbJsonController extends Controller
{
    public function showByPerumahaan($perumahaanId)
    {
        $settings = PpjbCaraBayar::where('perumahaan_id', $perumahaanId)
            ->where('status_aktif', true)
            ->select('id', 'perumahaan_id', 'jumlah_cicilan', 'minimal_dp', 'jenis_pembayaran', 'nama_cara_bayar')
            ->get();

        if ($settings->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada cara bayar aktif untuk perumahaan ini.',
            ], 404);
        }

        // pisahkan berdasarkan jenis pembayaran
        $cash = $settings->where('jenis_pembayaran', 'CASH')->values();
        $kpr  = $settings->where('jenis_pembayaran', 'KPR')->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'cash' => $cash,
                'kpr'  => $kpr,
            ],
        ]);
    }

    // public function showByPerumahaan($perumahaanId)
    // {
    //

    //     if ($caraBayarList->isEmpty()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Belum ada cara bayar aktif untuk perumahaan ini.',
    //         ], 404);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data'    => $caraBayarList,
    //     ]);
    // }

}
