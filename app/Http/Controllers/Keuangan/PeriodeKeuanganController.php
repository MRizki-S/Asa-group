<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\PeriodeKeuangan;
use Illuminate\Http\Request;

class PeriodeKeuanganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $periodeKeuangan = PeriodeKeuangan::all();

        return view('keuangan.periode.index', [
            'periodeKeuangan' => $periodeKeuangan,
            'breadcrumbs'     => [
                ['label' => 'Periode Keuangan', 'url' => route('keuangan.periodeKeuangan.index')],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'nama_periode' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        // Simpan ke database
        $periode = PeriodeKeuangan::create([
            'nama_periode' => $validated['nama_periode'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
        ]);

        // Redirect dengan pesan sukses
        return redirect()->route('keuangan.periodeKeuangan.index')
            ->with('success', 'Periode keuangan berhasil dibuat.');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $periode = PeriodeKeuangan::findOrFail($id);
        $periode->delete();

        return redirect()->route('keuangan.periodeKeuangan.index')
            ->with('success', 'Periode keuangan berhasil dihapus.');
    }
}
