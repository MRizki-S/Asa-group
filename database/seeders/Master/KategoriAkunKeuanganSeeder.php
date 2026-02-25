<?php

namespace Database\Seeders\Master;

use App\Models\KategoriAkunKeuangan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KategoriAkunKeuanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        $data = [
            [
                'kode' => '1000',
                'nama' => 'Aset',
                'normal_balance' => 'debit',
                'laporan' => 'neraca',
            ],
            [
                'kode' => '2000',
                'nama' => 'Kewajiban',
                'normal_balance' => 'kredit',
                'laporan' => 'neraca',
            ],
            [
                'kode' => '3000',
                'nama' => 'Ekuitas',
                'normal_balance' => 'kredit',
                'laporan' => 'neraca',
            ],
            [
                'kode' => '4000',
                'nama' => 'Pendapatan',
                'normal_balance' => 'kredit',
                'laporan' => 'laba_rugi',
            ],
            [
                'kode' => '5000',
                'nama' => 'Biaya',
                'normal_balance' => 'debit',
                'laporan' => 'laba_rugi',
            ],
            [
                'kode' => '6000',
                'nama' => 'Harga Pokok Penjualan',
                'normal_balance' => 'debit',
                'laporan' => 'laba_rugi',
            ],
        ];

        foreach ($data as $item) {
            KategoriAkunKeuangan::updateOrCreate(
                ['kode' => $item['kode']],
                $item
            );
        }
    }
}
