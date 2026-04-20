<?php

namespace App\Http\Controllers\Superadmin;

use App\Models\User;
use App\Models\Ubs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AkunKaryawanController extends Controller
{

    public function index()
    {
        $akunKaryawan = User::with(['roles', 'perumahaan'])
            ->where('type', 'karyawan')
            ->orderByRaw('perumahaan_id IS NULL DESC')
            ->latest()
            ->get();

        return view('superadmin.akun-karyawan.index', [
            'akunKaryawan' => $akunKaryawan,
            'breadcrumbs' => [
                [
                    'label' => 'Akun Karyawan',
                    'url' => route('superadmin.akunKaryawan.index'),
                ],
            ],
        ]);
    }

    public function create()
    {
        $ubs = Ubs::orderBy('nama_ubs')->get();
        $roles = Role::orderBy('name')->get();

        return view('superadmin.akun-karyawan.create', [
            'ubs' => $ubs,
            'roles' => $roles,
            'breadcrumbs' => [
                [
                    'label' => 'Akun Karyawan',
                    'url' => route('superadmin.akunKaryawan.index'),
                ],
                [
                    'label' => 'Tambah Akun',
                    'url' => route('superadmin.akunKaryawan.create'),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8',
            'no_hp' => 'required|string|max:20',
            'perumahaan_id' => 'required',
            'role' => 'required|exists:roles,name',
        ], [
            'username.unique' => 'Username ini sudah digunakan.',
            'password.min' => 'Password minimal 8 karakter.',
            'role.required' => 'Jabatan wajib dipilih.',
        ]);

        DB::beginTransaction();
        try {
            $isGlobal = ($request->perumahaan_id === 'HUB') ? 1 : 0;
            $perumahaanId = ($request->perumahaan_id === 'HUB') ? null : $request->perumahaan_id;

            $user = User::create([
                'nama_lengkap' => $request->nama_lengkap,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'no_hp' => '62' . ltrim($request->no_hp, '0'),
                'type' => 'karyawan',
                'perumahaan_id' => $perumahaanId,
                'is_global' => $isGlobal,
                'company_id' => 1, // Default company
            ]);

            // Assign Role
            $user->assignRole($request->role);

            DB::commit();

            return redirect()->route('superadmin.akunKaryawan.index')
                ->with('success', "Akun karyawan '{$user->nama_lengkap}' berhasil dibuat.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }
    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);
        $ubs = Ubs::orderBy('nama_ubs')->get();
        $roles = Role::orderBy('name')->get();

        return view('superadmin.akun-karyawan.edit', [
            'user' => $user,
            'ubs' => $ubs,
            'roles' => $roles,
            'breadcrumbs' => [
                [
                    'label' => 'Akun Karyawan',
                    'url' => route('superadmin.akunKaryawan.index'),
                ],
                [
                    'label' => 'Edit Akun: ' . $user->nama_lengkap,
                    'url' => route('superadmin.akunKaryawan.edit', $id),
                ],
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'password' => 'nullable|string|min:8',
            'no_hp' => 'required|string|max:20',
            'perumahaan_id' => 'required',
            'role' => 'required|exists:roles,name',
        ], [
            'username.unique' => 'Username ini sudah digunakan.',
            'password.min' => 'Password minimal 8 karakter.',
            'role.required' => 'Jabatan wajib dipilih.',
        ]);

        DB::beginTransaction();
        try {
            $isGlobal = ($request->perumahaan_id === 'HUB') ? 1 : 0;
            $perumahaanId = ($request->perumahaan_id === 'HUB') ? null : $request->perumahaan_id;

            $data = [
                'nama_lengkap' => $request->nama_lengkap,
                'username' => $request->username,
                'no_hp' => '62' . ltrim($request->no_hp, '0'),
                'perumahaan_id' => $perumahaanId,
                'is_global' => $isGlobal,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            // Sync Role
            $user->syncRoles([$request->role]);

            DB::commit();

            return redirect()->route('superadmin.akunKaryawan.index')
                ->with('success', "Akun karyawan '{$user->nama_lengkap}' berhasil diperbarui.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return redirect()->route('superadmin.akunKaryawan.index')
                ->with('success', "Akun karyawan '{$user->nama_lengkap}' berhasil dihapus.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus data: ' . $e->getMessage()]);
        }
    }
}


