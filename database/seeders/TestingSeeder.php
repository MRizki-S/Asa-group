<?php

namespace Database\Seeders;

use App\Models\Blok;
use App\Models\KualifikasiBlok;
use App\Models\MasterQcContainer;
use App\Models\MasterQcTugas;
use App\Models\MasterQcUrutan;
use App\Models\MasterRapBahan;
use App\Models\MasterRapUpah;
use App\Models\MasterUpah;
use App\Models\Tahap;
use App\Models\TahapKualifikasi;
use App\Models\TahapType;
use App\Models\Type;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class TestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tahap::create([
            'perumahaan_id' => 1,
            'nama_tahap' => 'Tahap 1',
            'slug' => 'tahap-1-v4ZTc'
        ]);

        Type::create([
            'perumahaan_id' => 1,
            'nama_type' => 'Type 1',
            'slug' => 'type-1-LgsFl',
            'luas_bangunan' => 45,
            'luas_tanah' => 65,
            'harga_dasar' => 25000000,
            'status_pengajuan' => 'acc'
        ]);

        TahapType::create([
            'tahap_id' => 1,
            'type_id' => 1
        ]);

        KualifikasiBlok::create([
            'nama_kualifikasi_blok' => 'Type Kulaifikasi 1',
            'slug' => 'type-kulaifikasi-1-JI4af',
        ]);

        TahapKualifikasi::create([
            'tahap_id' => 1,
            'kualifikasi_blok_id' => 1,
            'nominal_tambahan' => 1000000,
        ]);

        Blok::create([
            'perumahaan_id' => 1,
            'tahap_id' => 1,
            'nama_blok' => 'A',
            'slug' => 'a-xBIwY',
        ]);

        Unit::create([
            'perumahaan_id' => 1,
            'tahap_id' => 1,
            'blok_id' => 1,
            'type_id' => 1,
            'nama_unit' => 'A-1',
            'slug' => 'a-1-kGmLB',
            'kualifikasi_dasar' => 'standar',
            'tahap_kualifikasi_id' => 1,
            'status_unit' => 'available',
            'harga_final' => 30000000
        ]);

        Role::create([
            'name' => 'Pengawas Proyek'
        ]);

        $pengawas1 =  User::create([
            'username' => 'Pengawas1',
            'nama_lengkap' => 'Aditya',
            'no_hp' => '62903323417621',
            'password' => 'rahasia45',
            'type' => 'karyawan',
            'perumahaan_id' => null,
            'is_global' => true,
            'tanggal_expired' => null,
        ]);

        $pengawas1->assignRole('Pengawas Proyek');

        MasterQcContainer::create([
            'nama_container' => 'Pembangunan Type1',
            'type_id' => 1
        ]);

        MasterQcUrutan::create([
            'master_qc_container_id' =>  1,
            'qc_ke' => 1,
            'nama_qc' => 'QC-1'
        ]);

        MasterQcTugas::create([
            'master_qc_urutan_id' => 1,
            'tugas' => 'keterangan tugas'
        ]);

        MasterRapBahan::create([
            'type_id' => 1,
            'master_qc_container_id' => 1,
            'master_qc_urutan_id' => 1,
            'jumlah_kebutuhan_standar' => 12,
            'satuan' => 'sak',
        ]);

        MasterUpah::create([
            'nama_upah' => 'Gaji Pokok'
        ]);

        MasterRapUpah::create([
            'type_id' => 1,
            'master_qc_container_id' => 1,
            'master_qc_urutan_id' => 1,
            'master_upah_id' => 1,
            'nominal_standar' => 560000
        ]);
    }
}
