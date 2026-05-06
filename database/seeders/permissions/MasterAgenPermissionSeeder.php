<?php

namespace Database\Seeders\permissions;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class MasterAgenPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
        public function run(): void
    {
        $permissions = [

            // 🔹 MASTER AGEN - AGEN
            'master-agen.agen.read',
            'master-agen.agen.create',
            'master-agen.agen.update',
            'master-agen.agen.delete',

            // 🔹 MASTER AGEN - FEE AGEN
            'master-agen.fee-agen.read',
            'master-agen.fee-agen.pengajuan',
            'master-agen.fee-agen.aksi-pengajuan',
            'master-agen.fee-agen.cancel-pengajuan',
            'master-agen.fee-agen.nonaktif',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
