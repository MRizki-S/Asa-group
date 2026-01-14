<?php

namespace Database\Seeders\Master;

use App\Models\Perumahaan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PerumahaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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
