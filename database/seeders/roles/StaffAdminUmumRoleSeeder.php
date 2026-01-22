<?php

namespace Database\Seeders\Roles;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Exception;

class StaffAdminUmumRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // Role
            $role = Role::firstOrCreate([
                'name' => 'Staff Admin Umum',
                'guard_name' => 'web',
            ]);

            // Permission wajib
            $permissionNames = [
                // Menu Etalase
                'etalase.tahap.read',
                'etalase.tahap.create',
                'etalase.tahap.update',
                'etalase.tahap.delete',

                'etalase.tahap-type-unit.assign',
                'etalase.tahap-type-unit.unassign',

                'etalase.tahap-kualifikasi.assign',
                'etalase.tahap-kualifikasi.unassign',

                'etalase.type-unit.read',
                'etalase.type-unit.create',
                'etalase.type-unit.update',
                'etalase.type-unit.delete',

                'etalase.kualifikasi-blok.read',
                'etalase.kualifikasi-blok.create',
                'etalase.kualifikasi-blok.delete',

                'etalase.blok.read',
                'etalase.blok.create',
                'etalase.blok.update',
                'etalase.blok.delete',

                'etalase.unit.read',
                'etalase.unit.create',
                'etalase.unit.detail',
                'etalase.unit.update',
                'etalase.unit.delete',
            ];

            // Ambil permission
            $permissions = Permission::whereIn('name', $permissionNames)->get();

            // Validasi ketat
            $missing = collect($permissionNames)
                ->diff($permissions->pluck('name'));

            if ($missing->isNotEmpty()) {
                throw new Exception(
                    'Seeder Staff Admin Umum GAGAL. Permission belum terdaftar: ' .
                    $missing->implode(', ')
                );
            }

            // Assign
            $role->syncPermissions($permissions);
        });
    }
}
