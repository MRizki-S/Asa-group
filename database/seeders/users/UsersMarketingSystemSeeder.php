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
                    'username' => 'heni.mdl',
                    'nama_lengkap' => 'HENI HANDAYANI',
                    'no_hp' => '6285257782626',
                    'password' => 'heni#mdl90',
                    'type' => 'karyawan',
                    'perumahaan_id' => null,
                    'is_global' => true,
                    'tanggal_expired' => null,
                ],
            ],

            // ================= PERUMAHAAAN ADL (ID:1) =================
            [
                'role' => 'Proyek Manager',
                'data' => [
                    'username' => 'nurbi.pm',
                    'nama_lengkap' => 'Nurbiyanti PM',
                    'no_hp' => '6285238617670',
                    'password' => 'nurbi#pm33',
                    'type' => 'karyawan',
                    'perumahaan_id' => 1,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Staff KPR',
                'data' => [
                    'username' => 'rizqina.skpr',
                    'nama_lengkap' => 'RIZQINA SKPR',
                    'no_hp' => '6285232221051',
                    'password' => 'rizqina#skpr21',
                    'type' => 'karyawan',
                    'perumahaan_id' => 1,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Administrasi Proyek',
                'data' => [
                    'username' => 'fina.adp',
                    'nama_lengkap' => 'FINA ATIKA Admin Proyek',
                    'no_hp' => '6289515806753',
                    'password' => 'fina#adp32',
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
                    'username' => 'nizar.mrk',
                    'nama_lengkap' => 'NIZAR MARKETING',
                    'no_hp' => '628990080441',
                    'password' => 'nizar#mrk21',
                    'type' => 'karyawan',
                    'perumahaan_id' => 1,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Marketing',
                'data' => [
                    'username' => 'rizki.mrk',
                    'nama_lengkap' => 'RIZKI MARKETING',
                    'no_hp' => '6285238617670',
                    'password' => 'rizki#mrk24',
                    'type' => 'karyawan',
                    'perumahaan_id' => 1,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Marketing',
                'data' => [
                    'username' => 'zakiyah.mrk',
                    'nama_lengkap' => 'ZAKIYAH MARKETING',
                    'no_hp' => '6285238617670',
                    'password' => 'zakiyah#mrk44',
                    'type' => 'karyawan',
                    'perumahaan_id' => 1,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Marketing',
                'data' => [
                    'username' => 'amar.mrk',
                    'nama_lengkap' => 'MOCHAMAD AMARUDDIN MARKETING',
                    'no_hp' => '6285238617670',
                    'password' => 'amar#mrk52',
                    'type' => 'karyawan',
                    'perumahaan_id' => 1,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Marketing',
                'data' => [
                    'username' => 'dewi.mrk',
                    'nama_lengkap' => 'NARULITA DEWI MARKETING',
                    'no_hp' => '6285238617670',
                    'password' => 'dewi#mrk02',
                    'type' => 'karyawan',
                    'perumahaan_id' => 1,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Marketing',
                'data' => [
                    'username' => 'andini.mrk',
                    'nama_lengkap' => 'ANDINI NABILAH MARKETING',
                    'no_hp' => '6285238617670',
                    'password' => 'andini#mrk03',
                    'type' => 'karyawan',
                    'perumahaan_id' => 1,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            

            // ================= PERUMAHAAAN LHR (ID:2) =================
            [
                'role' => 'Proyek Manager',
                'data' => [
                    'username' => 'ranu.pm',
                    'nama_lengkap' => 'RANUDIRJO DWI ADI PM',
                    'no_hp' => '6282244576918',
                    'password' => 'ranu#pm81',
                    'type' => 'karyawan',
                    'perumahaan_id' => 2,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Staff KPR',
                'data' => [
                    'username' => 'oong.pm',
                    'nama_lengkap' => 'OONG YOGA YOSEANO W',
                    'no_hp' => '6282216970611',
                    'password' => 'oong#pm04',
                    'type' => 'karyawan',
                    'perumahaan_id' => 2,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Administrasi Proyek',
                'data' => [
                    'username' => 'nadia.adp',
                    'nama_lengkap' => 'NADIA AYU PERMATASARI',
                    'no_hp' => '6282337348205',
                    'password' => 'nadia#adp04',
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
                    'username' => 'irham.mrk',
                    'nama_lengkap' => 'IRHAM AFANDI MARKETING',
                    'no_hp' => '6285811640875',
                    'password' => 'irham#mrk20',
                    'type' => 'karyawan',
                    'perumahaan_id' => 2,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Marketing',
                'data' => [
                    'username' => 'iyan.mrk',
                    'nama_lengkap' => 'WAHYU FAJAR SUGIYANTO MARKETING',
                    'no_hp' => '6285238617670',
                    'password' => 'iyan#mrk31',
                    'type' => 'karyawan',
                    'perumahaan_id' => 2,
                    'is_global' => false,
                    'tanggal_expired' => null,
                ],
            ],
            [
                'role' => 'Marketing',
                'data' => [
                    'username' => 'devi.mrk',
                    'nama_lengkap' => 'FANDO DEVI MARKETING',
                    'no_hp' => '6285856141227',
                    'password' => 'devi#mrk84',
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
