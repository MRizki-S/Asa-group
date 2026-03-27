<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\MasterSatuan;
use Illuminate\Http\Request;

class MasterSatuanBarangController extends Controller
{
    // index view list 
    public function index()
    {
        $masterSatuan = MasterSatuan::all();

        return view('gudang.master-gudang.master-satuan.index', [
            'masterSatuan' => $masterSatuan,
            'breadcrumbs' => [
                [
                    'label' => 'Master Satuan Barang',
                    'url' => route('gudang.masterSatuanBarang.index'),
                ],
            ],
        ]);
    }

    public function create()
    {
        return view('gudang.master-gudang.master-satuan.create', [
            'breadcrumbs' => [
                [
                    'label' => 'Master Satuan Barang',
                    'url' => route('gudang.masterSatuanBarang.index'),
                ],
                [
                    'label' => 'Tambah',
                    'url' => route('gudang.masterSatuanBarang.create'),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_satuan' => 'required|unique:master_satuan,nama',
        ]);

        MasterSatuan::create([
            'nama' => strtolower($request->nama_satuan)
        ]);

        return redirect()->route('gudang.masterSatuanBarang.index')
            ->with('success', 'Satuan berhasil ditambahkan');
    }

    public function edit(string $id)
    {
        $satuan = MasterSatuan::findOrFail($id);

        return view('gudang.master-gudang.master-satuan.edit', [
            'satuan' => $satuan,
            'breadcrumbs' => [
                [
                    'label' => 'Master Satuan Barang',
                    'url' => route('gudang.masterSatuanBarang.index'),
                ],
                [
                    'label' => 'Edit',
                    'url' => route('gudang.masterSatuanBarang.edit', $id),
                ],
            ],
        ]);
    }

    public function update(Request $request, string $id)
    {
        $satuan = MasterSatuan::findOrFail($id);

        $request->validate([
            'nama_satuan' => 'required|unique:master_satuan,nama,' . $id,
        ]);

        $satuan->update([
            'nama' => strtolower($request->nama_satuan)
        ]);

        return redirect()->route('gudang.masterSatuanBarang.index')
            ->with('success', 'Satuan berhasil diupdate');
    }

    public function destroy(string $id)
    {
        $satuan = MasterSatuan::findOrFail($id);

        // Check if there are any relationship restrictions
        if ($satuan->barangKonversi()->exists() || $satuan->barangBase()->exists()) {
            return back()->with('error', 'Gagal menghapus! Satuan ini sedang digunakan pada Master Barang atau Konversi Satuan.');
        }

        $satuan->delete();

        return redirect()->route('gudang.masterSatuanBarang.index')
            ->with('success', 'Satuan berhasil dihapus');
    }
}
