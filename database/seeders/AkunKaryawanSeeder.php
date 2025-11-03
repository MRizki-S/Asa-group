<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AkunKaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        // #1 Super Admin
        $superadmin = User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'nama_lengkap'   => 'superadmin kalcerrzz',
                'no_hp'          => '6285236222236',
                'password'       => Hash::make('rahasia45'),
                'slug'           => Str::slug('superadmin'),
                'type'           => 'karyawan',
                'is_global'      => true,
            ]
        );
        $superadmin->assignRole('Super Admin');


        // #2 Admin KPR ASA
        $adminKprAsa = User::firstOrCreate(
            ['username' => 'admin kpr asa'],
            [
                'nama_lengkap'  => 'Admin KPR ASA',
                'no_hp'         => '62852',
                'password'      => Hash::make('12345678'),
                'slug'          => Str::slug('admin kpr asa'),
                'type'          => 'karyawan',
                'is_global'     => false,
                'perumahaan_id' => 1,
            ]
        );
        $adminKprAsa->assignRole('Admin KPR');

        // Admin KPR LHR
        $adminKprLhr = User::firstOrCreate(
            ['username' => 'admin kpr lhr'],
            [
                'nama_lengkap'  => 'Admin KPR LHR',
                'no_hp'         => '62852',
                'password'      => Hash::make('12345678'),
                'slug'          => Str::slug('admin kpr lhr'),
                'type'          => 'karyawan',
                'is_global'     => false,
                'perumahaan_id' => 2,
            ]
        );
        $adminKprLhr->assignRole('Admin KPR');


        // #3 Sales (Global)
        $salesList = [
            ['nama_lengkap' => 'Nizar', 'username' => 'nizar'],
            ['nama_lengkap' => 'Riski', 'username' => 'riski'],
            ['nama_lengkap' => 'Zakiyah', 'username' => 'zakiyah'],
            ['nama_lengkap' => 'Devi', 'username' => 'devi'],
        ];

        foreach ($salesList as $sales) {
            $user = User::firstOrCreate(
                ['username' => $sales['username']],
                [
                    'nama_lengkap'   => $sales['nama_lengkap'],
                    'no_hp'          => '6285238617670',
                    'password'       => Hash::make('12345678'),
                    'slug'           => Str::slug($sales['username']),
                    'type'           => 'karyawan',
                    'perumahaan_id'  => null,
                    'is_global'      => true,
                ]
            );
            $user->assignRole('Sales');
        }


        // #4 Manager Keuangan (ASA)
        $managerKeuangan = User::firstOrCreate(
            ['username' => 'manager keuangan'],
            [
                'nama_lengkap'  => 'HENI HANDAYANI',
                'no_hp'         => '6285238617670',
                'password'      => Hash::make('12345678'),
                'slug'          => Str::slug('manager keuangan'),
                'type'          => 'karyawan',
                'perumahaan_id' => 1,
                'is_global'     => false,
            ]
        );
        $managerKeuangan->assignRole('Manager Keuangan');


        // #5 Manager Pemasaran ASA
        $managerPemasaranAsa = User::firstOrCreate(
            ['username' => 'manager pemasaran asa'],
            [
                'nama_lengkap'  => 'NURBIYANTI',
                'no_hp'         => '6285238617670',
                'password'      => Hash::make('12345678'),
                'slug'          => Str::slug('manager pemasaran asa'),
                'type'          => 'karyawan',
                'perumahaan_id' => 1,
                'is_global'     => false,
            ]
        );
        $managerPemasaranAsa->assignRole('Manager Pemasaran');

        // Manager Pemasaran LHR
        $managerPemasaranLhr = User::firstOrCreate(
            ['username' => 'manager pemasaran lhr'],
            [
                'nama_lengkap'  => 'Manager Pemasaran LHR',
                'no_hp'         => '6285238617670',
                'password'      => Hash::make('12345678'),
                'slug'          => Str::slug('manager pemasaran lhr'),
                'type'          => 'karyawan',
                'perumahaan_id' => 2,
                'is_global'     => false,
            ]
        );
        $managerPemasaranLhr->assignRole('Manager Pemasaran');

        $this->command->info('✅ UserSeeder selesai: akun karyawan berhasil dibuat dan role ditautkan.');
    }
}
