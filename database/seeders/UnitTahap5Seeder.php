<?php

namespace Database\Seeders;

use App\Models\Blok;
use App\Models\Perumahaan;
use App\Models\Tahap;
use App\Models\TahapKualifikasi;
use App\Models\Type;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UnitTahap5Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil Perumahaan Asa Dreamland
        $perumahaan = Perumahaan::where('nama_perumahaan', 'like', '%Asa Dreamland%')->first()
            ?? Perumahaan::find(1);

        if (!$perumahaan) {
            $this->command->error("Perumahaan Asa Dreamland tidak ditemukan!");
            return;
        }

        // 2. Type Unit default (contoh 33/66)
        $type = Type::where('perumahaan_id', $perumahaan->id)->first();
        $typeId = $type ? $type->id : 1;

        // 3. Mapping data per tahap
        $dataPerTahap = [
            'Tahap 5' => [
                'AC-39',
                'AE-39',
                'AE-40',
                'AE-41',
                'AE-42',
                'AF-40',
                'AF-42',
                'AD-23',
            ],
            'Tahap 6' => [
                'P-46',
                'N-30',
                'N-51',
                'N-69',
                'N-70',
                'N-71',
                'O-45',
                'O-46',
                'O-47',
                'O-48',
                'O-49',
                'O-50',
                'O-51',
                'O-52',
                'O-53',
                'O-54',
                'O-56',
                'O-57',
                'O-58',
                'O-59',
                'O-60',
                'O-61',
                'O-63',
                'O-64',
                'O-65',
                'O-66',
                'P-51',
                'P-53',
                'P-55',
                'P-56',
                'P-57',
                'P-58',
                'P-63',
                'P-65',
                'P-67',
            ],
        ];

        $now = Carbon::now();
        $globalIndex = 0;

        foreach ($dataPerTahap as $namaTahap => $unitsData) {
            // Ambil objek Tahap
            $tahap = Tahap::where('perumahaan_id', $perumahaan->id)
                ->where('nama_tahap', $namaTahap)
                ->first();

            if (!$tahap) {
                $this->command->error("{$namaTahap} untuk {$perumahaan->nama_perumahaan} tidak ditemukan!");
                continue;
            }

            // Ambil Tahap Kualifikasi Standar untuk tahap ini
            $tahapKualifikasi = TahapKualifikasi::where('tahap_id', $tahap->id)->first();
            $tahapKualifikasiId = $tahapKualifikasi ? $tahapKualifikasi->id : 1;

            $this->command->info("--- Memproses {$namaTahap} (Total: " . count($unitsData) . " unit) ---");

            foreach ($unitsData as $namaUnit) {
                $globalIndex++;

                // Ambil nama blok dari awalan nama unit sebelum tanda '-'
                $parts = explode('-', $namaUnit);
                $namaBlok = trim($parts[0]);

                // Cari atau buat blok untuk Tahap ini
                $blok = Blok::firstOrCreate(
                    [
                        'perumahaan_id' => $perumahaan->id,
                        'tahap_id'      => $tahap->id,
                        'nama_blok'     => $namaBlok,
                    ]
                );

                // Cek apakah unit sudah ada
                $unitExists = Unit::where('perumahaan_id', $perumahaan->id)
                    ->where('nama_unit', $namaUnit)
                    ->first();

                $time = $now->copy()->addSeconds($globalIndex * 2);

                if ($unitExists) {
                    // Update unit jika sudah ada
                    $unitExists->update([
                        'tahap_id'             => $tahap->id,
                        'blok_id'              => $blok->id,
                        'type_id'              => $typeId,
                        'kualifikasi_dasar'    => 'standar',
                        'luas_kelebihan'       => null,
                        'nominal_kelebihan'    => null,
                        'tahap_kualifikasi_id' => $tahapKualifikasiId,
                        'status_unit'          => 'available',
                        'status_pembangunan'   => 'belum dibangun',
                        'harga_final'          => 166000000,
                    ]);
                    $this->command->info("Unit {$namaUnit} diperbarui (Blok: {$namaBlok}, Tahap: {$namaTahap}, Harga: 166jt).");
                } else {
                    // Buat unit baru
                    Unit::create([
                        'perumahaan_id'        => $perumahaan->id,
                        'tahap_id'             => $tahap->id,
                        'blok_id'              => $blok->id,
                        'type_id'              => $typeId,
                        'nama_unit'            => $namaUnit,
                        'kualifikasi_dasar'    => 'standar',
                        'luas_kelebihan'       => null,
                        'nominal_kelebihan'    => null,
                        'tahap_kualifikasi_id' => $tahapKualifikasiId,
                        'status_unit'          => 'available',
                        'status_pembangunan'   => 'belum dibangun',
                        'harga_final'          => 166000000,
                        'harga_jual'           => null,
                        'created_at'           => $time,
                        'updated_at'           => $time,
                    ]);
                    $this->command->info("Unit {$namaUnit} dibuat baru (Blok: {$namaBlok}, Tahap: {$namaTahap}, Harga: 166jt).");
                }
            }
        }

        $this->command->info("Selesai! Total keseluruhan unit yang diproses: {$globalIndex}");
    }
}
