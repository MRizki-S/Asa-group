<?php

namespace Database\Seeders;

use App\Models\Blok;
use App\Models\KualifikasiBlok;
use App\Models\MasterBarang;
use App\Models\MasterQcContainer;
use App\Models\MasterQcTugas;
use App\Models\MasterQcUrutan;
use App\Models\MasterRapBahan;
use App\Models\MasterRapUpah;
use App\Models\MasterSatuan;
use App\Models\MasterUpah;
use App\Models\Tahap;
use App\Models\TahapKualifikasi;
use App\Models\TahapType;
use App\Models\Type;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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

        $satuan = [
            ['nama' => 'pcs'],
            ['nama' => 'sak'],
            ['nama' => 'm3'],
            ['nama' => 'batang'],
            ['nama' => 'kg'],
            ['nama' => 'roll'],
            ['nama' => 'dus'],
        ];

        foreach ($satuan as $s) {
            DB::table('master_satuan')->insert(array_merge($s, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $materials = [
            ['kode' => 'MTR-001', 'nama' => 'Semen Gresik 40kg', 'unit' => 'sak', 'is_stock' => 1],
            ['kode' => 'MTR-002', 'nama' => 'Pasir Pasang', 'unit' => 'm3', 'is_stock' => 1],
            ['kode' => 'MTR-003', 'nama' => 'Batu Bata Merah', 'unit' => 'pcs', 'is_stock' => 1],
            ['kode' => 'MTR-004', 'nama' => 'Besi Beton 10mm', 'unit' => 'batang', 'is_stock' => 1],
            ['kode' => 'MTR-005', 'nama' => 'Keramik Lantai 40x40', 'unit' => 'dus', 'is_stock' => 1],
            ['kode' => 'MTR-006', 'nama' => 'Cat Tembok Putih 5kg', 'unit' => 'pcs', 'is_stock' => 1],
            ['kode' => 'MTR-007', 'nama' => 'Kabel Listrik Nym 2x1.5', 'unit' => 'roll', 'is_stock' => 1],
            ['kode' => 'MTR-008', 'nama' => 'Paku Kayu 5cm', 'unit' => 'kg', 'is_stock' => 1],
        ];

        foreach ($materials as $m) {
            // 1. Ambil ID Satuan berdasarkan nama
            $satuanId = DB::table('master_satuan')->where('nama', $m['unit'])->value('id');

            // 2. Insert ke master_barang
            $barangId = DB::table('master_barang')->insertGetId([
                'kode_barang' => $m['kode'],
                'nama_barang' => $m['nama'],
                'base_unit_id' => $satuanId,
                'is_stock' => $m['is_stock'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Insert ke barang_satuan_konversi (Set sebagai Default)
            DB::table('barang_satuan_konversi')->insert([
                'barang_id' => $barangId,
                'satuan_id' => $satuanId,
                'konversi_ke_base' => 1.000,
                'is_default' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Insert ke stock_gudang (Inisialisasi stok 0)
            DB::table('stock_gudang')->insert([
                'barang_id' => $barangId,
                'stock_type' => 'UBS', // Mengikuti contoh data kamu
                'ubs_id' => 1,
                'jumlah_stock' => 0.00,
                'minimal_stock' => 10.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        MasterRapBahan::create([
            'type_id' => 1,
            'master_qc_container_id' => 1,
            'master_qc_urutan_id' => 1,
            'master_barang_id' => 1,
            'jumlah_kebutuhan_standar' => 12,
            'master_satuan_id' => 1,
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
