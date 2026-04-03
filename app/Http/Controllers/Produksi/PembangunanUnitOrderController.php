<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\PembangunanUnit;
use App\Models\PembangunanUnitBarangOrder;
use App\Models\PembangunanUnitBarangOrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PembangunanUnitOrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'pembangunan_unit_id' => 'required',
            'pembangunan_unit_qc_id' => 'required',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required',
            'items.*.nama_barang' => 'required',
            'items.*.satuan_id' => 'required',
            'items.*.satuan' => 'required',
            'items.*.jumlah_input' => 'required|numeric|min:0.001',
            'items.*.faktor_konversi' => 'required|numeric|min:0.001',
            'jenis_order' => 'required|string|in:stock,direct'
        ]);

        try {
            DB::beginTransaction();

            $pembangunanUnit = PembangunanUnit::findOrFail($request->pembangunan_unit_id);

            $order = PembangunanUnitBarangOrder::create([
                'pembangunan_unit_id' => $request->pembangunan_unit_id,
                'pembangunan_unit_qc_id' => $request->pembangunan_unit_qc_id,
                'jenis_order' => $request->jenis_order,
                'tanggal_diajukan' => now(),
                'status_order' => 'menunggu',
                'catatan' => $request->catatan,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                PembangunanUnitBarangOrderDetail::create([
                    'order_id' => $order->id,
                    'barang_id' => $item['barang_id'],
                    'nama_barang' => $item['nama_barang'],
                    'satuan_id' => $item['satuan_id'],
                    'satuan' => $item['satuan'],
                    'ubs_id' => $pembangunanUnit->perumahaan_id,
                    'rap_bahan_id' => $item['pembangunan_unit_rap_bahan_id'] ?? null,
                    'jumlah_input' => $item['jumlah_input'],
                    'jumlah_base'   => (float)$item['faktor_konversi'] * (float)$item['jumlah_input'],
                    'alasan_permintaan_tidak_sesuai_rap' => $item['alasan'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Permintaan barang berhasil dikirim.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request) {}
}
