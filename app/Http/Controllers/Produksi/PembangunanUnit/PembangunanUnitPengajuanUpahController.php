<?php

namespace App\Http\Controllers\Produksi\PembangunanUnit;

use App\Http\Controllers\Controller;
use App\Models\PembangunanUnitUpahPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembangunanUnitPengajuanUpahController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'pembangunan_unit_id' => 'required',
            'pembangunan_unit_qc_id' => 'required',
            'items' => 'required|array|min:1',
            'items.*.nominal_pengajuan' => 'required|numeric|min:1',
            'items.*.catatan_pengawas' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->items as $item) {
                PembangunanUnitUpahPengajuan::create([
                    'pembangunan_unit_id' => $request->pembangunan_unit_id,
                    'pembangunan_unit_qc_id' => $request->pembangunan_unit_qc_id,
                    'pembangunan_unit_rap_upah_id' => $item['pembangunan_unit_rap_upah_id'],
                    'nama_upah' => $item['nama_upah'],
                    'nominal_diajukan' => $item['nominal_pengajuan'],
                    'catatan_pengawas' => $item['catatan_pengawas'] ?? null,
                    'tanggal_diajukan' => now(),
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Pengajuan upah berhasil dikirim.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
