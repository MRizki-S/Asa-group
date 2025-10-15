<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\PpjbCaraBayar;

class SettingPpjbJsonController extends Controller
{
    public function showByPerumahaan($perumahaanId)
    {
        $setting = PpjbCaraBayar::where('perumahaan_id', $perumahaanId)
            ->where('status_aktif', true)
            ->select('id', 'perumahaan_id', 'jumlah_cicilan', 'minimal_dp')
            ->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada cara bayar aktif untuk perumahaan ini.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id'              => $setting->id,
                'perumahaan_id'   => $setting->perumahaan_id,
                'jumlah_cicilan'  => (int) $setting->jumlah_cicilan,
                'minimal_dp'      => (float) $setting->minimal_dp,
            ],
        ]);
    }
}
