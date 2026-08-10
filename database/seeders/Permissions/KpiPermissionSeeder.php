<?php

namespace Database\Seeders\Permissions;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class KpiPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Master KPI
            'kpi.master-kpi.read',
            'kpi.master-kpi.create',
            'kpi.master-kpi.update',
            'kpi.master-kpi.delete',

            // Kpi User
            'kpi.kpi-user.read',
            'kpi.kpi-user.create',
            'kpi.kpi-user.update',
            'kpi.kpi-user.update-simpan-nilai',
            'kpi.kpi-user.minta-riview',
            'kpi.kpi-user.detail',
            'kpi.kpi-user.export',
            'kpi.kpi-user.delete',

            // Kpi Riview
            'kpi.kpi-riview.read',
            'kpi.kpi-riview.riview-skor',
            'kpi.kpi-riview.simpan-hasil-riview',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
