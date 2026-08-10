<?php

namespace Database\Seeders\Roles;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Exception;

class SuperadminRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // Buat / ambil role
            $role = Role::firstOrCreate([
                'name' => 'Superadmin',
                'guard_name' => 'web',
            ]);

            // Ambil SEMUA permission
            $permissions = Permission::all();

            if ($permissions->isEmpty()) {
                throw new Exception(
                    'Seeder Superadmin GAGAL. Tidak ada permission di database.'
                );
            }

            // Assign semua permission
            $role->syncPermissions($permissions);
        });
    }
}
