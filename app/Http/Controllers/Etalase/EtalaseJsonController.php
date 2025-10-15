<?php
namespace App\Http\Controllers\Etalase;

use App\Http\Controllers\Controller;
use App\Models\Perumahaan;
use App\Models\Tahap;
use App\Models\Unit;

class EtalaseJsonController extends Controller
{
    // endpoint untuk ambil tahap sesuai perumahaan
    public function listByPerumahaan(Perumahaan $perumahaan)
    {
        // otomatis pakai slug karena getRouteKeyName di model Perumahaan
        return response()->json(
            $perumahaan->tahap()->select('id', 'nama_tahap', 'slug')->get()
        );
    }

    // app/Http/Controllers/EtalaseJsonController.php
    // public function getUnitsByTahap($tahapId)
    // {
    //     try {
    //         $units = Unit::where('tahap_id', $tahapId)
    //             ->where('status_unit', 'available') // pastikan ada kolom status
    //             ->get(['id', 'nama_unit']);    // pastikan ada kolom nama_unit

    //         return response()->json($units);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function getUnitsByTahap($tahapId)
    {
        try {
            // Ambil ID unit yang sedang dipakai (jika mode edit)
            $currentUnitId = request()->query('current_unit_id');

            $units = Unit::where('tahap_id', $tahapId)
                ->where(function ($query) use ($currentUnitId) {
                    $query->where('status_unit', 'available'); // default: hanya yang ready

                    // Jika sedang edit dan ada unit lama, tetap tampilkan unit itu
                    if ($currentUnitId) {
                        $query->orWhere('id', $currentUnitId);
                    }
                })
                ->select('id', 'nama_unit')
                ->get();

            return response()->json($units);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getUnitHarga($id)
    {
        $unit = Unit::select('id', 'nama_unit', 'harga_final')->findOrFail($id);
        return response()->json($unit);
    }

}
