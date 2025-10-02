<?php
namespace App\Http\Controllers;

use App\Models\Perumahaan;
use Illuminate\Http\Request;

class PerumahaanSelectController extends Controller
{
    public function index()
    {
        // Semua perumahaan
        $perumahaan = Perumahaan::all();

        return view('auth.pilih-perumahaan', compact('perumahaan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'perumahaan_id' => 'required|exists:perumahaan,id',
        ]);

        // Simpan pilihan di session
        $request->session()->put('current_perumahaan_id', $request->perumahaan_id);

        // Jika halaman sebelumnya adalah route pilih perumahaan, redirect ke dashboard
        if ($request->headers->get('referer') === route('perumahaan.select')) {
            return redirect('/')->with('success', 'Perumahaan berhasil dipilih.');
        }

        // Kalau bukan dari halaman pilih perumahaan (misal dropdown), kembali ke halaman sebelumnya
        return back()->with('success', 'Perumahaan berhasil dipilih.');
    }

}
