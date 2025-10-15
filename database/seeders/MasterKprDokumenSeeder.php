<?php
namespace Database\Seeders;

use App\Models\MasterBank;
use App\Models\MasterKprDokumen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterKprDokumenSeeder extends Seeder
{
    public function run(): void
    {

        // Nonaktifkan sementara foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Hapus data lama tanpa menghapus struktur & relasi
        MasterKprDokumen::query()->delete();

        // Reset auto increment (biar ID mulai dari 1 lagi)
        DB::statement('ALTER TABLE master_kpr_dokumen AUTO_INCREMENT = 1;');

        // Aktifkan lagi foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // =========================
        // 📂 KATEGORI: DATA DIRI
        // =========================
        $dokumenDataDiri = [
            'KTP',
            'KK',
            'SKET Belum Menikah / Menikah',
            'SKET Belum Memiliki Rumah Kelurahan',
            'FOTO RESMI',
            'SIKASEP',
        ];

        // =========================
        // 📂 KATEGORI: DATA KERJA
        // =========================
        $dokumenDataKerjaUmum = [
            'NPWP',
            'SPT (u/ NPWP >1th)',
            'SLIP GAJI 3BLN TERKAHIR',
            'RK 3 BLN TERAKHIR',
        ];

        // Khusus bank BTN
        $dokumenDataKerjaBTN = [
            'FOTO TEMPAT KERJA (BTN)',
            'FOTO SELFIE DI TEMPAT KERJA (BTN)',
            'DENAH LOKASI KERJA (BTN)',
        ];

        // =========================
        // 📂 KATEGORI: FORM BANK
        // =========================
        $dokumenFormBank = [
            'FLPP TTD PRIBADI',
            'FLPP TTD ATASAN',
        ];

        // =========================
        // 📂 KATEGORI: DEVELOPER
        // =========================
        $dokumenDeveloperUmum = [
            'SERTIF',
            'PBB',
            'PBG',
            'AIR',
            'SITEPLAN',
            'BROSUR + PL',
        ];

        $banks = MasterBank::all();

        foreach ($banks as $bank) {
            $bankNama = strtoupper($bank->kode_bank);

            // ---- DATA DIRI
            foreach ($dokumenDataDiri as $namaDokumen) {
                MasterKprDokumen::create([
                    'bank_id'      => $bank->id,
                    'kategori'     => 'data_diri',
                    'nama_dokumen' => $namaDokumen,
                    'wajib'        => true,
                ]);
            }

            // ---- DATA KERJA
            foreach ($dokumenDataKerjaUmum as $namaDokumen) {
                MasterKprDokumen::create([
                    'bank_id'      => $bank->id,
                    'kategori'     => 'data_kerja',
                    'nama_dokumen' => $namaDokumen,
                    'wajib'        => true,
                ]);
            }

            // Jika bank adalah BTN, tambahkan dokumen ekstra
            if ($bankNama === 'BTN') {
                foreach ($dokumenDataKerjaBTN as $namaDokumen) {
                    MasterKprDokumen::create([
                        'bank_id'      => $bank->id,
                        'kategori'     => 'data_kerja',
                        'nama_dokumen' => $namaDokumen,
                        'wajib'        => true,
                    ]);
                }
            }

            // ---- FORM BANK
            foreach ($dokumenFormBank as $namaDokumen) {
                MasterKprDokumen::create([
                    'bank_id'      => $bank->id,
                    'kategori'     => 'form_bank',
                    'nama_dokumen' => $namaDokumen,
                    'wajib'        => true,
                ]);
            }

            // ---- DEVELOPER
            foreach ($dokumenDeveloperUmum as $namaDokumen) {
                MasterKprDokumen::create([
                    'bank_id'      => $bank->id,
                    'kategori'     => 'developer',
                    'nama_dokumen' => $namaDokumen,
                    'wajib'        => true,
                ]);
            }

        }

        // Tambahkan SLF untuk bank BRI dengan id 3
        MasterKprDokumen::create([
            'bank_id'      => '3',
            'kategori'     => 'developer',
            'nama_dokumen' => 'SLF (BRI)',
            'wajib'        => true,
        ]);
    }
}
