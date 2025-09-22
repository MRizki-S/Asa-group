<?php
namespace App\Http\Controllers\Etalase;

use App\Http\Controllers\Controller;
use App\Models\Tahap;
use App\Models\TahapType;
use Illuminate\Http\Request;

class TahapTypeController extends Controller
{
    public function store(Request $request, Tahap $tahap)
    {
        // dd($tahap);
        // Validasi
        $validated = $request->validate([
            'type_id' => [
                'required',
                // cek unik di pivot: tidak boleh ada kombinasi tahap_id + type_id yang sama
                function ($attribute, $value, $fail) use ($tahap) {
                    if ($tahap->types()->where('type_id', $value)->exists()) {
                        $fail('Tipe Unit sudah terhubung dengan Tahap ini.');
                    }
                },
            ],
        ]);
        // dd($validated);

        // Tambahkan ke pivot table
        $tahap->types()->attach($validated['type_id']);

        return redirect()->back()
            ->with('success', 'Tipe Unit berhasil ditambahkan ke ' . $tahap->nama_tahap . '.');

    }

    public function destroy($id)
    {
        $pivot = TahapType::findOrFail($id); // otomatis 404 kalau nggak ada
        // dd($pivot);
        $pivot->delete(); // hapus record pivot

        return redirect()->back()->with('success', 'Relasi Tipe Unit berhasil dihapus.');
    }

}
