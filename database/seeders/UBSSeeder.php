<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UBSSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ubs')->insert([
            [
                'nama_ubs' => 'UBS Asa Dreamland',
                'alamat'   => 'Jl. Merpati, Perumnas, Patrang, Kec. Patrang, Kabupaten Jember, Jawa Timur',
            ],
            [
                'nama_ubs' => 'UBS Lembah Hijau Residence',
                'alamat'   => 'Jl. Rinjani, Tegal Bal, Karangrejo, Kec. Sumbersari, Kabupaten Jember, Jawa Timur',
            ],
            [
                'nama_ubs' => 'UBS Mangoon.id',
                'alamat'   => 'Jl. Merpati, Perumnas, Patrang, Kec. Patrang, Kabupaten Jember, Jawa Timur',
            ],
        ]);

        Log::info('UBS Seeder berhasil dijalankan dan data ditambahkan.');
    }
}
