<?php

namespace App\Http\Controllers\Superadmin;

use App\Models\Karyawan;
use App\Models\Ubs;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $ubsFilter = $request->input('ubs_id');

        $karyawans = Karyawan::with(['role', 'ubs', 'users'])
            ->when($ubsFilter, function ($query, $ubsFilter) {
                if ($ubsFilter === 'HUB') {
                    return $query->whereNull('ubs_id');
                }
                return $query->where('ubs_id', $ubsFilter);
            })
            ->latest()
            ->get();

        $ubs = Ubs::orderBy('nama_ubs')->get();

        return view('superadmin.karyawan.index', [
            'karyawans' => $karyawans,
            'ubs' => $ubs,
            'breadcrumbs' => [
                [
                    'label' => 'Karyawan',
                    'url' => route('superadmin.karyawan.index'),
                ],
            ],
        ]);
    }

    public function create()
    {
        $ubs = Ubs::orderBy('nama_ubs')->get();
        $roles = Role::orderBy('name')->get();
        
        // Get all users of type 'karyawan'
        $users = User::where('type', 'karyawan')
            ->orderBy('nama_lengkap')
            ->get();

        return view('superadmin.karyawan.create', [
            'ubs' => $ubs,
            'roles' => $roles,
            'users' => $users,
            'breadcrumbs' => [
                [
                    'label' => 'Karyawan',
                    'url' => route('superadmin.karyawan.index'),
                ],
                [
                    'label' => 'Tambah Karyawan',
                    'url' => route('superadmin.karyawan.create'),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'ubs_id' => 'nullable',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ], [
            'nama.required' => 'Nama karyawan wajib diisi.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'role_id.required' => 'Jabatan/Role wajib dipilih.',
        ]);

        $ubsId = ($request->ubs_id === 'HUB' || empty($request->ubs_id)) ? null : $request->ubs_id;

        DB::transaction(function () use ($request, $ubsId) {
            $karyawan = Karyawan::create([
                'nama' => $request->nama,
                'no_hp' => '62' . ltrim($request->no_hp, '0'),
                'role_id' => $request->role_id,
                'ubs_id' => $ubsId,
            ]);

            if ($request->has('user_ids')) {
                User::whereIn('id', $request->user_ids)->update([
                    'karyawan_id' => $karyawan->id
                ]);
            }
        });

        return redirect()->route('superadmin.karyawan.index')
            ->with('success', 'Data Karyawan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $ubs = Ubs::orderBy('nama_ubs')->get();
        $roles = Role::orderBy('name')->get();
        
        // Get all users of type 'karyawan'
        $users = User::where('type', 'karyawan')
            ->orderBy('nama_lengkap')
            ->get();

        return view('superadmin.karyawan.edit', [
            'karyawan' => $karyawan,
            'ubs' => $ubs,
            'roles' => $roles,
            'users' => $users,
            'breadcrumbs' => [
                [
                    'label' => 'Karyawan',
                    'url' => route('superadmin.karyawan.index'),
                ],
                [
                    'label' => 'Edit Karyawan',
                    'url' => route('superadmin.karyawan.edit', $id),
                ],
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $karyawan = Karyawan::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'ubs_id' => 'nullable',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ], [
            'nama.required' => 'Nama karyawan wajib diisi.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'role_id.required' => 'Jabatan/Role wajib dipilih.',
        ]);

        $ubsId = ($request->ubs_id === 'HUB' || empty($request->ubs_id)) ? null : $request->ubs_id;

        $noHp = $request->no_hp;
        if (!str_starts_with($noHp, '62')) {
            $noHp = '62' . ltrim($noHp, '0');
        }

        DB::transaction(function () use ($request, $karyawan, $ubsId, $noHp) {
            $karyawan->update([
                'nama' => $request->nama,
                'no_hp' => $noHp,
                'role_id' => $request->role_id,
                'ubs_id' => $ubsId,
            ]);

            // Set karyawan_id to null for users previously linked to this karyawan
            User::where('karyawan_id', $karyawan->id)->update([
                'karyawan_id' => null
            ]);

            // Link newly selected users
            if ($request->has('user_ids')) {
                User::whereIn('id', $request->user_ids)->update([
                    'karyawan_id' => $karyawan->id
                ]);
            }
        });

        return redirect()->route('superadmin.karyawan.index')
            ->with('success', 'Data Karyawan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $karyawan->delete();

        return redirect()->route('superadmin.karyawan.index')
            ->with('success', 'Data Karyawan berhasil dihapus.');
    }
}
