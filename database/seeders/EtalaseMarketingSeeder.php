<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EtalaseMarketingSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Roles (idempotent)
        $roles = [
            'Super Admin',
            'customer',
            'HRD',
            'Admin Umum',
            'Manager Keuangan',
            'Manager Pemasaran',
            'Staff Keuangan',
            'Admin KPR',
            'Sales',
        ];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        // 2) Permissions (Etalase + Marketing user management)
        $permissions = [
            // Etalase > Perumahaan
            'etalase.perumahaan.view',
            'etalase.perumahaan.detail',

            // Etalase > Tahap
            'etalase.tahap.view',
            'etalase.tahap.create',
            'etalase.tahap.edit',
            'etalase.tahap.delete',
            'etalase.tahap.set_types',
            'etalase.tahap.set_kualifikasi',

            // Etalase > Tipe Unit (global)
            'etalase.type.view',
            'etalase.type.create',
            'etalase.type.edit',
            'etalase.type.delete',
            'etalase.type.change_price',
            'etalase.type.approve_price',

            // Etalase > Kualifikasi Blok (global)
            'etalase.kualifikasi.view',
            'etalase.kualifikasi.edit',
            'etalase.kualifikasi.delete',

            // Etalase > Blok
            'etalase.blok.view',
            'etalase.blok.create',
            'etalase.blok.edit',
            'etalase.blok.delete',

            // Etalase > Unit
            'etalase.unit.view',
            'etalase.unit.create',
            'etalase.unit.edit',
            'etalase.unit.delete',

            // Marketing > Akun User
            'marketing.user.list',
            'marketing.user.create',
            'marketing.user.edit',
            'marketing.user.delete',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // 3) Assign permissions

        // A. Super Admin → semua permission
        $allPermissions = Permission::pluck('name')->toArray();
        Role::firstOrCreate(['name' => 'Super Admin'])
            ->syncPermissions($allPermissions);

        // B. Sales → hanya permission khusus
        $salesPermissions = [
            'etalase.perumahaan.view',
            'etalase.perumahaan.detail',
            'etalase.tahap.view',
            'etalase.type.view',
            'etalase.kualifikasi.view',
            'etalase.blok.view',
            'etalase.unit.view',
            'marketing.user.list',
            'marketing.user.create',
            'marketing.user.edit',
            'marketing.user.delete',
        ];
        Role::firstOrCreate(['name' => 'Sales'])
            ->syncPermissions($salesPermissions);

        // C. Role lain dibiarkan default tanpa assignment
    }
}
