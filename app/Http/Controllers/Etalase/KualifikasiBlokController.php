<?php
namespace App\Http\Controllers\Etalase;

use App\Http\Controllers\Controller;
use App\Models\KualifikasiBlok;
use Illuminate\Http\Request;

class KualifikasiBlokController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kualifikasiBlok = KualifikasiBlok::latest()->get();

        return view('Etalase.kualifikasi-blok.index', [
            'kualifikasiBlok' => $kualifikasiBlok,
            'breadcrumbs'     => [
                ['label' => 'Kualifikasi Blok', 'url' => route('kualifikasi-blok.index')],
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
        $request->validate([
            'nama_kualifikasi_blok' => 'required|string|unique:kualifikasi_blok,nama_kualifikasi_blok',
        ], [
            'nama_kualifikasi_blok.required' => 'Nama kualifikasi blok wajib diisi',
            'nama_kualifikasi_blok.string'   => 'Nama kualifikasi blok harus berupa string',
            'nama_kualifikasi_blok.unique'   => 'Nama kualifikasi blok sudah ada',
        ]);

        KualifikasiBlok::create([
            'nama_kualifikasi_blok' => $request->nama_kualifikasi_blok,
        ]);

        return redirect()->route('kualifikasi-blok.index')->with('success', 'Kualifikasi blok berhasil ditambahkan');
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
        $kualifikasi = KualifikasiBlok::findOrFail($id);

        $kualifikasi->delete();

        return redirect()->route('kualifikasi-blok.index')->with('success','Kualifikasi blok berhasil dihapus');
    }
}
