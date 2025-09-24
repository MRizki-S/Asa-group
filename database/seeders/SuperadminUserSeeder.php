<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class SuperadminUserSeeder extends Seeder
{
    public function run(): void
    {
        $username = 'superadmin';
        $no_hp    = '6285238617670';
        $rawPassword = 'rahasia45';

        // Pastikan role Super Admin ada
        Role::firstOrCreate(['name' => 'Super Admin']);

        // Buat atau update user; slug dibiarkan kosong -> otomatis diisi booted()
        $user = User::updateOrCreate(
            ['no_hp' => $no_hp],
            [
                'username' => $username,
                'password' => bcrypt($rawPassword),
                'slug'     => null,   // atau cukup hilangkan key 'slug'
                'type'     => 'karyawan',
            ]
        );

        // Assign role Super Admin (idempotent)
        if (! $user->hasRole('Super Admin')) {
            $user->assignRole('Super Admin');
        }

        $this->command->info("Super Admin user created/updated: id={$user->id}, slug={$user->slug}");
    }
}
