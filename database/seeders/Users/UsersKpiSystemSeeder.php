<?php

namespace Database\Seeders\Users;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersKpiSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'role' => 'Manager Strategi & Kepatuhan',
                'data' => [
                    'username' => 'bagas.msk',
                    'nama_lengkap' => 'AGUSTYAN KRISNA BAGASKARA',
                    'no_hp' => '6289685813512',
                    'password' => 'bagas#msk11',
                    'type' => 'karyawan',
                    'perumahaan_id' => null,
                    'is_global' => true,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Staff Admin Eksekutif',
                'data' => [
                    'username' => 'fina.adk',
                    'nama_lengkap' => 'FINA ATIKA NURMA',
                    'no_hp' => '6289515806753',
                    'password' => 'fina#adk07',
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
    }
}
