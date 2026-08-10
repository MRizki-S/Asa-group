<?php

namespace Database\Seeders\Roles;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Exception;

class StaffKprRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // Role
            $role = Role::firstOrCreate([
                'name' => 'Staff KPR',
                'guard_name' => 'web',
            ]);

            // Permission yang boleh diakses
            $permissionNames = [

                // Dashboard
                'dashboard.marketing.read',

                // ETALASE (View Only)
                'etalase.tahap.read',

                'etalase.type-unit.read',

                'etalase.kualifikasi-blok.read',

                'etalase.blok.read',

                'etalase.unit.read',
                'etalase.unit.detail',

                // MARKETING
                // Customer
                'marketing.customer.read',
                'marketing.customer.create',
                'marketing.customer.update',
                'marketing.customer.delete',

                // Pemesanan Unit
                'marketing.pemesanan-unit.read',
                'marketing.pemesanan-unit.create',

                // Kelola Pemesanan
                'marketing.kelola-pemesanan.read',
                'marketing.kelola-pemesanan.tagihan.read',
                'marketing.kelola-pemesanan.print-ppjb',
                'marketing.kelola-pemesanan.read-berkas',
                'marketing.kelola-pemesanan.update-berkas',

                // Pengajuan
                'marketing.pengajuan-pemesanan.read',
                'marketing.pengajuan-pemesanan.detail',
                'marketing.pengajuan-pemesanan.action',

                'marketing.pengajuan-pembatalan.read',

                // Adendum
                'marketing.adendum.pengajuan-adendum.read',
                'marketing.adendum.pengajuan-adendum.action',
                'marketing.adendum.create',

                'marketing.adendum.list-adendum.read',
                'marketing.adendum.list-adendum.detail',
                'marketing.adendum.list-adendum.delete',

                // Setting PPJB
                'marketing.setting-ppjb.read',
            ];

            // Ambil permission
            $permissions = Permission::whereIn('name', $permissionNames)->get();

            // Validasi ketat
            $missing = collect($permissionNames)
                ->diff($permissions->pluck('name'));

            if ($missing->isNotEmpty()) {
                throw new Exception(
                    'Seeder Staff KPR GAGAL. Permission belum tersedia: ' .
                    $missing->implode(', ')
                );
            }

            // Assign
            $role->syncPermissions($permissions);
        });
    }
}
