<?php
namespace App\Http\Controllers\Etalase;

use App\Http\Controllers\Controller;
use App\Models\Perumahaan;
use App\Models\Tahap;
use Illuminate\Http\Request;

class PerumahaanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perumahaan = Perumahaan::all();

        return view('Etalase.perumahaan.index', [
            'perumahaan'  => $perumahaan,
            'breadcrumbs' => [
                ['label' => 'Perumahaan', 'url' => route('perumahaan.index')],
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
        //
    }

/**
 * Display the specified resource.
 */
    public function show(Perumahaan $perumahaan)
    {
        // get tahap yang ada pada perumahaan tersebut
        // $tahaps = $perumahaan->tahap()->latest()->get();
        $tahaps = $perumahaan->tahap()
            ->with(['types', 'kualifikasiBlok:id,nama_kualifikasi_blok'])               // ambil relasi type
            ->withCount(['blok', 'unit'])
            ->latest()
            ->get();
        // dd(vars: $tahaps);
        return view('etalase.perumahaan.show', [
            'perumahaan'  => $perumahaan,
            'tahaps'      => $tahaps,
            'breadcrumbs' => [
                ['label' => 'Perumahaan', 'url' => route('perumahaan.index')],
                ['label' => $perumahaan->nama_perumahaan, 'url' => ''], // current page kosong url
            ],
        ]);
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
        //
    }
}
