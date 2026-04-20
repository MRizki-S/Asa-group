<?php

namespace Database\Seeders\users;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersKeuanganSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        $users = [

            // ================= KEUANGAN =================
            [
                'role' => 'Staff Akuntansi',
                'data' => [
                    'username' => 'rindang.sak',
                    'nama_lengkap' => 'RINDANG SARI RAHMAWATI',
                    'no_hp' => '6285238617670',
                    'password' => 'rin#sak42',
                    'type' => 'karyawan',
                    'perumahaan_id' => null,
                    'is_global' => true,
                    'tanggal_expired' => null,
                ],
            ],

        ];

        foreach ($users as $item) {

            $user = User::updateOrCreate(
                ['username' => $item['data']['username']],
                [
                    ...$item['data'],
                    'password' => Hash::make($item['data']['password']),
                ]
            );

            $user->assignRole($item['role']);
        }

        $this->command->info('✅ UsersKeuanganSystemSeeder selesai: Staff Akuntansi berhasil dibuat & role di-assign.');
    }
}
