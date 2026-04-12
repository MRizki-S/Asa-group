<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KpiIndicatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $indicators = [
            // 1. KEPATUHAN PERSEN
            ['tipe_perhitungan' => 'KEPATUHAN', 'tipe_indikator' => 'range', 'skor' => 100, 'batas_bawah' => 100, 'batas_atas' => null, 'option' => null, 'nilai' => null],
            ['tipe_perhitungan' => 'KEPATUHAN', 'tipe_indikator' => 'range', 'skor' => 85,  'batas_bawah' => 95,  'batas_atas' => 99,  'option' => null, 'nilai' => null],
            ['tipe_perhitungan' => 'KEPATUHAN', 'tipe_indikator' => 'range', 'skor' => 70,  'batas_bawah' => 90,  'batas_atas' => 94,  'option' => null, 'nilai' => null],
            ['tipe_perhitungan' => 'KEPATUHAN', 'tipe_indikator' => 'range', 'skor' => 0,   'batas_bawah' => null,   'batas_atas' => 89,  'option' => null, 'nilai' => null],

            // 2. DEVIASI BUDGET
            ['tipe_perhitungan' => 'DEVIASI_BUDGET', 'tipe_indikator' => 'range', 'skor' => 100, 'batas_bawah' => null,   'batas_atas' => 100, 'option' => null, 'nilai' => null],
            ['tipe_perhitungan' => 'DEVIASI_BUDGET', 'tipe_indikator' => 'range', 'skor' => 90,  'batas_bawah' => 101, 'batas_atas' => 103, 'option' => null, 'nilai' => null],
            ['tipe_perhitungan' => 'DEVIASI_BUDGET', 'tipe_indikator' => 'range', 'skor' => 80,  'batas_bawah' => 104, 'batas_atas' => 105, 'option' => null, 'nilai' => null],
            ['tipe_perhitungan' => 'DEVIASI_BUDGET', 'tipe_indikator' => 'range', 'skor' => 70,  'batas_bawah' => 106, 'batas_atas' => 110, 'option' => null, 'nilai' => null],
            ['tipe_perhitungan' => 'DEVIASI_BUDGET', 'tipe_indikator' => 'range', 'skor' => 60,  'batas_bawah' => 111, 'batas_atas' => null, 'option' => null, 'nilai' => null],

            // 3. SELISIH STOK
            ['tipe_perhitungan' => 'SELISIH_STOK', 'tipe_indikator' => 'range', 'skor' => 100, 'batas_bawah' => null, 'batas_atas' => 0, 'option' => null, 'nilai' => null],
            ['tipe_perhitungan' => 'SELISIH_STOK', 'tipe_indikator' => 'range', 'skor' => 95,  'batas_bawah' => 0.1, 'batas_atas' => 1, 'option' => null, 'nilai' => null],
            ['tipe_perhitungan' => 'SELISIH_STOK', 'tipe_indikator' => 'range', 'skor' => 90,  'batas_bawah' => 1.1, 'batas_atas' => 2, 'option' => null, 'nilai' => null],
            ['tipe_perhitungan' => 'SELISIH_STOK', 'tipe_indikator' => 'range', 'skor' => 80,  'batas_bawah' => 2.1, 'batas_atas' => 3, 'option' => null, 'nilai' => null],
            ['tipe_perhitungan' => 'SELISIH_STOK', 'tipe_indikator' => 'range', 'skor' => 70,  'batas_bawah' => 3.1, 'batas_atas' => null, 'option' => null, 'nilai' => null],

            // 4. KONDISI BARANG GUDANG (KONDISI_LANGSUNG)
            ['tipe_perhitungan' => 'KONDISI_LANGSUNG', 'tipe_indikator' => 'select', 'skor' => null, 'batas_bawah' => null, 'batas_atas' => null, 'option' => 'Tidak ada kehilangan / kerusakan', 'nilai' => 100],
            ['tipe_perhitungan' => 'KONDISI_LANGSUNG', 'tipe_indikator' => 'select', 'skor' => null,  'batas_bawah' => null, 'batas_atas' => null, 'option' => 'Ada kerusakan kecil', 'nilai' => 85],
            ['tipe_perhitungan' => 'KONDISI_LANGSUNG', 'tipe_indikator' => 'select', 'skor' => null,  'batas_bawah' => null, 'batas_atas' => null, 'option' => 'Ada barang hilang', 'nilai' => 60],
            ['tipe_perhitungan' => 'KONDISI_LANGSUNG', 'tipe_indikator' => 'select', 'skor' => null,  'batas_bawah' => null, 'batas_atas' => null, 'option' => 'Tidak ada laporan saat', 'nilai' => 40],

            // 5. AKUMULASI NILAI ABSENSI (AKKUMULASI_NILAI)
            ['tipe_perhitungan' => 'AKKUMULASI_NILAI', 'tipe_indikator' => 'select', 'skor' => null, 'batas_bawah' => null, 'batas_atas' => null, 'option' => '100% akurat', 'nilai' => 100],
            ['tipe_perhitungan' => 'AKKUMULASI_NILAI', 'tipe_indikator' => 'select', 'skor' => null,  'batas_bawah' => null, 'batas_atas' => null, 'option' => 'Ada sedikit keterlambatan', 'nilai' => 80],
            ['tipe_perhitungan' => 'AKKUMULASI_NILAI', 'tipe_indikator' => 'select', 'skor' => null,  'batas_bawah' => null, 'batas_atas' => null, 'option' => 'Sering terlambat', 'nilai' => 70],
            ['tipe_perhitungan' => 'AKKUMULASI_NILAI', 'tipe_indikator' => 'select', 'skor' => null,  'batas_bawah' => null, 'batas_atas' => null, 'option' => 'Banyak data hilang', 'nilai' => 50],
        ];

        foreach ($indicators as $data) {
            DB::table('kpi_indicators')->insert(array_merge($data, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
