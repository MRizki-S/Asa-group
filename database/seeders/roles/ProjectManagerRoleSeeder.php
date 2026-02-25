<?php

namespace Database\Seeders\Roles;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Exception;

class ProjectManagerRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // Role
            $role = Role::firstOrCreate([
                'name' => 'Project Manager',
                'guard_name' => 'web',
            ]);

            // Permission wajib
            $permissionNames = [
                // Dashboard
                'dashboard.marketing.read',

                // ETALASE
                // Tahap
                'etalase.tahap.read',
                'etalase.tahap.create',
                'etalase.tahap.update',
                'etalase.tahap.delete',

                // Tahap → Type Unit
                'etalase.tahap-type-unit.assign',
                'etalase.tahap-type-unit.unassign',

                // Tahap → Kualifikasi
                'etalase.tahap-kualifikasi.assign',
                'etalase.tahap-kualifikasi.unassign',
                'etalase.tahap-kualifikasi.pengajuan-perubahaan-harga',

                // Type Unit
                'etalase.type-unit.read',
                'etalase.type-unit.pengajuan-perubahaan-harga',

                // Kualifikasi Blok
                'etalase.kualifikasi-blok.read',

                // Blok
                'etalase.blok.read',
                'etalase.blok.create',
                'etalase.blok.update',
                'etalase.blok.delete',

                // Unit
                'etalase.unit.read',
                'etalase.unit.create',
                'etalase.unit.detail',
                'etalase.unit.update',
                'etalase.unit.delete',

                // Perubahan Harga (Etalase)
                'etalase.perubahaan-harga.type-unit.read',
                'etalase.perubahaan-harga.type-unit.cancel',

                'etalase.perubahaan-harga.tahap-kualifikasi.read',
                'etalase.perubahaan-harga.tahap-kualifikasi.cancel',

                // MARKETING
                // Customer
                'marketing.customer.read',

                // Pemesanan Unit
                'marketing.pemesanan-unit.read',
                'marketing.pemesanan-unit.create',

                // Kelola Pemesanan
                'marketing.kelola-pemesanan.read',
                'marketing.kelola-pemesanan.tagihan.read',
                'marketing.kelola-pemesanan.read-berkas',

                // Pengajuan
                'marketing.pengajuan-pemesanan.read',
                'marketing.pengajuan-pemesanan.detail',

                'marketing.pengajuan-pembatalan.read',

                // Adendum
                'marketing.adendum.list-adendum.read',
                'marketing.adendum.list-adendum.detail',

                // Setting PPJB
                'marketing.setting-ppjb.read',
                'marketing.setting-ppjb.kelola',
                'marketing.setting-ppjb.kelola.pengajuan-perubahaan',
                'marketing.setting-ppjb.kelola.cancel',
                'marketing.setting-ppjb.kelola.nonaktif',
            ];

            // Ambil permission
            $permissions = Permission::whereIn('name', $permissionNames)->get();

            // Validasi ketat
            $missing = collect($permissionNames)
                ->diff($permissions->pluck('name'));

            if ($missing->isNotEmpty()) {
                throw new Exception(
                    'Seeder Project Manager GAGAL. Permission belum terdaftar: ' .
                    $missing->implode(', ')
                );
            }

            // Assign
            $role->syncPermissions($permissions);
        });
    }
}
