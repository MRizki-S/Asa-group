<?php

namespace Database\Seeders\Master;

use App\Models\Devisi;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DevisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $devisis = [
            'Dukungan & Layanan',
            'Marketing',
            'Keuangan',
            'Strategi & Kepatuhan',
            'Operasional',
            'IT / Superadmin'
        ];

        $devisiIds = [];
        foreach ($devisis as $nama) {
            $devisi = Devisi::firstOrCreate([
                'nama_devisi' => $nama
            ]);
            $devisiIds[$nama] = $devisi->id;
        }

        // Map existing roles to devisi
        $roleMapping = [
            'Manager Strategi & Kepatuhan' => 'Strategi & Kepatuhan',
            'Staff Admin Eksekutif' => 'Strategi & Kepatuhan',
            'Project Manager' => 'Marketing',
            'Staff Admin Umum' => 'Marketing',
            'Staff KPR' => 'Marketing',
            'Marketing' => 'Marketing',
            'Staff Akuntansi' => 'Keuangan',
            'Manager Dukungan & Layanan' => 'Dukungan & Layanan',
            'Superadmin' => 'IT / Superadmin'
        ];

        foreach ($roleMapping as $roleName => $devisiName) {
            $role = Role::where('name', $roleName)->first();
            if ($role && isset($devisiIds[$devisiName])) {
                $role->update([
                    'devisi_id' => $devisiIds[$devisiName]
                ]);
            }
        }
    }
}
