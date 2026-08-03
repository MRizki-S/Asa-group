<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\MasterTukang;

class MasterTukangController extends Controller
{
    public function index()
    {
        $masterTukang = MasterTukang::latest()->get();
       return view('gudang.upah-harian-tukang.master-tukang.index', [
            'masterTukang' => $masterTukang,
            'breadcrumbs' => [
                [
                    'label' => 'Master Tukang',
                    'url' => route('gudang.masterTukang.index'),
                ],
            ],
        ]);
    }

    public function create()
    {
        return view('gudang.upah-harian-tukang.master-tukang.create', [
            'breadcrumbs' => [
                [
                    'label' => 'Master Tukang',
                    'url' => route('gudang.masterTukang.index'),
                ],
                [
                    'label' => 'Tambah Tukang',
                    'url' => route('gudang.masterTukang.create'),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_tukang' => 'required|string|max:255|unique:master_tukang,nama_tukang',
            'jenis_referensi' => 'required|in:perumahan,mangoon',
            'gaji_harian_default' => 'required|numeric|min:0',
            'jam_kerja_default' => 'required|integer|min:1|max:24',
            'status' => 'nullable|string',
        ]);

        $lastTukang = MasterTukang::orderBy('id', 'desc')->first();
        if ($lastTukang) {
            $lastId = intval(str_replace('TKG-', '', $lastTukang->kode));
            $newKode = 'TKG-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newKode = 'TKG-0001';
        }

        MasterTukang::create([
            'kode' => $newKode,
            'nama_tukang' => $validated['nama_tukang'],
            'jenis_referensi' => $validated['jenis_referensi'],
            'gaji_harian_default' => $validated['gaji_harian_default'],
            'jam_kerja_default' => $validated['jam_kerja_default'],
            'status' => isset($request->status) && ($request->status === 'active' || $request->status === 'on' || $request->status == 1),
        ]);

        return redirect()->route('gudang.masterTukang.index')
            ->with('success', 'Data Tukang berhasil ditambahkan');
    }

    public function edit($id)
    {
        $tukang = MasterTukang::findOrFail($id);
        return view('gudang.upah-harian-tukang.master-tukang.edit', [
            'tukang' => $tukang,
            'breadcrumbs' => [
                [
                    'label' => 'Master Tukang',
                    'url' => route('gudang.masterTukang.index'),
                ],
                [
                    'label' => 'Edit Tukang',
                    'url' => route('gudang.masterTukang.edit', $id),
                ],
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $tukang = MasterTukang::findOrFail($id);

        $validated = $request->validate([
            'nama_tukang' => 'required|string|max:255|unique:master_tukang,nama_tukang,' . $id,
            'jenis_referensi' => 'required|in:perumahan,mangoon',
            'gaji_harian_default' => 'required|numeric|min:0',
            'jam_kerja_default' => 'required|integer|min:1|max:24',
            'status' => 'nullable|string',
        ]);

        $tukang->update([
            'nama_tukang' => $validated['nama_tukang'],
            'jenis_referensi' => $validated['jenis_referensi'],
            'gaji_harian_default' => $validated['gaji_harian_default'],
            'jam_kerja_default' => $validated['jam_kerja_default'],
            'status' => isset($request->status) && ($request->status === 'active' || $request->status === 'on' || $request->status == 1),
        ]);

        return redirect()->route('gudang.masterTukang.index')
            ->with('success', 'Data Tukang berhasil diperbarui');
    }

    public function destroy($id)
    {
        $tukang = MasterTukang::findOrFail($id);
        $tukang->delete();

        return redirect()->route('gudang.masterTukang.index')
            ->with('success', 'Data Tukang berhasil dihapus');
    }

}
