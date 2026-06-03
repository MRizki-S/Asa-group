<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\MasterUpah;
use Illuminate\Http\Request;

class PenamaanUpahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allMasterUpah = MasterUpah::with('rapUpah')->latest()->get();

        return view('Produksi.penamaan-upah.index', [
            'allMasterUpah' => $allMasterUpah,
            'breadcrumbs' => [['label' => 'Penamaan Upah', 'url' => route('produksi.masterUpah.index')]],
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
        $request->validate(
            [
                'nama_upah' => 'required|string|max:255|unique:master_upah,nama_upah',
            ],
            [
                'nama_upah.unique' => 'Nama upah ini sudah ada di database.',
            ],
        );

        MasterUpah::create([
            'nama_upah' => $request->nama_upah,
        ]);

        return redirect()->route('produksi.masterUpah.index')->with('success', 'Penamaan upah baru berhasil ditambahkan.');
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
        $masterUpah = MasterUpah::findOrFail($id);

        $request->validate([
            'nama_upah' => 'required|string|max:255|unique:master_upah,nama_upah,' . $id,
        ]);

        $masterUpah->update([
            'nama_upah' => $request->nama_upah,
        ]);

        return redirect()->route('produksi.masterUpah.index')->with('success', 'Penamaan upah berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $masterUpah = MasterUpah::findOrFail($id);

        if ($masterUpah->rapUpah()->exists()) {
            return back()->with('error', 'Gagal menghapus! Data ini masih digunakan di dalam Rencana Anggaran Biaya (RAP).');
        }

        $masterUpah->delete();

        return redirect()->route('produksi.masterUpah.index')->with('success', 'Penamaan upah berhasil dihapus.');
    }
}
