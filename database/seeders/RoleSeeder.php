<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Super Admin', // sudah ada akun seeder
            'customer', // akun customer dibuat saat pemesesanan unit difrontend
            'HRD',
            'Admin Umum',
            'Manager Keuangan', // sudah ada akun seeder
            'Manager Pemasaran', // sudah ada akun seeder
            'Staff Keuangan',
            'Admin KPR', // sudah ada akun seeder
            'Sales',    // sudah ada akun seeder
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $this->command->info('✅ RoleSeeder selesai: semua role berhasil dibuat.');
    }
}
