<?php

namespace Database\Seeders\Permissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ProduksiPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'produksi.master-qc-rap',
            'produksi.permintaan-dibangun',
            'produksi.pembangunan-unit',
            'produksi.project-baru',
            'produksi.pembangunan-proyek',
            'produksi.buat-pembangunan-kawasan',
            'produksi.pembangunan-kawasan',
            'produksi.penamaan-upah',
            'produksi.upah-properti',
            'produksi.upah-kontraktor',
            'produksi.upah-kawasan',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
