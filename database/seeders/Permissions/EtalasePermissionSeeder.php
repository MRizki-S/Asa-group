<?php

namespace Database\Seeders\Permissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class EtalasePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            // Dashboard
            'dashboard.marketing.read', // fitur belum selesai

            // Etalase - Tahap
            'etalase.tahap.read',
            'etalase.tahap.create',
            'etalase.tahap.update',
            'etalase.tahap.delete',

            // Tahap → Type Unit (Assign)
            'etalase.tahap-type-unit.assign',
            'etalase.tahap-type-unit.unassign',

            // Tahap → Kualifikasi
            'etalase.tahap-kualifikasi.assign',
            'etalase.tahap-kualifikasi.unassign',
            'etalase.tahap-kualifikasi.pengajuan-perubahaan-harga', // fitur belum selesai

            // Type Unit
            'etalase.type-unit.read',
            'etalase.type-unit.create',
            'etalase.type-unit.update',
            'etalase.type-unit.delete',
            'etalase.type-unit.pengajuan-perubahaan-harga',

            // Kualifikasi Blok
            'etalase.kualifikasi-blok.read',
            'etalase.kualifikasi-blok.create',
            'etalase.kualifikasi-blok.delete',

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

             // Perubahan Harga (ETALASE)
            'etalase.perubahaan-harga.type-unit.read',
            'etalase.perubahaan-harga.type-unit.cancel', // fitur belum selesai
            'etalase.perubahaan-harga.type-unit.action',

            'etalase.perubahaan-harga.tahap-kualifikasi.read', // fitur belum selesai
            'etalase.perubahaan-harga.tahap-kualifikasi.cancel', // fitur belum selesai
            'etalase.perubahaan-harga.tahap-kualifikasi.action', // fitur belum selesai
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
