<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Perumahaan;

class PerumahaanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama_perumahaan' => 'Asa Dreamland',
                'alamat' => 'Jl. Merpati, Perumnas, Patrang, Kec. Patrang, Kabupaten Jember, Jawa Timur',
            ],
            [
                'nama_perumahaan' => 'Lembah Hijau Residence',
                'alamat' => 'Jl. Rinjani, Tegal Bal, Karangrejo, Kec. Sumbersari, Kabupaten Jember, Jawa Timur',
            ],
        ];

        foreach ($data as $item) {
            Perumahaan::create($item);
        }

         $this->command->info('✅ PerumahaanSeeder selesai: semua perumahaan berhasil dibuat.');
    }
}
