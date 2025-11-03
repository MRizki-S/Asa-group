<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterBank;

class MasterBankSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            [
                'nama_bank' => 'Bank Tabungan Negara Syariah',
                'kode_bank' => 'BTNS',
            ],
            [
                'nama_bank' => 'Bank Tabungan Negara',
                'kode_bank' => 'BTN',
            ],
            [
                'nama_bank' => 'Bank Rakyat Indonesia',
                'kode_bank' => 'BRI',
            ],
            [
                'nama_bank' => 'BJS',
                'kode_bank' => 'BJS',
            ],
            [
                'nama_bank' => 'Mandiri',
                'kode_bank' => 'Mandiri',
            ],
            [
                'nama_bank' => 'MEGA SYARIAH',
                'kode_bank' => 'MEGA SYARIAH',
            ],
            [
                'nama_bank' => 'JATIM KONVEN',
                'kode_bank' => 'JATIM KONVEN',
            ],
        ];

        foreach ($banks as $bank) {
            MasterBank::firstOrCreate(
                ['kode_bank' => $bank['kode_bank']], // cek unik berdasarkan kode_bank
                $bank // data yang akan disimpan kalau belum ada
            );
        }

        $this->command->info('✅ MasterBankSeeder: Data master_bank berhasil ditambahkan!');
    }
}
