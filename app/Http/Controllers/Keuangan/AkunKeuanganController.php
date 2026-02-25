<?php

namespace App\Http\Controllers\Keuangan;

use App\Models\AkunKeuangan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\KategoriAkunKeuangan;

class AkunKeuanganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $akunKeuangan = AkunKeuangan::with(['kategori', 'children.children.children.children']) // tingkatkan sesuai kedalaman
            ->whereNull('parent_id')
            ->orderBy('kode_akun')
            ->get();


        return view('keuangan.akun-keuangan.index', [
            'akunKeuangan' => $akunKeuangan,
            'breadcrumbs' => [
                ['label' => 'Akun Keuangan', 'url' => route('keuangan.akunKeuangan.index')],
            ],
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil akun yang bisa dijadikan parent (is_leaf = false)
        $akunParent = AkunKeuangan::where('is_leaf', false)
            ->orderBy('kode_akun')
            ->get();

        // Ambil semua kategori akun
        $kategoriAkun = KategoriAkunKeuangan::orderBy('kode')->get();

        return view('keuangan.akun-keuangan.create', [
            'breadcrumbs' => [
                ['label' => 'Akun Keuangan', 'url' => route('keuangan.akunKeuangan.index')],
                ['label' => 'Tambah Akun Keuangan', 'url' => route('keuangan.akunKeuangan.create')],
            ],
            'akunParent' => $akunParent,
            'kategoriAkun' => $kategoriAkun,
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $data = $request->validate([
            'nama_akun' => 'required|string|max:255',
            'kode_akun' => 'required|string|max:50|unique:akun_keuangan,kode_akun',
            'parent_id' => 'nullable|exists:akun_keuangan,id',
            'kategori_akun_id' => 'required|exists:kategori_akun_keuangan,id',
            'akun_leaf' => 'sometimes|boolean', // checkbox dari form
        ]);

        //  Set field is_leaf dari input akun_leaf
        $data['is_leaf'] = $request->has('akun_leaf');

        AkunKeuangan::create([
            'nama_akun' => $data['nama_akun'],
            'kode_akun' => $data['kode_akun'],
            'parent_id' => $data['parent_id'] ?? null,
            'kategori_akun_id' => $data['kategori_akun_id'],
            'is_leaf' => $data['is_leaf'],
        ]);

        return redirect()->route('keuangan.akunKeuangan.index')
            ->with('success', 'Akun keuangan berhasil ditambahkan.');
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
        // Ambil akun yang diedit
        $akun = AkunKeuangan::findOrFail($id);

        // Ambil akun yang bisa dijadikan parent (is_leaf = false, kecuali dirinya sendiri)
        $akunParent = AkunKeuangan::where('is_leaf', false)
            ->where('id', '!=', $akun->id) // jangan bisa jadi parent dirinya sendiri
            ->orderBy('kode_akun')
            ->get();

        // Ambil semua kategori akun
        $kategoriAkun = KategoriAkunKeuangan::orderBy('kode')->get();

        return view('keuangan.akun-keuangan.edit', [
            'breadcrumbs' => [
                ['label' => 'Akun Keuangan', 'url' => route('keuangan.akunKeuangan.index')],
                ['label' => 'Edit Akun Keuangan', 'url' => route('keuangan.akunKeuangan.edit', $id)],
            ],
            'akun' => $akun,
            'akunParent' => $akunParent,
            'kategoriAkun' => $kategoriAkun,
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validasi input
        $validated = $request->validate([
            'kode_akun' => 'required|string|max:50',
            'nama_akun' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:akun_keuangan,id',
            'kategori_akun_id' => 'required|exists:kategori_akun_keuangan,id',
            'akun_leaf' => 'nullable|boolean',
        ]);

        // Ambil akun yang akan diupdate
        $akun = AkunKeuangan::findOrFail($id);

        // Jangan biarkan parent_id sama dengan id sendiri
        if ($validated['parent_id'] == $akun->id) {
            return back()->withErrors(['parent_id' => 'Parent tidak boleh sama dengan akun sendiri'])->withInput();
        }

        // Update data
        $akun->update([
            'kode_akun' => $validated['kode_akun'],
            'nama_akun' => $validated['nama_akun'],
            'parent_id' => $validated['parent_id'],
            'kategori_akun_id' => $validated['kategori_akun_id'],
            'is_leaf' => $request->has('akun_leaf') ? true : false,
        ]);

        return redirect()->route('keuangan.akunKeuangan.index')
            ->with('success', 'Akun keuangan berhasil diperbarui.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Ambil akun
        $akun = AkunKeuangan::findOrFail($id);

        // Cek apakah punya anak
        if ($akun->children()->count() > 0) {
            return redirect()->route('keuangan.akunKeuangan.index')
                ->with('error', 'Akun ini tidak bisa dihapus karena masih memiliki akun anak.');
        }

        // Hapus akun
        $akun->delete();

        return redirect()->route('keuangan.akunKeuangan.index')
            ->with('success', 'Akun keuangan berhasil dihapus.');
    }
}
