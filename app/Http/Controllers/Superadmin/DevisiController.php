<?php

namespace App\Http\Controllers\Superadmin;

use App\Models\Devisi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DevisiController extends Controller
{
    public function index()
    {
        $devisis = Devisi::latest()->get();

        return view('superadmin.devisi.index', [
            'devisis' => $devisis,
            'breadcrumbs' => [
                [
                    'label' => 'Devisi',
                    'url' => route('superadmin.devisi.index'),
                ],
            ],
        ]);
    }

    public function create()
    {
        return view('superadmin.devisi.create', [
            'breadcrumbs' => [
                [
                    'label' => 'Devisi',
                    'url' => route('superadmin.devisi.index'),
                ],
                [
                    'label' => 'Tambah Devisi',
                    'url' => route('superadmin.devisi.create'),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_devisi' => 'required|string|max:255|unique:devisi,nama_devisi',
        ], [
            'nama_devisi.required' => 'Nama devisi wajib diisi.',
            'nama_devisi.unique' => 'Nama devisi ini sudah ada.',
        ]);

        Devisi::create([
            'nama_devisi' => $request->nama_devisi,
        ]);

        return redirect()->route('superadmin.devisi.index')
            ->with('success', 'Devisi berhasil dibuat.');
    }

    public function edit($id)
    {
        $devisi = Devisi::findOrFail($id);

        return view('superadmin.devisi.edit', [
            'devisi' => $devisi,
            'breadcrumbs' => [
                [
                    'label' => 'Devisi',
                    'url' => route('superadmin.devisi.index'),
                ],
                [
                    'label' => 'Edit Devisi',
                    'url' => route('superadmin.devisi.edit', $id),
                ],
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $devisi = Devisi::findOrFail($id);

        $request->validate([
            'nama_devisi' => 'required|string|max:255|unique:devisi,nama_devisi,' . $id,
        ], [
            'nama_devisi.required' => 'Nama devisi wajib diisi.',
            'nama_devisi.unique' => 'Nama devisi ini sudah digunakan.',
        ]);

        $devisi->update([
            'nama_devisi' => $request->nama_devisi,
        ]);

        return redirect()->route('superadmin.devisi.index')
            ->with('success', 'Devisi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $devisi = Devisi::findOrFail($id);
        
        // Prevent deletion if Devisi has roles
        if ($devisi->roles()->count() > 0) {
            return back()->withErrors(['error' => 'Devisi ini tidak dapat dihapus karena masih dikaitkan dengan beberapa Jabatan/Role.']);
        }

        $devisi->delete();

        return redirect()->route('superadmin.devisi.index')
            ->with('success', 'Devisi berhasil dihapus.');
    }
}
