<?php

namespace Database\Seeders\Permissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class SuperadminMenuPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        $permissions = [

            // Role
            'superadmin.role.read',
            'superadmin.role.create',
            'superadmin.role.update',
            'superadmin.role.delete',

            // Akun Karyawan
            'superadmin.akun-karyawan.read',
            'superadmin.akun-karyawan.create',
            'superadmin.akun-karyawan.update',
            'superadmin.akun-karyawan.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
