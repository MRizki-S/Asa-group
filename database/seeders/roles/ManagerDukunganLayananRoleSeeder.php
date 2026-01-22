<?php

namespace Database\Seeders\Roles;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Exception;

class ManagerDukunganLayananRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // Create / ambil role
            $role = Role::firstOrCreate([
                'name' => 'Manager Dukungan & Layanan',
                'guard_name' => 'web',
            ]);

            // Permission yang HARUS ADA
            $permissionNames = [

                // Dashboard
                'dashboard.marketing.read',

                // =====================
                // ETALASE
                // =====================
                'etalase.tahap.read',

                'etalase.type-unit.read',

                'etalase.kualifikasi-blok.read',

                'etalase.blok.read',

                'etalase.unit.read',
                'etalase.unit.detail',

                // Perubahan Harga (Etalase)
                'etalase.perubahaan-harga.type-unit.read',
                'etalase.perubahaan-harga.type-unit.action',

                'etalase.perubahaan-harga.tahap-kualifikasi.read',
                'etalase.perubahaan-harga.tahap-kualifikasi.action',

                // =====================
                // MARKETING
                // =====================
                'marketing.pengajuan-pembatalan.read',
                'marketing.pengajuan-pembatalan.action',

                // Setting PPJB
                'marketing.setting-ppjb.read',
                'marketing.setting-ppjb.kelola',
                'marketing.setting-ppjb.kelola.action',
                'marketing.setting-ppjb.kelola.nonaktif',
            ];

            // Ambil permission dari DB
            $permissions = Permission::whereIn('name', $permissionNames)->get();

            // VALIDASI KETAT
            $missingPermissions = collect($permissionNames)
                ->diff($permissions->pluck('name'));

            if ($missingPermissions->isNotEmpty()) {
                throw new Exception(
                    'Seeder Manager Dukungan & Layanan GAGAL. Permission belum terdaftar: ' .
                    $missingPermissions->implode(', ')
                );
            }

            // Assign (atomic)
            $role->syncPermissions($permissions);
        });
    }
}
