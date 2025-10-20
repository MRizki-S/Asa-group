<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AdminKprSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan role "Admin KPR" sudah ada
        $role = Role::firstOrCreate(['name' => 'Admin KPR']);

        // === Admin KPR ASA ===
        $adminAsa = User::updateOrCreate(
            ['username' => 'admin kpr asa'],
            [
                'username'       => 'admin kpr asa',
                'no_hp'          => '62853',
                'password'       => Hash::make('12345678'),
                'slug'           => Str::slug('admin kpr asa'),
                'type'           => 'karyawan',
                'is_global'      => false,
                'perumahaan_id'  => 1,
            ]
        );
        $adminAsa->syncRoles([$role->name]); // assign role Admin KPR

        // === Admin KPR LHR ===
        $adminLhr = User::updateOrCreate(
            ['username' => 'admin kpr lhr'],
            [
                'username'       => 'admin kpr lhr',
                'no_hp'          => '62854',
                'password'       => Hash::make('12345678'),
                'slug'           => Str::slug('admin kpr lhr'),
                'type'           => 'karyawan',
                'is_global'      => false,
                'perumahaan_id'  => 2,
            ]
        );
        $adminLhr->syncRoles([$role->name]);

        $this->command->info('Admin KPR ASA & LHR berhasil dibuat dan dikaitkan dengan role Admin KPR.');
    }
}
