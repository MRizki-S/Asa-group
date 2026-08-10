<?php

namespace Database\Seeders\Permissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class KeuanganUpahHarianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cache permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Upah Harian Tukang - Daftar Pengajuan
            'keuangan.upah-harian-tukang.daftar-pengajuan.read',
            'keuangan.upah-harian-tukang.daftar-pengajuan.detail',
            'keuangan.upah-harian-tukang.daftar-pengajuan.input-bon',
            'keuangan.upah-harian-tukang.daftar-pengajuan.aksi',
            'keuangan.upah-harian-tukang.daftar-pengajuan.export-excel',

            // Upah Harian Tukang - Riwayat Pengajuan
            'keuangan.upah-harian-tukang.riwayat-pengajuan.read',
            'keuangan.upah-harian-tukang.riwayat-pengajuan.detail',
            'keuangan.upah-harian-tukang.riwayat-pengajuan.export-excel',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }
    }
}
