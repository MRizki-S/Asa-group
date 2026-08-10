<?php

namespace Database\Seeders\Permissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class TargetMarketingPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'target-marketing.target-penjualan.read',
            'target-marketing.target-penjualan.update',
            'target-marketing.anggaran-promosi.read',
            'target-marketing.anggaran-promosi.update',
            'target-marketing.anggaran-promsi.update', // typo fallback
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
