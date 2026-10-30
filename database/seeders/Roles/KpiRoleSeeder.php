<?php

namespace Database\Seeders\Roles;

use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class KpiRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $manager = Role::whereIn('name', ['MANAJER STRATEGI & KEPATUHAN', 'Manager Strategi & Kepatuhan'])->first() 
            ?? Role::create(['name' => 'MANAJER STRATEGI & KEPATUHAN', 'guard_name' => 'web']);

        $staff = Role::whereIn('name', ['STAF ADMINISTRASI EKSEKUTIF', 'Staff Admin Eksekutif'])->first() 
            ?? Role::create(['name' => 'STAF ADMINISTRASI EKSEKUTIF', 'guard_name' => 'web']);

        $managerPermissions = [
            'kpi.master-kpi.read',
            'kpi.kpi-user.read',
            'kpi.kpi-user.update',
            'kpi.kpi-user.detail',
            'kpi.kpi-user.export',
            'kpi.kpi-user.review',
            'kpi.kpi-user.minta-revisi',
            'kpi.kpi-user.finalisasi',
        ];

        $staffPermissions = [
            'kpi.master-kpi.read',
            'kpi.master-kpi.create',
            'kpi.master-kpi.update',
            'kpi.master-kpi.delete',
            'kpi.kpi-user.read',
            'kpi.kpi-user.create',
            'kpi.kpi-user.update',
            'kpi.kpi-user.update-simpan-nilai',
            'kpi.kpi-user.minta-riview',
            'kpi.kpi-user.detail',
            'kpi.kpi-user.export',
            'kpi.kpi-user.delete',
        ];

        // Manager
        $createManagerPermissions = Permission::whereIn('name', $managerPermissions)->get();

        // VALIDASI KETAT
        $missingPermissions = collect($managerPermissions)
            ->diff($createManagerPermissions->pluck('name'));

        if ($missingPermissions->isNotEmpty()) {
            throw new Exception(
                'Seeder Manager Strategi & Kepatuhan GAGAL. Permission belum terdaftar: ' .
                    $missingPermissions->implode(', ')
            );
        }

        // Assign without wiping existing other domain permissions
        $manager->givePermissionTo($createManagerPermissions);

        // Staff
        $createStaffPermissions = Permission::whereIn('name', $staffPermissions)->get();

        // VALIDASI KETAT
        $missingPermissions = collect($staffPermissions)
            ->diff($createStaffPermissions->pluck('name'));

        if ($missingPermissions->isNotEmpty()) {
            throw new Exception(
                'Seeder Staff Admin Eksekutif GAGAL. Permission belum terdaftar: ' .
                    $missingPermissions->implode(', ')
            );
        }

        // Assign without wiping existing other domain permissions
        $staff->givePermissionTo($createStaffPermissions);
    }
}
