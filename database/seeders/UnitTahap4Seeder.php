<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UnitTahap4Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $start = Carbon::now();

        for ($i = 2; $i <= 27; $i++) {
            $time = $start->copy()->addSeconds($i * 3); // tiap unit beda 3 detik
            Unit::create([
                'perumahaan_id' => 1,
                'tahap_id' => 1,
                'blok_id' => 2,
                'type_id' => 3,
                'nama_unit' => "CF-$i",
                'slug' => "cf-$i-" . Str::random(5),
                'kualifikasi_dasar' => 'standar',
                'luas_kelebihan' => null,
                'nominal_kelebihan' => null,
                'tahap_kualifikasi_id' => 1,
                'status_unit' => 'available',
                'harga_final' => 170000000,
                'harga_jual' => null,
                'created_at' => $time,
                'updated_at' => $time,
            ]);
        }
    }
}
