<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use App\Models\Role;
use App\Models\Devisi;

class RoleHakAksesController extends Controller
{
    public function index()
    {
        $roles = Role::with('devisi')->withCount('permissions')->orderBy('name')->get();
        $totalRoles = $roles->count();
        $totalPermissions = Permission::count();

        return view('superadmin.role-hakAkses.index', [
            'roles' => $roles,
            'totalRoles' => $totalRoles,
            'totalPermissions' => $totalPermissions,
            'breadcrumbs' => [
                [
                    'label' => 'Role dan Hak Akses',
                    'url' => route('superadmin.roleHakAkses.index'),
                ],
            ],
        ]);
    }

    public function create() {
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $this->groupPermissions($permissions);
        $devisis = Devisi::orderBy('nama_devisi')->get();

        return view('superadmin.role-hakAkses.create', [
            'groupedPermissions' => $groupedPermissions,
            'devisis' => $devisis,
            'breadcrumbs' => [
                [
                    'label' => 'Role dan Hak Akses',
                    'url' => route('superadmin.roleHakAkses.index'),
                ],
                [
                    'label' => 'Tambah Role',
                    'url' => route('superadmin.roleHakAkses.create'),
                ],
            ],
        ]);
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::orderBy('name')->get();
        
        $groupedPermissions = $this->groupPermissions($permissions);
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        $devisis = Devisi::orderBy('nama_devisi')->get();

        return view('superadmin.role-hakAkses.edit', [
            'role' => $role,
            'groupedPermissions' => $groupedPermissions,
            'rolePermissions' => $rolePermissions,
            'devisis' => $devisis,
            'breadcrumbs' => [
                [
                    'label' => 'Role dan Hak Akses',
                    'url' => route('superadmin.roleHakAkses.index'),
                ],
                [
                    'label' => 'Edit Role: ' . $role->name,
                    'url' => route('superadmin.roleHakAkses.edit', $id),
                ],
            ],
        ]);
    }

    private function groupPermissions($permissions)
    {
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $count = count($parts);

            $category = $parts[0] ?? 'Other';

            if ($category === 'produksi') {
                $permissionName = $permission->name;
                
                if (in_array($permissionName, [
                    'produksi.master-qc-rap',
                    'produksi.permintaan-dibangun',
                    'produksi.pembangunan-unit'
                ])) {
                    $module = 'properti';
                    if ($permissionName === 'produksi.master-qc-rap') {
                        $permission->custom_label = 'Master QC & RAP';
                    } elseif ($permissionName === 'produksi.permintaan-dibangun') {
                        $permission->custom_label = 'Permintaan Dibangun';
                    } else {
                        $permission->custom_label = 'Pembangunan Unit';
                    }
                } elseif (in_array($permissionName, [
                    'produksi.project-baru',
                    'produksi.pembangunan-proyek'
                ])) {
                    $module = 'kontraktor';
                    if ($permissionName === 'produksi.project-baru') {
                        $permission->custom_label = 'Project Baru';
                    } else {
                        $permission->custom_label = 'Pembangunan Proyek';
                    }
                } elseif (in_array($permissionName, [
                    'produksi.buat-pembangunan-kawasan',
                    'produksi.pembangunan-kawasan'
                ])) {
                    $module = 'kawasan';
                    if ($permissionName === 'produksi.buat-pembangunan-kawasan') {
                        $permission->custom_label = 'Buat Pembangunan';
                    } else {
                        $permission->custom_label = 'Pembangunan Kawasan';
                    }
                } else {
                    $module = 'manajemen upah';
                    if ($permissionName === 'produksi.penamaan-upah') {
                        $permission->custom_label = 'Penamaan Upah';
                    } elseif ($permissionName === 'produksi.upah-properti') {
                        $permission->custom_label = 'Upah Properti';
                    } elseif ($permissionName === 'produksi.upah-kontraktor') {
                        $permission->custom_label = 'Upah Kontraktor';
                    } else {
                        $permission->custom_label = 'Upah Kawasan';
                    }
                }

                $subModule = 'default';
            } else {
                if ($count >= 4) {
                    // Contoh: etalase.perubahaan-harga.type-unit.read
                    $module = $parts[1];
                    $subModule = $parts[2];
                } elseif ($count == 3) {
                    // Contoh: etalase.unit.read
                    $module = $parts[1];
                    $subModule = 'default'; // Modul tanpa sub-modul
                } else {
                    $module = $parts[1] ?? 'General';
                    $subModule = 'default';
                }
            }

            $groupedPermissions[$category][$module][$subModule][] = $permission;
        }
        return $groupedPermissions;
    }



    public function store(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string|max:255|unique:roles,name',
            'devisi_id' => 'nullable|exists:devisi,id',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'role_name.required' => 'Nama role wajib diisi.',
            'role_name.unique' => 'Nama role sudah ada.',
            'permissions.required' => 'Pilih minimal satu hak akses.',
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $request->role_name,
                'devisi_id' => $request->devisi_id,
                'guard_name' => 'web'
            ]);

            // Ambil instance permission berdasarkan ID agar Spatie tidak menganggap ID sebagai nama
            $permissions = Permission::findMany($request->permissions);
            $role->syncPermissions($permissions);

            DB::commit();

            return redirect()->route('superadmin.roleHakAkses.index')
                ->with('success', "Role '{$role->name}' dan hak aksesnya berhasil disimpan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'role_name' => 'required|string|max:255|unique:roles,name,' . $id,
            'devisi_id' => 'nullable|exists:devisi,id',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'role_name.required' => 'Nama role wajib diisi.',
            'role_name.unique' => 'Nama role sudah digunakan.',
            'permissions.required' => 'Pilih minimal satu hak akses.',
        ]);

        DB::beginTransaction();
        try {
            $role->update([
                'name' => $request->role_name,
                'devisi_id' => $request->devisi_id,
            ]);

            // Sync permissions
            $permissions = Permission::findMany($request->permissions);
            $role->syncPermissions($permissions);

            DB::commit();

            return redirect()->route('superadmin.roleHakAkses.index')
                ->with('success', "Role '{$role->name}' berhasil diperbarui.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Safety check: Jangan hapus role superadmin jika ada
        if (strtolower($role->name) === 'superadmin') {
            return back()->withErrors(['error' => 'Role Superadmin tidak dapat dihapus demi keamanan sistem.']);
        }

        // Cek apakah role sedang digunakan oleh user
        if ($role->users()->count() > 0) {
            return back()->withErrors(['error' => 'Role ini tidak dapat dihapus karena masih digunakan oleh beberapa pengguna.']);
        }

        $role->delete();

        return redirect()->route('superadmin.roleHakAkses.index')
            ->with('success', "Role '{$role->name}' berhasil dihapus.");
    }
}


