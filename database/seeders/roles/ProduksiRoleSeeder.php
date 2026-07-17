<?php

namespace Database\Seeders\Roles;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class ProduksiRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Roles Definition
            $roles = [
                'Manager Produksi' => [
                    'produksi.master-qc-rap',
                    'produksi.permintaan-dibangun',
                    'produksi.buat-pembangunan-kawasan',
                    'produksi.penamaan-upah',
                    'produksi.upah-properti',
                    'produksi.upah-kontraktor',
                    'produksi.upah-kawasan',
                ],
                'Pengawas Kawasan' => [
                    'produksi.pembangunan-kawasan',
                ],
                'Pengawas Proyek Mangoon' => [
                    'produksi.pembangunan-proyek',
                ],
                'Pengawas Unit' => [
                    'produksi.pembangunan-unit',
                ],
                'Manager Proyek Mangoon' => [
                    'produksi.project-baru',
                    'produksi.penamaan-upah',
                    'produksi.upah-kontraktor',
                ],
                'SPV Drafting, Teknis & Estimasi' => [
                    'produksi.pembangunan-unit',
                    'produksi.permintaan-dibangun',
                ],
            ];

            // 2. Create Roles & Sync Permissions
            foreach ($roles as $roleName => $permissionNames) {
                $role = Role::firstOrCreate([
                    'name' => $roleName,
                    'guard_name' => 'web',
                ]);

                $permissions = Permission::whereIn('name', $permissionNames)->get();
                $role->syncPermissions($permissions);
            }

            // 3. Assign all new permissions to Superadmin
            $superadmin = Role::where('name', 'Superadmin')->first();
            if ($superadmin) {
                $allProduksiPermissions = Permission::where('name', 'like', 'produksi.%')->get();
                $superadmin->givePermissionTo($allProduksiPermissions);
            }

            // 4. Create Test Users for Produksi Roles
            $testUsers = [
                [
                    'username' => 'pengawas_kawasan',
                    'nama_lengkap' => 'Pengawas Kawasan Test',
                    'no_hp' => '6281111111111',
                    'password' => 'rahasia45',
                    'type' => 'karyawan',
                    'is_global' => true,
                ],
                [
                    'username' => 'pengawas_proyek',
                    'nama_lengkap' => 'Pengawas Proyek Test',
                    'no_hp' => '6282222222222',
                    'password' => 'rahasia45',
                    'type' => 'karyawan',
                    'is_global' => true,
                ],
                [
                    'username' => 'pengawas_unit',
                    'nama_lengkap' => 'Pengawas Unit Test',
                    'no_hp' => '6283333333333',
                    'password' => 'rahasia45',
                    'type' => 'karyawan',
                    'is_global' => true,
                ],
                [
                    'username' => 'manager_produksi',
                    'nama_lengkap' => 'Manager Produksi Test',
                    'no_hp' => '6284444444444',
                    'password' => 'rahasia45',
                    'type' => 'karyawan',
                    'is_global' => true,
                ],
                [
                    'username' => 'manager_proyek',
                    'nama_lengkap' => 'Manager Proyek Test',
                    'no_hp' => '6285555555555',
                    'password' => 'rahasia45',
                    'type' => 'karyawan',
                    'is_global' => true,
                ],
            ];

            $roleMapping = [
                'pengawas_kawasan' => 'Pengawas Kawasan',
                'pengawas_proyek' => 'Pengawas Proyek Mangoon',
                'pengawas_unit' => 'Pengawas Unit',
                'manager_produksi' => 'Manager Produksi',
                'manager_proyek' => 'Manager Proyek Mangoon',
            ];

            foreach ($testUsers as $uData) {
                $roleName = $roleMapping[$uData['username']];
                $user = \App\Models\User::updateOrCreate(
                    ['username' => $uData['username']],
                    array_merge($uData, [
                        'password' => \Illuminate\Support\Facades\Hash::make($uData['password'])
                    ])
                );
                $user->syncRoles([$roleName]);
            }
        });
    }
}
