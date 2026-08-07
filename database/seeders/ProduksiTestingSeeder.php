<?php

namespace Database\Seeders;

use App\Models\MasterBarang;
use App\Models\MasterQcContainer;
use App\Models\MasterQcTugas;
use App\Models\MasterQcUrutan;
use App\Models\MasterRapBahan;
use App\Models\MasterRapUpah;
use App\Models\MasterSatuan;
use App\Models\MasterUpah;
use App\Models\StockGudang;
use App\Models\Type;
use App\Models\Ubs;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProduksiTestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil atau Buat Type Pembangunan (Default: Type 33/66)
        $perumahan = \App\Models\Perumahaan::first();
        $type = Type::firstOrCreate(
            ['nama_type' => '33/66'],
            [
                'perumahaan_id' => $perumahan ? $perumahan->id : 1,
                'luas_bangunan' => 33,
                'luas_tanah' => 66,
                'harga_dasar' => 150000000,
                'harga_diajukan' => 180000000,
            ]
        );

        // 2. Data Master Satuan
        $satuans = [
            'bh', 'rit', 'sak', 'bj', 'ljr', 'kubik', 'lbr', 'dus', 'pail', 'galon', 'bngks', 'rol', 'ls',
            'kg', 'zak', 'pcs', 'box', 'm2', 'kaleng', 'pack', 'meter', 'batang'
        ];
        $satuanMap = [];
        foreach ($satuans as $sat) {
            $s = MasterSatuan::firstOrCreate(['nama' => $sat]);
            $satuanMap[$sat] = $s->id;
        }

        // 3. Data Master Upah
        $upahs = [
            'Borongan galian',
            'Borongan pondasi',
            'Borongan sepitenk',
            'Borong kerja unit',
            'Borong pipa',
            'Borong urukan',
            'Borong listrik dan bahan',
            'Borong atap',
            'Borong plafond',
            'Pelunasan listrik',
            'Upah Harian pembersihan',
            'Boronan cempolok selokan dan jembatan',
            'Borong kerja cat',
            'Boronga kerja kusen',
            'Borong kerja pintu',
            'Pembesihan'
        ];
        $upahMap = [];
        foreach ($upahs as $u) {
            $up = MasterUpah::firstOrCreate(['nama_upah' => $u]);
            $upahMap[$u] = $up->id;
        }

        // 4. Data Master Barang & Stock Gudang
        $barangsData = [
            // QC0
            ['nama' => 'Benang', 'satuan' => 'bh', 'is_stock' => true],
            ['nama' => 'Batu kali', 'satuan' => 'rit', 'is_stock' => true],
            ['nama' => 'Semen', 'satuan' => 'sak', 'is_stock' => true],
            ['nama' => 'Pasir', 'satuan' => 'rit', 'is_stock' => true],
            ['nama' => 'Cempolong', 'satuan' => 'bj', 'is_stock' => true],
            ['nama' => 'Tutup cempolong', 'satuan' => 'bj', 'is_stock' => true],

            // QC1
            ['nama' => 'Besi ram', 'satuan' => 'ljr', 'is_stock' => true],
            ['nama' => 'Hebel', 'satuan' => 'kubik', 'is_stock' => true],
            ['nama' => 'perekat', 'satuan' => 'sak', 'is_stock' => true],
            ['nama' => 'Urukan', 'satuan' => 'rit', 'is_stock' => true],
            ['nama' => 'Sirap', 'satuan' => 'lbr', 'is_stock' => true],
            ['nama' => 'Bambu', 'satuan' => 'ljr', 'is_stock' => true],
            ['nama' => 'Pipa 4', 'satuan' => 'ljr', 'is_stock' => true],
            ['nama' => 'Knee drajat 4', 'satuan' => 'bh', 'is_stock' => true],
            ['nama' => 'Knee 4', 'satuan' => 'bh', 'is_stock' => true],
            ['nama' => 'Pipa 3', 'satuan' => 'ljr', 'is_stock' => true],
            ['nama' => 'Knee 3', 'satuan' => 'bh', 'is_stock' => true],
            ['nama' => 'Pipa 3/4', 'satuan' => 'ljr', 'is_stock' => true],
            ['nama' => 'Knee 3/4', 'satuan' => 'bh', 'is_stock' => true],

            // QC2
            ['nama' => 'Triplek', 'satuan' => 'lbr', 'is_stock' => true],
            ['nama' => 'Usuk', 'satuan' => 'ljr', 'is_stock' => true],
            ['nama' => 'Knee drat 3/4', 'satuan' => 'bh', 'is_stock' => true],
            ['nama' => 'Kran', 'satuan' => 'bh', 'is_stock' => true],

            // QC3
            ['nama' => 'Canal C', 'satuan' => 'ljr', 'is_stock' => true],
            ['nama' => 'Reng', 'satuan' => 'ljr', 'is_stock' => true],
            ['nama' => 'Spandek 7 m', 'satuan' => 'lbr', 'is_stock' => true],
            ['nama' => 'Baut canal', 'satuan' => 'bj', 'is_stock' => true],
            ['nama' => 'Baut reng', 'satuan' => 'bj', 'is_stock' => true],
            ['nama' => 'Baut spandek', 'satuan' => 'bj', 'is_stock' => true],
            ['nama' => 'Hollow plafond', 'satuan' => 'ljr', 'is_stock' => true],
            ['nama' => 'Enjel', 'satuan' => 'ljr', 'is_stock' => true],
            ['nama' => 'Kalsiboar', 'satuan' => 'lbr', 'is_stock' => true],
            ['nama' => 'Skrup gip', 'satuan' => 'dus', 'is_stock' => true],
            ['nama' => 'Kornes', 'satuan' => 'sak', 'is_stock' => true],
            ['nama' => 'Perban tip', 'satuan' => 'bh', 'is_stock' => true],
            ['nama' => 'Talang PVC', 'satuan' => 'ljr', 'is_stock' => true],
            ['nama' => 'Tutup talang', 'satuan' => 'bh', 'is_stock' => true],
            ['nama' => 'Corong', 'satuan' => 'bh', 'is_stock' => true],
            ['nama' => 'Genteng', 'satuan' => 'bh', 'is_stock' => true],
            ['nama' => 'Wuwung', 'satuan' => 'bh', 'is_stock' => true],
            ['nama' => 'Acian bata ringan', 'satuan' => 'sak', 'is_stock' => true],
            ['nama' => 'Kramik dinding km dan laundry', 'satuan' => 'dus', 'is_stock' => true],
            ['nama' => 'Kramik lantai km', 'satuan' => 'dus', 'is_stock' => true],
            ['nama' => 'Closet jongkok', 'satuan' => 'bh', 'is_stock' => true],
            ['nama' => 'Lampu tempel', 'satuan' => 'bh', 'is_stock' => true],

            // QC4
            ['nama' => 'Kramik utama', 'satuan' => 'dus', 'is_stock' => true],
            ['nama' => 'Pintu PVC', 'satuan' => 'bj', 'is_stock' => true],
            ['nama' => 'Cempolong selokan', 'satuan' => 'bj', 'is_stock' => true],

            // QC5
            ['nama' => 'Kalsium', 'satuan' => 'rit', 'is_stock' => true],
            ['nama' => 'Semen putih', 'satuan' => 'sak', 'is_stock' => true],
            ['nama' => 'Cat int avitek dalam', 'satuan' => 'pail', 'is_stock' => true],
            ['nama' => 'Cat impro plafond', 'satuan' => 'galon', 'is_stock' => true],
            ['nama' => 'Cat yoko genteng', 'satuan' => 'galon', 'is_stock' => true],
            ['nama' => 'Cat no drop abu2', 'satuan' => 'galon', 'is_stock' => true],
            ['nama' => 'Cat no drop putih', 'satuan' => 'galon', 'is_stock' => true],
            ['nama' => 'Lem rajawali', 'satuan' => 'bngks', 'is_stock' => true],
            ['nama' => 'Engsel 12', 'satuan' => 'bj', 'is_stock' => true],
            ['nama' => 'OB 3', 'satuan' => 'ljr', 'is_stock' => true],
            ['nama' => 'Casement 2 profil', 'satuan' => 'ljr', 'is_stock' => true],
            ['nama' => 'Tutup polos', 'satuan' => 'ljr', 'is_stock' => true],
            ['nama' => 'Spigot', 'satuan' => 'bh', 'is_stock' => true],
            ['nama' => 'Karet o', 'satuan' => 'rol', 'is_stock' => true],
            ['nama' => 'Karet c', 'satuan' => 'rol', 'is_stock' => true],
            ['nama' => 'Tatapan', 'satuan' => 'ljr', 'is_stock' => true],
            ['nama' => 'Skrup fiser', 'satuan' => 'bj', 'is_stock' => true],
            ['nama' => 'Silent', 'satuan' => 'bj', 'is_stock' => true],
            ['nama' => 'Pintu HPL', 'satuan' => 'bj', 'is_stock' => true],
            ['nama' => 'Engsel 4', 'satuan' => 'bj', 'is_stock' => true],
            ['nama' => 'Kunci slot', 'satuan' => 'bj', 'is_stock' => true],
        ];

        $barangMap = [];
        $allUbs = Ubs::all();

        foreach ($barangsData as $bData) {
            $satId = $satuanMap[$bData['satuan']];
            $barang = MasterBarang::firstOrCreate(
                ['nama_barang' => $bData['nama']],
                [
                    'kode_barang' => 'BRG-' . strtoupper(Str::random(6)),
                    'base_unit_id' => $satId,
                    'is_stock' => $bData['is_stock']
                ]
            );
            $barangMap[$bData['nama']] = $barang->id;

            // 4a. Tambahkan Konversi Satuan Utama (1 base_unit = 1.0)
            \App\Models\BarangSatuanKonversi::firstOrCreate(
                [
                    'barang_id' => $barang->id,
                    'satuan_id' => $satId,
                ],
                [
                    'konversi_ke_base' => 1.0,
                    'is_default' => true
                ]
            );

            // 4b. Satuan Konversi Tambahan
            $extraKonversi = [];
            switch ($bData['nama']) {
                case 'Semen':
                case 'Semen putih':
                case 'Acian bata ringan':
                case 'perekat':
                case 'Kornes':
                    // 1 Sak = 40 Kg, 1 Ton = 25 Sak
                    if (isset($satuanMap['kg'])) $extraKonversi[] = ['satuan_id' => $satuanMap['kg'], 'konversi' => 0.025]; // 1 kg = 0.025 sak
                    if (isset($satuanMap['zak'])) $extraKonversi[] = ['satuan_id' => $satuanMap['zak'], 'konversi' => 1.0];
                    break;

                case 'Pasir':
                case 'Batu kali':
                case 'Urukan':
                case 'Kalsium':
                    // 1 Rit = 4 Kubik
                    if (isset($satuanMap['kubik']) && $bData['satuan'] === 'rit') {
                        $extraKonversi[] = ['satuan_id' => $satuanMap['kubik'], 'konversi' => 0.25]; // 1 kubik = 0.25 rit
                    } elseif (isset($satuanMap['rit']) && $bData['satuan'] === 'kubik') {
                        $extraKonversi[] = ['satuan_id' => $satuanMap['rit'], 'konversi' => 4.0]; // 1 rit = 4 kubik
                    }
                    break;

                case 'Hebel':
                    // 1 Kubik = 83 Biji / Pcs
                    if (isset($satuanMap['bj'])) $extraKonversi[] = ['satuan_id' => $satuanMap['bj'], 'konversi' => 0.012]; // 1 pcs = 0.012 kubik
                    if (isset($satuanMap['pcs'])) $extraKonversi[] = ['satuan_id' => $satuanMap['pcs'], 'konversi' => 0.012];
                    break;

                case 'Skrup gip':
                    // 1 Dus = 1000 Biji
                    if (isset($satuanMap['bj'])) $extraKonversi[] = ['satuan_id' => $satuanMap['bj'], 'konversi' => 0.001]; // 1 bj = 0.001 dus
                    if (isset($satuanMap['box'])) $extraKonversi[] = ['satuan_id' => $satuanMap['box'], 'konversi' => 1.0];
                    break;

                case 'Kramik utama':
                case 'Kramik lantai km':
                case 'Kramik dinding km dan laundry':
                    // 1 Dus = 1 m2 (sekitar 4-6 Pcs)
                    if (isset($satuanMap['m2'])) $extraKonversi[] = ['satuan_id' => $satuanMap['m2'], 'konversi' => 1.0];
                    if (isset($satuanMap['bj'])) $extraKonversi[] = ['satuan_id' => $satuanMap['bj'], 'konversi' => 0.25];
                    break;

                case 'Cat int avitek dalam':
                    // 1 Pail = 5 Galon = 20 Kg
                    if (isset($satuanMap['galon'])) $extraKonversi[] = ['satuan_id' => $satuanMap['galon'], 'konversi' => 0.2]; // 1 galon = 0.2 pail
                    if (isset($satuanMap['kg'])) $extraKonversi[] = ['satuan_id' => $satuanMap['kg'], 'konversi' => 0.05];
                    break;

                case 'Cat impro plafond':
                case 'Cat yoko genteng':
                case 'Cat no drop abu2':
                case 'Cat no drop putih':
                    // 1 Galon = 4 Kg / 5 Kaleng
                    if (isset($satuanMap['kg'])) $extraKonversi[] = ['satuan_id' => $satuanMap['kg'], 'konversi' => 0.25];
                    if (isset($satuanMap['kaleng'])) $extraKonversi[] = ['satuan_id' => $satuanMap['kaleng'], 'konversi' => 1.0];
                    break;

                case 'Baut canal':
                case 'Baut reng':
                case 'Baut spandek':
                case 'Skrup fiser':
                    // 1 Dus = 500 Biji
                    if (isset($satuanMap['dus'])) $extraKonversi[] = ['satuan_id' => $satuanMap['dus'], 'konversi' => 500.0]; // 1 dus = 500 bj
                    if (isset($satuanMap['pack'])) $extraKonversi[] = ['satuan_id' => $satuanMap['pack'], 'konversi' => 100.0];
                    break;

                case 'Pipa 4':
                case 'Pipa 3':
                case 'Pipa 3/4':
                case 'Canal C':
                case 'Reng':
                case 'Hollow plafond':
                case 'Enjel':
                case 'Talang PVC':
                case 'Besi ram':
                case 'Usuk':
                    // 1 Lajar (6 Meter)
                    if (isset($satuanMap['meter'])) $extraKonversi[] = ['satuan_id' => $satuanMap['meter'], 'konversi' => 0.167]; // 1 meter = 0.167 ljr
                    if (isset($satuanMap['batang'])) $extraKonversi[] = ['satuan_id' => $satuanMap['batang'], 'konversi' => 1.0];
                    break;

                case 'Karet o':
                case 'Karet c':
                    // 1 Rol = 50 Meter
                    if (isset($satuanMap['meter'])) $extraKonversi[] = ['satuan_id' => $satuanMap['meter'], 'konversi' => 0.02];
                    break;
            }

            foreach ($extraKonversi as $ek) {
                \App\Models\BarangSatuanKonversi::firstOrCreate(
                    [
                        'barang_id' => $barang->id,
                        'satuan_id' => $ek['satuan_id'],
                    ],
                    [
                        'konversi_ke_base' => $ek['konversi'],
                        'is_default' => false
                    ]
                );
            }

            // Inisialisasi Stock Gudang (Gudang Utama HUB & UBS)
            StockGudang::firstOrCreate(
                [
                    'barang_id' => $barang->id,
                    'stock_type' => 'HUB',
                    'ubs_id' => null
                ],
                [
                    'jumlah_stock' => 10000,
                    'minimal_stock' => 10
                ]
            );

            foreach ($allUbs as $ubs) {
                StockGudang::firstOrCreate(
                    [
                        'barang_id' => $barang->id,
                        'stock_type' => 'UBS',
                        'ubs_id' => $ubs->id
                    ],
                    [
                        'jumlah_stock' => 1000,
                        'minimal_stock' => 5
                    ]
                );
            }
        }

        // 4c. Inisialisasi Nota Barang Masuk (Posted) untuk FIFO HPP Layering
        $notaHeader = \App\Models\NotaBarangMasuk::firstOrCreate(
            ['nomor_nota' => 'NBM-INIT-TESTING'],
            [
                'tanggal_nota' => now()->subDays(30),
                'cara_bayar' => 'cash',
                'status' => 'posted',
                'created_by' => 1,
                'posted_at' => now()->subDays(30)
            ]
        );

        $hargaEstimasiMap = [
            'Benang' => 15000,
            'Batu kali' => 350000,
            'Semen' => 65000,
            'Pasir' => 300000,
            'Cempolong' => 45000,
            'Tutup cempolong' => 35000,
            'Besi ram' => 55000,
            'Hebel' => 650000,
            'perekat' => 85000,
            'Urukan' => 200000,
            'Sirap' => 12000,
            'Bambu' => 15000,
            'Pipa 4' => 120000,
            'Knee drajat 4' => 25000,
            'Knee 4' => 20000,
            'Pipa 3' => 85000,
            'Knee 3' => 15000,
            'Pipa 3/4' => 25000,
            'Knee 3/4' => 5000,
            'Triplek' => 75000,
            'Usuk' => 35000,
            'Knee drat 3/4' => 7500,
            'Kran' => 35000,
            'Canal C' => 95000,
            'Reng' => 45000,
            'Spandek 7 m' => 250000,
            'Baut canal' => 500,
            'Baut reng' => 300,
            'Baut spandek' => 800,
            'Hollow plafond' => 30000,
            'Enjel' => 20000,
            'Kalsiboar' => 65000,
            'Skrup gip' => 45000,
            'Kornes' => 55000,
            'Perban tip' => 12000,
            'Talang PVC' => 85000,
            'Tutup talang' => 15000,
            'Corong' => 20000,
            'Genteng' => 4500,
            'Wuwung' => 12000,
            'Acian bata ringan' => 85000,
            'Kramik dinding km dan laundry' => 75000,
            'Kramik lantai km' => 65000,
            'Closet jongkok' => 175000,
            'Lampu tempel' => 45000,
            'Kramik utama' => 85000,
            'Pintu PVC' => 250000,
            'Cempolong selokan' => 50000,
            'Kalsium' => 180000,
            'Semen putih' => 95000,
            'Cat int avitek dalam' => 450000,
            'Cat impro plafond' => 180000,
            'Cat yoko genteng' => 195000,
            'Cat no drop abu2' => 220000,
            'Cat no drop putih' => 220000,
            'Lem rajawali' => 18000,
            'Engsel 12' => 25000,
            'OB 3' => 45000,
            'Casement 2 profil' => 65000,
            'Tutup polos' => 35000,
            'Spigot' => 15000,
            'Karet o' => 75000,
            'Karet c' => 75000,
            'Tatapan' => 45000,
            'Skrup fiser' => 500,
            'Silent' => 35000,
            'Pintu HPL' => 450000,
            'Engsel 4' => 35000,
            'Kunci slot' => 45000,
        ];

        foreach ($barangsData as $bData) {
            $bId = $barangMap[$bData['nama']];
            $sId = $satuanMap[$bData['satuan']];
            $hSatuan = $hargaEstimasiMap[$bData['nama']] ?? 25000;
            $qtyBase = 10000.0;
            $hTotal = $qtyBase * $hSatuan;

            \App\Models\NotaBarangMasukDetail::firstOrCreate(
                [
                    'nota_id' => $notaHeader->id,
                    'barang_id' => $bId,
                ],
                [
                    'merk' => 'Standar Testing',
                    'jumlah_input' => $qtyBase,
                    'satuan_id' => $sId,
                    'jumlah_base' => $qtyBase,
                    'harga_satuan' => $hSatuan,
                    'harga_satuan_base' => $hSatuan,
                    'harga_total' => $hTotal,
                    'jumlah_sisa' => $qtyBase,
                ]
            );
        }

        // 5. Buat Master QC Container untuk Type ini
        $container = MasterQcContainer::firstOrCreate(
            [
                'type_id' => $type->id,
            ],
            [
                'nama_container' => 'Master RAP & QC Type ' . $type->nama_type
            ]
        );

        // 6. Data Struktur QC1 - QC7 (Pekerjaan, Bahan RAP, & Upah RAP)
        $qcStructure = [
            1 => [
                'nama_qc' => 'QC 1 - Pekerjaan Persiapan & Pondasi',
                'tugas' => [
                    'Pekerjaan galian tanah pondasi',
                    'Pemasangan batu kali & pondasi',
                    'Pemasangan cempolong & tutupan'
                ],
                'bahan' => [
                    ['nama' => 'Benang', 'qty' => 1, 'satuan' => 'bh'],
                    ['nama' => 'Batu kali', 'qty' => 1.5, 'satuan' => 'rit'],
                    ['nama' => 'Semen', 'qty' => 3, 'satuan' => 'sak'],
                    ['nama' => 'Pasir', 'qty' => 1, 'satuan' => 'rit'],
                    ['nama' => 'Cempolong', 'qty' => 3, 'satuan' => 'bj'],
                    ['nama' => 'Tutup cempolong', 'qty' => 2, 'satuan' => 'bj'],
                ],
                'upah' => [
                    ['nama' => 'Borongan galian', 'nominal' => 220000],
                    ['nama' => 'Borongan pondasi', 'nominal' => 475000],
                    ['nama' => 'Borongan sepitenk', 'nominal' => 150000],
                ]
            ],
            2 => [
                'nama_qc' => 'QC 2 - Pekerjaan Dinding & Pipa Bawah',
                'tugas' => [
                    'Pemasangan hebel & perekat',
                    'Pemasangan besi ram & sirap',
                    'Instalasi pipa air kotor & bersih bawah'
                ],
                'bahan' => [
                    ['nama' => 'Benang', 'qty' => 1, 'satuan' => 'bh'],
                    ['nama' => 'Besi ram', 'qty' => 8, 'satuan' => 'ljr'],
                    ['nama' => 'Semen', 'qty' => 3, 'satuan' => 'sak'],
                    ['nama' => 'Pasir', 'qty' => 1, 'satuan' => 'kubik'],
                    ['nama' => 'Hebel', 'qty' => 8, 'satuan' => 'kubik'],
                    ['nama' => 'perekat', 'qty' => 9, 'satuan' => 'sak'],
                    ['nama' => 'Urukan', 'qty' => 2, 'satuan' => 'rit'],
                    ['nama' => 'Sirap', 'qty' => 20, 'satuan' => 'lbr'],
                    ['nama' => 'Bambu', 'qty' => 30, 'satuan' => 'ljr'],
                    ['nama' => 'Pipa 4', 'qty' => 2, 'satuan' => 'ljr'],
                    ['nama' => 'Knee drajat 4', 'qty' => 2, 'satuan' => 'bh'],
                    ['nama' => 'Knee 4', 'qty' => 1, 'satuan' => 'bh'],
                    ['nama' => 'Pipa 3', 'qty' => 4, 'satuan' => 'ljr'],
                    ['nama' => 'Knee 3', 'qty' => 1, 'satuan' => 'bh'],
                    ['nama' => 'Pipa 3/4', 'qty' => 5, 'satuan' => 'ljr'],
                    ['nama' => 'Knee 3/4', 'qty' => 3, 'satuan' => 'bh'],
                ],
                'upah' => [
                    ['nama' => 'Borong kerja unit', 'nominal' => 2150000],
                    ['nama' => 'Borong pipa', 'nominal' => 50000],
                    ['nama' => 'Borong urukan', 'nominal' => 200000],
                ]
            ],
            3 => [
                'nama_qc' => 'QC 3 - Pekerjaan Cor Kolom & Pasang Ringbalk',
                'tugas' => [
                    'Pengecoran kolom praktis & ringbalk',
                    'Pemasangan dinding hebel lanjutan',
                    'Instalasi kelistrikan awal & kran'
                ],
                'bahan' => [
                    ['nama' => 'Besi ram', 'qty' => 8, 'satuan' => 'ljr'],
                    ['nama' => 'Semen', 'qty' => 3, 'satuan' => 'sak'],
                    ['nama' => 'Pasir', 'qty' => 1, 'satuan' => 'kubik'],
                    ['nama' => 'Kornes', 'qty' => 10, 'satuan' => 'sak'],
                    ['nama' => 'Perban tip', 'qty' => 1, 'satuan' => 'bh'],
                    ['nama' => 'Talang PVC', 'qty' => 1, 'satuan' => 'ljr'],
                    ['nama' => 'Tutup talang', 'qty' => 2, 'satuan' => 'bh'],
                    ['nama' => 'Corong', 'qty' => 1, 'satuan' => 'bh'],
                    ['nama' => 'Genteng', 'qty' => 350, 'satuan' => 'bh'],
                    ['nama' => 'Wuwung', 'qty' => 11, 'satuan' => 'bh'],
                    ['nama' => 'Acian bata ringan', 'qty' => 15, 'satuan' => 'sak'],
                    ['nama' => 'Semen', 'qty' => 3, 'satuan' => 'sak'],
                    ['nama' => 'Pasir', 'qty' => 2, 'satuan' => 'kubik'],
                    ['nama' => 'Kramik dinding km dan laundry', 'qty' => 9, 'satuan' => 'dus'],
                    ['nama' => 'Kramik lantai km', 'qty' => 1.5, 'satuan' => 'dus'],
                    ['nama' => 'Closet jongkok', 'qty' => 1, 'satuan' => 'bh'],
                    ['nama' => 'Lampu tempel', 'qty' => 1, 'satuan' => 'bh'],
                ],
                'upah' => [
                    ['nama' => 'Borong kerja unit', 'nominal' => 2150000],
                    ['nama' => 'Borong atap', 'nominal' => 600000],
                    ['nama' => 'Borong plafond', 'nominal' => 496000],
                    ['nama' => 'Pelunasan listrik', 'nominal' => 1331500],
                ]
            ],
            4 => [
                'nama_qc' => 'QC 4 - Keramik Utama & Finishing Selokan',
                'tugas' => [
                    'Pemasangan lantai keramik utama',
                    'Pemasangan pintu PVC kamar mandi',
                    'Pekerjaan selokan & cempolong jembatan'
                ],
                'bahan' => [
                    ['nama' => 'Pasir', 'qty' => 1, 'satuan' => 'rit'],
                    ['nama' => 'Semen', 'qty' => 15, 'satuan' => 'sak'],
                    ['nama' => 'Kramik utama', 'qty' => 33, 'satuan' => 'dus'],
                    ['nama' => 'Pintu PVC', 'qty' => 1, 'satuan' => 'bj'],
                    ['nama' => 'Cempolong selokan', 'qty' => 4, 'satuan' => 'bj'],
                ],
                'upah' => [
                    ['nama' => 'Borong kerja unit', 'nominal' => 2150000],
                    ['nama' => 'Upah Harian pembersihan', 'nominal' => 150000],
                    ['nama' => 'Boronan cempolok selokan dan jembatan', 'nominal' => 50000],
                ]
            ],
            5 => [
                'nama_qc' => 'QC 5 - Pengecatan & Kusen Pintu Jendela',
                'tugas' => [
                    'Pengecatan dinding dalam, luar, & plafond',
                    'Pemasangan kusen alumunium, jendela, & pintu HPL',
                    'Pemasangan engsel, kunci, & aksesoris pintu'
                ],
                'bahan' => [
                    ['nama' => 'Kalsium', 'qty' => 2, 'satuan' => 'rit'],
                    ['nama' => 'Semen putih', 'qty' => 1, 'satuan' => 'sak'],
                    ['nama' => 'Cat int avitek dalam', 'qty' => 1, 'satuan' => 'pail'],
                    ['nama' => 'Cat impro plafond', 'qty' => 1, 'satuan' => 'galon'],
                    ['nama' => 'Cat yoko genteng', 'qty' => 2, 'satuan' => 'galon'],
                    ['nama' => 'Cat no drop abu2', 'qty' => 2, 'satuan' => 'galon'],
                    ['nama' => 'Cat no drop putih', 'qty' => 2, 'satuan' => 'galon'],
                    ['nama' => 'Lem rajawali', 'qty' => 12, 'satuan' => 'bngks'],
                    ['nama' => 'Engsel 12', 'qty' => 4, 'satuan' => 'bj'],
                    ['nama' => 'OB 3', 'qty' => 8, 'satuan' => 'ljr'],
                    ['nama' => 'Casement 2 profil', 'qty' => 4, 'satuan' => 'ljr'],
                    ['nama' => 'Tutup polos', 'qty' => 1, 'satuan' => 'ljr'],
                    ['nama' => 'Spigot', 'qty' => 1, 'satuan' => 'bh'],
                    ['nama' => 'Karet o', 'qty' => 7, 'satuan' => 'rol'],
                    ['nama' => 'Karet c', 'qty' => 1, 'satuan' => 'rol'],
                    ['nama' => 'Tatapan', 'qty' => 7, 'satuan' => 'ljr'],
                    ['nama' => 'Skrup fiser', 'qty' => 50, 'satuan' => 'bj'],
                    ['nama' => 'Silent', 'qty' => 4, 'satuan' => 'bj'],
                    ['nama' => 'Pintu HPL', 'qty' => 4, 'satuan' => 'bj'],
                    ['nama' => 'Engsel 4', 'qty' => 7, 'satuan' => 'bj'],
                    ['nama' => 'Kunci slot', 'qty' => 4, 'satuan' => 'bj'],
                ],
                'upah' => [
                    ['nama' => 'Borong kerja cat', 'nominal' => 1100000],
                    ['nama' => 'Boronga kerja kusen', 'nominal' => 800000],
                    ['nama' => 'Borong kerja pintu', 'nominal' => 200000],
                ]
            ],
            6 => [
                'nama_qc' => 'QC 6 - Pembersihan Akhir & Serah Terima',
                'tugas' => [
                    'Pembersihan sisa sisa proyek',
                    'Pemeriksaan akhir unit & siap serah terima'
                ],
                'bahan' => [],
                'upah' => [
                    ['nama' => 'Pembesihan', 'nominal' => 150000],
                ]
            ],
        ];

        foreach ($qcStructure as $qcIndex => $dataQC) {
            $urutan = MasterQcUrutan::firstOrCreate(
                [
                    'master_qc_container_id' => $container->id,
                    'qc_ke' => $qcIndex
                ],
                [
                    'nama_qc' => $dataQC['nama_qc']
                ]
            );

            // Seed Tugas QC
            foreach ($dataQC['tugas'] as $tugasText) {
                MasterQcTugas::firstOrCreate([
                    'master_qc_urutan_id' => $urutan->id,
                    'tugas' => $tugasText
                ]);
            }

            // Seed RAP Bahan QC
            foreach ($dataQC['bahan'] as $b) {
                $bId = $barangMap[$b['nama']];
                $sId = $satuanMap[$b['satuan']];

                MasterRapBahan::updateOrCreate(
                    [
                        'type_id' => $type->id,
                        'master_qc_container_id' => $container->id,
                        'master_qc_urutan_id' => $urutan->id,
                        'master_barang_id' => $bId,
                    ],
                    [
                        'jumlah_kebutuhan_standar' => $b['qty'],
                        'master_satuan_id' => $sId
                    ]
                );
            }

            // Seed RAP Upah QC
            foreach ($dataQC['upah'] as $u) {
                $uId = $upahMap[$u['nama']];

                MasterRapUpah::updateOrCreate(
                    [
                        'type_id' => $type->id,
                        'master_qc_container_id' => $container->id,
                        'master_qc_urutan_id' => $urutan->id,
                        'master_upah_id' => $uId,
                    ],
                    [
                        'nominal_standar' => $u['nominal']
                    ]
                );
            }
        }
    }
}
