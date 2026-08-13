<?php

namespace Database\Seeders\Master;

use App\Models\MasterSupplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterSupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'nama_supplier' => 'MANIK MULIA',
                'kategori_supplier' => 'Semua material proyek',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'BERKAH SEJAHTERA',
                'kategori_supplier' => 'Semua material proyek',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'ADIVA',
                'kategori_supplier' => 'Pipa, klosed, kne, pintu pvc',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'BETON CENTRE',
                'kategori_supplier' => 'Cempolong, roster',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'KEBON JAYA',
                'kategori_supplier' => 'Semua material proyek',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'KEBON JAYA II',
                'kategori_supplier' => 'Semua material proyek',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'TIRTA KENCANA',
                'kategori_supplier' => 'Pipa, cat',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'PUTRA JAYA SENTOSA',
                'kategori_supplier' => 'Keramik, semen',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'BINTANG SURYA',
                'kategori_supplier' => 'Bata merah',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'JEMBER PRIMA',
                'kategori_supplier' => 'Sewa alat berat, truk',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'P. BAGIO',
                'kategori_supplier' => 'Batu, pasir, sirtu',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'MULTI BANGUNAN',
                'kategori_supplier' => 'Paving, genteng',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'RIZQY AGUNG',
                'kategori_supplier' => 'Genteng, wuwung',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'AJAYA',
                'kategori_supplier' => 'Semua material proyek',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'SEMERU',
                'kategori_supplier' => 'Almunium',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'ANUGRAH BAJA',
                'kategori_supplier' => 'Besi, canal, reng, spandek, holo',
                'alamat' => 'Surabaya',
            ],
            [
                'nama_supplier' => 'TENTREM',
                'kategori_supplier' => 'Sewa alat berat, truk',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'RESTU IBU',
                'kategori_supplier' => 'Cempolong',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'TANAH MAS',
                'kategori_supplier' => 'Bata merah',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'PT SUPERIOR',
                'kategori_supplier' => 'Bata ringan, perekat',
                'alamat' => 'Surabaya',
            ],
            [
                'nama_supplier' => 'WAFI',
                'kategori_supplier' => 'Sewa truk',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'P. IMAM',
                'kategori_supplier' => 'Batu, pasir, sirtu',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'BIMA ARYA',
                'kategori_supplier' => 'Genteng, wuwung',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'MUTIARA',
                'kategori_supplier' => 'Cempolong',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'SUMBER INTI PERKASA',
                'kategori_supplier' => 'Perekat , acian',
                'alamat' => 'Surabaya',
            ],
            [
                'nama_supplier' => 'PERSADA JAYA',
                'kategori_supplier' => 'Paving, genteng',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'WAWAN',
                'kategori_supplier' => 'Sewa alat berat, truk',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'KARYA KONTRUKSINDO',
                'kategori_supplier' => 'Paving, genteng',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'ANEKA',
                'kategori_supplier' => 'Almunium',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'LAJUARDI',
                'kategori_supplier' => 'Sewa alat berat, truk',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'SUMBER JAYA',
                'kategori_supplier' => 'Semua material proyek',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'P. HARI',
                'kategori_supplier' => 'Sewa truk',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'PUTRA SEKAWAN',
                'kategori_supplier' => 'Almunium',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'KEBON JAYA III',
                'kategori_supplier' => 'Semua material proyek',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'GLOBAL INTI',
                'kategori_supplier' => 'Semua material proyek',
                'alamat' => 'Banyuwangi',
            ],
            [
                'nama_supplier' => 'SUMBER WARNA',
                'kategori_supplier' => 'Cat',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'SURADI',
                'kategori_supplier' => 'Semen',
                'alamat' => 'Bondowoso',
            ],
            [
                'nama_supplier' => 'P. ANSORI',
                'kategori_supplier' => 'Sewa truk',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'SAHABAT',
                'kategori_supplier' => 'Semua material proyek',
                'alamat' => 'Balung Jember',
            ],
            [
                'nama_supplier' => 'FOKON SURYA',
                'kategori_supplier' => 'Perekat , acian',
                'alamat' => 'Surabaya',
            ],
            [
                'nama_supplier' => 'DUA BERLIN',
                'kategori_supplier' => 'Genteng, wuwung',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'HARTONO',
                'kategori_supplier' => 'Genteng, wuwung',
                'alamat' => 'Jember',
            ],
            [
                'nama_supplier' => 'INTI TRIO',
                'kategori_supplier' => 'Kalsiboard, paku, baut',
                'alamat' => 'Jember',
            ],
        ];

        DB::transaction(function () use ($suppliers) {
            foreach ($suppliers as $data) {
                // Cek apakah supplier dengan nama ini sudah ada
                $existing = MasterSupplier::where('nama_supplier', $data['nama_supplier'])->first();

                if ($existing) {
                    $existing->update([
                        'kategori_supplier' => $data['kategori_supplier'],
                        'alamat'            => $data['alamat'],
                        'status'            => 1,
                    ]);
                } else {
                    // Cari nomor urut berikutnya berdasarkan kode_supplier tertinggi
                    $last = MasterSupplier::orderByDesc('id')->value('kode_supplier');
                    $nextNum = 1;
                    if ($last && preg_match('/SPL-(\d+)/', $last, $m)) {
                        $nextNum = (int) $m[1] + 1;
                    }
                    $kodeSupplier = 'SPL-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

                    MasterSupplier::create([
                        'kode_supplier'     => $kodeSupplier,
                        'nama_supplier'     => $data['nama_supplier'],
                        'kategori_supplier' => $data['kategori_supplier'],
                        'alamat'            => $data['alamat'],
                        'status'            => 1,
                    ]);
                }
            }
        });
    }
}
