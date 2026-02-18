<?php

namespace Database\Seeders\Users;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersMarketingSystemSeeder extends Seeder
{
    public function run(): void
    {
        $users = [

            // ================= HUB / GLOBAL =================
            [
                'role' => 'Superadmin',
                'data' => [
                    'username' => 'superadmin',
                    'nama_lengkap' => 'Superadmin',
                    'no_hp' => '6285238617670',
                    'password' => 'rahasia45',
                    'type' => 'karyawan',
                    'perumahaan_id' => null,
                    'is_global' => true,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Manager Dukungan & Layanan',
                'data' => [
                    'username' => 'heni',
                    'nama_lengkap' => 'HENI HANDAYANI',
                    'no_hp' => '6285257782626',
                    'password' => 'heni#mdl29i',
                    'type' => 'karyawan',
                    'perumahaan_id' => null,
                    'is_global' => true,
                    'tanggal_expired' => null,
                ],
            ],

            // ================= PERUMAHAAAN ADL (ID:1) =================
            [
                'role' => 'Project Manager',
                'data' => [
                    'username' => 'project-manager-adl',
                    'nama_lengkap' => 'Project Manager ADL',
                    'no_hp' => '6285238617670',
                    'password' => '12345678',
                    'type' => 'karyawan',
                    'perumahaan_id' => 1,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Staff KPR',
                'data' => [
                    'username' => 'rizqina',
                    'nama_lengkap' => 'RIZQINA CAHYANI',
                    'no_hp' => '6285232221051',
                    'password' => 'rizqina#1kpr67',
                    'type' => 'karyawan',
                    'perumahaan_id' => 1,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Staff Admin Umum',
                'data' => [
                    'username' => 'fina',
                    'nama_lengkap' => 'FINA ATIKA NURMA R',
                    'no_hp' => '6289515806753',
                    'password' => 'fina#adm23fd',
                    'type' => 'karyawan',
                    'perumahaan_id' => 1,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],

            // Marketing ADL
            [
                'role' => 'Marketing',
                'data' => [
                    'username' => 'nizar-marketing',
                    'nama_lengkap' => 'NIZAR MARKETING',
                    'no_hp' => '628990080441',
                    'password' => 'nizar#mrk4dl',
                    'type' => 'karyawan',
                    'perumahaan_id' => 1,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Marketing',
                'data' => [
                    'username' => 'rizki-marketing',
                    'nama_lengkap' => 'RIZKI MARKETING',
                    'no_hp' => '6285238617670',
                    'password' => 'rizkimrk#oadl2',
                    'type' => 'karyawan',
                    'perumahaan_id' => 1,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Marketing',
                'data' => [
                    'username' => 'devi-marketing',
                    'nama_lengkap' => 'DEVI MARKETING',
                    'no_hp' => '6285238617670',
                    'password' => 'devi#eadl39',
                    'type' => 'karyawan',
                    'perumahaan_id' => 1,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Marketing',
                'data' => [
                    'username' => 'zakiyah-marketing',
                    'nama_lengkap' => 'ZAKIYAH MARKETING',
                    'no_hp' => '6285238617670',
                    'password' => 'zakiyah#zaadl20',
                    'type' => 'karyawan',
                    'perumahaan_id' => 1,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],

            // ================= PERUMAHAAAN LHR (ID:2) =================
            [
                'role' => 'Project Manager',
                'data' => [
                    'username' => 'ranu',
                    'nama_lengkap' => 'RANUDIRJO DWI ADI',
                    'no_hp' => '6282244576918',
                    'password' => 'ranu#1lhr30a',
                    'type' => 'karyawan',
                    'perumahaan_id' => 2,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Staff KPR',
                'data' => [
                    'username' => 'oong',
                    'nama_lengkap' => 'OONG YOGA YOSEANO W',
                    'no_hp' => '6282216970611',
                    'password' => 'oong#2kpr73',
                    'type' => 'karyawan',
                    'perumahaan_id' => 2,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Staff Admin Umum',
                'data' => [
                    'username' => 'nadia',
                    'nama_lengkap' => 'NADIA AYU PERMATASARI',
                    'no_hp' => '6282337348205',
                    'password' => 'nadia#adm21h',
                    'type' => 'karyawan',
                    'perumahaan_id' => 2,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],

            // Marketing LHR
            [
                'role' => 'Marketing',
                'data' => [
                    'username' => 'irham-marketing',
                    'nama_lengkap' => 'IRHAM AFANDI',
                    'no_hp' => '6285811640875',
                    'password' => 'irham#irlhr12',
                    'type' => 'karyawan',
                    'perumahaan_id' => 2,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Marketing',
                'data' => [
                    'username' => 'wahyu-marketing',
                    'nama_lengkap' => 'WAHYU FAJAR SUGIYANTO',
                    'no_hp' => '6285238617670',
                    'password' => 'wahyu#yulhr23a',
                    'type' => 'karyawan',
                    'perumahaan_id' => 2,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Marketing',
                'data' => [
                    'username' => 'devilhr-marketing',
                    'nama_lengkap' => 'DEVINTA ADELIA',
                    'no_hp' => '6285856141227',
                    'password' => '12345678',
                    'type' => 'karyawan',
                    'perumahaan_id' => 2,
                    'is_global' => false,
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

        $this->command->info('✅ UsersMarketingSystemSeeder selesai: semua akun karyawan marketing berhasil dibuat & role di-assign.');
    }
}
