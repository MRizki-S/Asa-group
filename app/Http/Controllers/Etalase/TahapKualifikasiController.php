<?php
namespace App\Http\Controllers\Etalase;

use App\Http\Controllers\Controller;
use App\Models\Tahap;
use App\Models\TahapKualifikasi;
use Illuminate\Http\Request;

class TahapKualifikasiController extends Controller
{
    public function store(Request $request, Tahap $tahap)
    {
        // Validasi
        $validated = $request->validate([
            'kualifikasi_blok_id' => 'required|exists:kualifikasi_blok,id',
            'nominal_tambahan'    => 'nullable|numeric',
        ]);

        // Jika nominal kosong, kirim ke server sebagai 0
        $nominal = $validated['nominal_tambahan'] ?? 0;

        // Simpan ke database, misal ke tabel tahap_kualifikasi
        $tahapKualifikasi                      = new TahapKualifikasi();
        $tahapKualifikasi->tahap_id            = $request->tahap->id; // pastikan route model binding
        $tahapKualifikasi->kualifikasi_blok_id = $validated['kualifikasi_blok_id'];
        $tahapKualifikasi->nominal_tambahan    = $nominal;
        $tahapKualifikasi->save();

        return redirect()->back()->with('success', 'Kualifikasi berhasil ditambahkan ke ' . $tahap->nama_tahap . '.');
    }

    public function update(Request $request, $id) {
        // dd($request->all());
        // Validasi
        $validated = $request->validate([
            'kualifikasi_blok_id' => 'required|exists:kualifikasi_blok,id',
            'nominal_tambahan'    => 'nullable|numeric',
        ]);

        // Jika nominal kosong, kirim ke server sebagai 0
        $nominal = $validated['nominal_tambahan'] ?? 0;

        // Update ke database, misal ke tabel tahap_kualifikasi
        $tahapKualifikasi = TahapKualifikasi::findOrFail($id);
        $tahapKualifikasi->kualifikasi_blok_id = $validated['kualifikasi_blok_id'];
        $tahapKualifikasi->nominal_tambahan    = $nominal;
        $tahapKualifikasi->save();

        return redirect()->back()->with('success', 'Relasi Kualifikasi Blok berhasil diperbarui.');
    }


    public function destroy($id) {
        // dd($id);
        $tahap = TahapKualifikasi::findOrFail($id);
        $tahap->delete();

        return redirect()->back()->with('success','Relasi Kualifikasi Blok berhasil dihapus.');
    }
}
