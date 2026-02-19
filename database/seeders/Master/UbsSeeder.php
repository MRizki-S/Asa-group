<?php

namespace Database\Seeders\Master;

use App\Models\Ubs;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UbsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama_ubs' => 'Asa Dreamland',
                'alamat' => 'Jl. Merpati, Perumnas, Patrang, Kec. Patrang, Kabupaten Jember, Jawa Timur'
            ],
            [
                'nama_ubs' => 'Lembah Hijau Residence',
                'alamat' => 'Jl. Rinjani, Tegal Bal, Karangrejo, Kec. Sumbersari, Kabupaten Jember, Jawa Timur'
            ],
            [
                'nama_ubs' => 'Mangoon.id',
                'alamat' => 'Jl. Merpati, Perumnas, Patrang, Kec. Patrang, Kabupaten Jember, Jawa Timur'
            ],
        ];

        foreach ($data as $ubs) {
            Ubs::updateOrCreate(
                ['nama_ubs' => $ubs['nama_ubs']], // unik berdasarkan nama
                $ubs
            );
        }
    }
}
