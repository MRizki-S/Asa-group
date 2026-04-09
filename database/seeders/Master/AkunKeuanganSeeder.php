<?php

namespace Database\Seeders\Master;

use App\Models\AkunKeuangan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AkunKeuanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ============================
        // 1000 ASET
        // ============================

        // Aset kategori id: 1
        $aset = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '1000'],
            [
                'nama_akun' => 'Aset',
                'parent_id' => null,
                'kategori_akun_id' => 1,
                'is_leaf' => false,
            ]
        );

        // ASET LANCAR
        $asetLancar = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '1100'],
            [
                'nama_akun' => 'Aset Lancar',
                'parent_id' => $aset->id,
                'kategori_akun_id' => 1,
                'is_leaf' => false,
            ]
        );

        // KAS
        $kas = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '1101'],
            [
                'nama_akun' => 'Kas',
                'parent_id' => $asetLancar->id,
                'kategori_akun_id' => 1,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '1101-1', 'nama_akun' => 'Kas Kecil Operasional Kantor', 'parent_id' => $kas->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1101-2', 'nama_akun' => 'Kas Kecil Operasional Produksi', 'parent_id' => $kas->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1101-3', 'nama_akun' => 'Kas Kecil Material Produksi', 'parent_id' => $kas->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1101-4', 'nama_akun' => 'Kas Kecil Kasbon Tukang', 'parent_id' => $kas->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // BANK
        $bank = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '1102'],
            [
                'nama_akun' => 'Bank',
                'parent_id' => $asetLancar->id,
                'kategori_akun_id' => 1,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '1102-1',  'nama_akun' => 'BCA an. Alvin Zakaria', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-2',  'nama_akun' => 'BCA xx91', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-3',  'nama_akun' => 'BCA xx92', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-4',  'nama_akun' => 'BRI an. Alvin Bhakti Mandiri (xx302)', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-5',  'nama_akun' => 'Mandiri an. Alvin Bhakti Mandiri (x7001)', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-6',  'nama_akun' => 'BRI an. Alvin Zakaria (xx562)', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-7',  'nama_akun' => 'Bank Jatim Syariah an. PT. Alvin Bhakti Mandiri (xx136)', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-8',  'nama_akun' => 'BTN an. Alvin Bhakti Mandiri (xx084)', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-9',  'nama_akun' => 'BTN an. Alvin Zakaria', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-10', 'nama_akun' => 'BTN Syariah an. Alvin Zakaria', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-11', 'nama_akun' => 'BNI an. Alvin Bhakti Mandiri (xx186)', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-12', 'nama_akun' => 'Mandiri an. Alvin Bhakti Mandiri (8813)', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-13', 'nama_akun' => 'Mandiri an. Alvin Bhakti Mandiri (3088)', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-14', 'nama_akun' => 'BTN TAG', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // PIUTANG
        $piutang = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '1103'],
            [
                'nama_akun' => 'Piutang',
                'parent_id' => $asetLancar->id,
                'kategori_akun_id' => 1,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '1103-1', 'nama_akun' => 'Piutang Uang Muka', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-2', 'nama_akun' => 'Piutang Realisasi KPR', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-3', 'nama_akun' => 'Piutang Dana Ditahan', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-4', 'nama_akun' => 'Piutang SBUM', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-5', 'nama_akun' => 'Piutang Karyawan Tidak Tetap / Tukang', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-6', 'nama_akun' => 'Piutang Karyawan', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-7', 'nama_akun' => 'Piutang P. Alvin', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-8', 'nama_akun' => 'Piutang B. Saleh', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-9', 'nama_akun' => 'Piutang P. Saleh', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-10', 'nama_akun' => 'Piutang LHR', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-11', 'nama_akun' => 'Piutang Mangoon', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-12', 'nama_akun' => 'PIUTANG LAINNYA', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-13', 'nama_akun' => 'BRT Produksi ASA', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-14', 'nama_akun' => 'BRT Produksi LHR', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-15', 'nama_akun' => 'BRT Produksi Mangoon', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-16', 'nama_akun' => 'BRT Marketing', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // UANG MUKA
        $uangMuka = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '1104'],
            [
                'nama_akun' => 'Uang Muka',
                'parent_id' => $asetLancar->id,
                'kategori_akun_id' => 1,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '1104-1', 'nama_akun' => 'UANG MUKA KONSUMEN - UNIT PROPERTI', 'parent_id' => $uangMuka->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1104-2', 'nama_akun' => 'TITIPAN NUP', 'parent_id' => $uangMuka->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1104-3', 'nama_akun' => 'TITIPAN PAJAK', 'parent_id' => $uangMuka->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1104-4', 'nama_akun' => 'TITIPAN BI ADM KPR', 'parent_id' => $uangMuka->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1104-5', 'nama_akun' => 'TITIPAN BI PROSES', 'parent_id' => $uangMuka->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1104-6', 'nama_akun' => 'TITIPAN KELEBIHAN UM', 'parent_id' => $uangMuka->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1104-7', 'nama_akun' => 'TITIPAN PEMBATALAN', 'parent_id' => $uangMuka->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1104-8', 'nama_akun' => 'TITIPAN LAIN-LAIN', 'parent_id' => $uangMuka->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // ASET TETAP
        $asetTetap = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '1200'],
            [
                'nama_akun' => 'Aset Tetap',
                'parent_id' => $aset->id,
                'kategori_akun_id' => 1,
                'is_leaf' => false,
            ]
        );

        // PERSEDIAAN (Moved under Aset Tetap based on request)
        $persediaan = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '1201'],
            [
                'nama_akun' => 'Persediaan',
                'parent_id' => $asetTetap->id,
                'kategori_akun_id' => 1,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '1201-1', 'nama_akun' => 'Persediaan Material Gudang', 'parent_id' => $persediaan->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1201-2', 'nama_akun' => 'Persediaan Rumah Jadi', 'parent_id' => $persediaan->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1201-3', 'nama_akun' => 'Persediaan Tanah Matang', 'parent_id' => $persediaan->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1201-4', 'nama_akun' => 'Tanah Lain-Lain', 'parent_id' => $persediaan->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // Inventaris Rumah Contoh
        $rumahContoh = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '1202'],
            [
                'nama_akun' => 'Inventaris Rumah Contoh',
                'parent_id' => $asetTetap->id,
                'kategori_akun_id' => 1,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '1202-1', 'nama_akun' => 'Inventaris Rumah Contoh', 'parent_id' => $rumahContoh->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1202-2', 'nama_akun' => 'Akumulasi Penyusutan Rumah Contoh', 'parent_id' => $rumahContoh->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // Inventaris Bangunan dan Kantor
        $bangunanKantor = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '1203'],
            [
                'nama_akun' => 'Inventaris Bangunan dan Kantor',
                'parent_id' => $asetTetap->id,
                'kategori_akun_id' => 1,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '1203-1', 'nama_akun' => 'Inventaris Bangunan dan Kantor', 'parent_id' => $bangunanKantor->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1203-2', 'nama_akun' => 'Akumulasi Penyusutan Bangunan dan Kantor', 'parent_id' => $bangunanKantor->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // Inventaris Kendaraan
        $kendaraan = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '1204'],
            [
                'nama_akun' => 'Inventaris Kendaraan',
                'parent_id' => $asetTetap->id,
                'kategori_akun_id' => 1,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '1204-1', 'nama_akun' => 'Inventaris Kendaraan & Mesin', 'parent_id' => $kendaraan->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1204-2', 'nama_akun' => 'Akumulasi Penyusutan Kendaraan & Mesin', 'parent_id' => $kendaraan->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // Inventaris & Peralatan Produksi
        $peralatanProduksi = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '1205'],
            [
                'nama_akun' => 'Inventaris & Peralatan Produksi',
                'parent_id' => $asetTetap->id,
                'kategori_akun_id' => 1,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '1205-1', 'nama_akun' => 'Inventaris & Peralatan Produksi', 'parent_id' => $peralatanProduksi->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1205-2', 'nama_akun' => 'Akumulasi Penyusutan Produksi', 'parent_id' => $peralatanProduksi->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }





        // ============================
        // 2000 KEWAJIBAN
        // kategori_akun_id = 2
        // ============================

        $kewajiban = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '2000'],
            [
                'nama_akun' => 'Kewajiban',
                'parent_id' => null,
                'kategori_akun_id' => 2,
                'is_leaf' => false,
            ]
        );

        $kewajibanJangkaPanjang = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '2100'],
            [
                'nama_akun' => 'Kewajiban Jangka Panjang',
                'parent_id' => $kewajiban->id,
                'kategori_akun_id' => 2,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '2101', 'nama_akun' => 'Utang Bank', 'parent_id' => $kewajibanJangkaPanjang->id, 'kategori_akun_id' => 2, 'is_leaf' => true],
            ['kode_akun' => '2102', 'nama_akun' => 'Utang Tanah', 'parent_id' => $kewajibanJangkaPanjang->id, 'kategori_akun_id' => 2, 'is_leaf' => true],
            ['kode_akun' => '2103', 'nama_akun' => 'Utang Fee Pembebasan Lahan', 'parent_id' => $kewajibanJangkaPanjang->id, 'kategori_akun_id' => 2, 'is_leaf' => true],
            ['kode_akun' => '2104', 'nama_akun' => 'Utang Pajak Penjualan', 'parent_id' => $kewajibanJangkaPanjang->id, 'kategori_akun_id' => 2, 'is_leaf' => true],
            ['kode_akun' => '2105', 'nama_akun' => 'Utang Saham', 'parent_id' => $kewajibanJangkaPanjang->id, 'kategori_akun_id' => 2, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // Kewajiban Jangka Pendek
        $kewajibanJangkaPendek = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '2200'],
            [
                'nama_akun' => 'Kewajiban Jangka Pendek',
                'parent_id' => $kewajiban->id,
                'kategori_akun_id' => 2,
                'is_leaf' => false,
            ]
        );

        // Utang Pajak (2201) dengan sub-akun
        $utangPajak = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '2201'],
            [
                'nama_akun' => 'Utang Pajak',
                'parent_id' => $kewajibanJangkaPendek->id,
                'kategori_akun_id' => 2,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '2201-1', 'nama_akun' => 'Utang Pajak - PPH PS 21', 'parent_id' => $utangPajak->id, 'kategori_akun_id' => 2, 'is_leaf' => true],
            ['kode_akun' => '2201-2', 'nama_akun' => 'Utang Pajak - PPH PS 4(2) PP 23', 'parent_id' => $utangPajak->id, 'kategori_akun_id' => 2, 'is_leaf' => true],
            ['kode_akun' => '2201-3', 'nama_akun' => 'Utang Pajak - PPH PS 23', 'parent_id' => $utangPajak->id, 'kategori_akun_id' => 2, 'is_leaf' => true],
            ['kode_akun' => '2201-4', 'nama_akun' => 'Utang Pajak - PPH PS 4 (2) JASA', 'parent_id' => $utangPajak->id, 'kategori_akun_id' => 2, 'is_leaf' => true],
            ['kode_akun' => '2201-5', 'nama_akun' => 'Utang Pajak - PPH PS 4 (2) PENJUALAN', 'parent_id' => $utangPajak->id, 'kategori_akun_id' => 2, 'is_leaf' => true],
            ['kode_akun' => '2201-6', 'nama_akun' => 'Utang Pajak - PPN KELUARAN / KURANG BAYAR', 'parent_id' => $utangPajak->id, 'kategori_akun_id' => 2, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        foreach ([
            ['kode_akun' => '2202', 'nama_akun' => 'Utang Supplier', 'parent_id' => $kewajibanJangkaPendek->id, 'kategori_akun_id' => 2, 'is_leaf' => true],
            ['kode_akun' => '2203', 'nama_akun' => 'Utang Vendor', 'parent_id' => $kewajibanJangkaPendek->id, 'kategori_akun_id' => 2, 'is_leaf' => true],
            ['kode_akun' => '2204', 'nama_akun' => 'Utang Usaha Subkon', 'parent_id' => $kewajibanJangkaPendek->id, 'kategori_akun_id' => 2, 'is_leaf' => true],
            ['kode_akun' => '2205', 'nama_akun' => 'Utang LHR', 'parent_id' => $kewajibanJangkaPendek->id, 'kategori_akun_id' => 2, 'is_leaf' => true],
            ['kode_akun' => '2206', 'nama_akun' => 'Utang Mangoon', 'parent_id' => $kewajibanJangkaPendek->id, 'kategori_akun_id' => 2, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // ============================
        // 3000 EKUITAS
        // kategori_akun_id = 3
        // ============================

        $ekuitas = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '3000'],
            [
                'nama_akun' => 'Ekuitas',
                'parent_id' => null,
                'kategori_akun_id' => 3,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '3100', 'nama_akun' => 'Modal', 'parent_id' => $ekuitas->id, 'kategori_akun_id' => 3, 'is_leaf' => true],
            ['kode_akun' => '3200', 'nama_akun' => 'Prive', 'parent_id' => $ekuitas->id, 'kategori_akun_id' => 3, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // ============================
        // 4000 PENDAPATAN
        // kategori_akun_id = 4
        // ============================

        $pendapatan = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '4000'],
            [
                'nama_akun' => 'Pendapatan',
                'parent_id' => null,
                'kategori_akun_id' => 4,
                'is_leaf' => false,
            ]
        );

        // 4100 Penjualan Rumah (leaf - tidak ada anak)
        AkunKeuangan::updateOrCreate(
            ['kode_akun' => '4100'],
            [
                'nama_akun' => 'Penjualan Rumah',
                'parent_id' => $pendapatan->id,
                'kategori_akun_id' => 4,
                'is_leaf' => true,
            ]
        );

        // 4200 Penambahan Bangunan Kelebihan Tanah (leaf - tidak ada anak)
        AkunKeuangan::updateOrCreate(
            ['kode_akun' => '4200'],
            [
                'nama_akun' => 'Penambahan Bangunan Kelebihan Tanah',
                'parent_id' => $pendapatan->id,
                'kategori_akun_id' => 4,
                'is_leaf' => true,
            ]
        );

        // 4300 Kelebihan Tanah (leaf - tidak ada anak)
        AkunKeuangan::updateOrCreate(
            ['kode_akun' => '4300'],
            [
                'nama_akun' => 'Kelebihan Tanah',
                'parent_id' => $pendapatan->id,
                'kategori_akun_id' => 4,
                'is_leaf' => true,
            ]
        );

        // 4400 Pendapatan Lain-Lain (punya anak)
        $pendapatanLain = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '4400'],
            [
                'nama_akun' => 'Pendapatan Lain-Lain',
                'parent_id' => $pendapatan->id,
                'kategori_akun_id' => 4,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '4401', 'nama_akun' => 'Admin Pembatalan', 'parent_id' => $pendapatanLain->id, 'kategori_akun_id' => 4, 'is_leaf' => true],
            ['kode_akun' => '4402', 'nama_akun' => 'Penjualan Tanah Kavling', 'parent_id' => $pendapatanLain->id, 'kategori_akun_id' => 4, 'is_leaf' => true],
            ['kode_akun' => '4403', 'nama_akun' => 'Penjualan Aset Tetap', 'parent_id' => $pendapatanLain->id, 'kategori_akun_id' => 4, 'is_leaf' => true],
            ['kode_akun' => '4404', 'nama_akun' => 'Denda Atas Keterlambatan Bayar', 'parent_id' => $pendapatanLain->id, 'kategori_akun_id' => 4, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // ============================
        // 5000 BIAYA
        // kategori_akun_id = 5
        // ============================

        $biaya = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '5000'],
            [
                'nama_akun' => 'Biaya',
                'parent_id' => null,
                'kategori_akun_id' => 5,
                'is_leaf' => false,
            ]
        );

        // ---- 5001 Biaya Pajak Penjualan ----
        $biayaPajak = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '5001'],
            [
                'nama_akun' => 'Biaya Pajak Penjualan',
                'parent_id' => $biaya->id,
                'kategori_akun_id' => 5,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '5001-1', 'nama_akun' => 'AJB',   'parent_id' => $biayaPajak->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5001-2', 'nama_akun' => 'PPH',   'parent_id' => $biayaPajak->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5001-3', 'nama_akun' => 'BPHTB', 'parent_id' => $biayaPajak->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5001-4', 'nama_akun' => 'PBB',   'parent_id' => $biayaPajak->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5001-5', 'nama_akun' => 'PPN',   'parent_id' => $biayaPajak->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // ---- 5002 Biaya Realisasi KPR ----
        $biayaKpr = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '5002'],
            [
                'nama_akun' => 'Biaya Realisasi KPR',
                'parent_id' => $biaya->id,
                'kategori_akun_id' => 5,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '5002-1', 'nama_akun' => 'Bia Realisasi',                              'parent_id' => $biayaKpr->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5002-2', 'nama_akun' => 'PIJB / PPJB, Balik Nama, & PPAT - Unit',    'parent_id' => $biayaKpr->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5002-3', 'nama_akun' => 'Bia LPA',                                    'parent_id' => $biayaKpr->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5002-4', 'nama_akun' => 'Cetak Plat KPR',                             'parent_id' => $biayaKpr->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // ---- 5003 Biaya Pemasaran ----
        $biayaPemasaran = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '5003'],
            [
                'nama_akun' => 'Biaya Pemasaran',
                'parent_id' => $biaya->id,
                'kategori_akun_id' => 5,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '5003-1',  'nama_akun' => 'Fee & Komisi Penjualan',     'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-2',  'nama_akun' => 'Bia Promosi Online',          'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-3',  'nama_akun' => 'Sponsorship - Event',         'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-4',  'nama_akun' => 'Banner',                      'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-5',  'nama_akun' => 'Dinding Reklame',             'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-6',  'nama_akun' => 'Open Table',                  'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-7',  'nama_akun' => 'Bia Gimmick',                 'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-8',  'nama_akun' => 'Bia Konsumsi',                'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-10', 'nama_akun' => 'Ft. Copy. Pricelist',         'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-11', 'nama_akun' => 'Sewa Alat & Talent',          'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-12', 'nama_akun' => 'Biaya Pemasaran Lainnya',     'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // ---- 5004 Biaya Operasional Kantor Tetap ----
        $biayaOpTetap = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '5004'],
            [
                'nama_akun' => 'Biaya Operasional Kantor Tetap',
                'parent_id' => $biaya->id,
                'kategori_akun_id' => 5,
                'is_leaf' => false,
            ]
        );

        // 5004-6 Bia Utilitas (punya sub-akun)
        $biayaUtilitas = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '5004-6'],
            [
                'nama_akun' => 'Bia Utilitas',
                'parent_id' => $biayaOpTetap->id,
                'kategori_akun_id' => 5,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '5004-6-1', 'nama_akun' => 'B. PLN',      'parent_id' => $biayaUtilitas->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5004-6-2', 'nama_akun' => 'B. TELEPON',  'parent_id' => $biayaUtilitas->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5004-6-3', 'nama_akun' => 'B. INTERNET', 'parent_id' => $biayaUtilitas->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // 5004-7 Bia BPJS (punya sub-akun)
        $biayaBpjs = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '5004-7'],
            [
                'nama_akun' => 'Bia BPJS',
                'parent_id' => $biayaOpTetap->id,
                'kategori_akun_id' => 5,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '5004-7-1', 'nama_akun' => 'Bia BPJS Kesehatan',     'parent_id' => $biayaBpjs->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5004-7-2', 'nama_akun' => 'Bia BPJS Tenagakerjaan', 'parent_id' => $biayaBpjs->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // Sub-akun 5004 lainnya (leaf)
        foreach ([
            ['kode_akun' => '5004-1',  'nama_akun' => 'Bia Admin & Transfer Bank',   'parent_id' => $biayaOpTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5004-2',  'nama_akun' => 'Bia Cetak Berkas',            'parent_id' => $biayaOpTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5004-3',  'nama_akun' => 'Bia BBM',                     'parent_id' => $biayaOpTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5004-4',  'nama_akun' => 'Biaya ATK',                   'parent_id' => $biayaOpTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5004-5',  'nama_akun' => 'Biaya Pantry',                'parent_id' => $biayaOpTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5004-8',  'nama_akun' => 'Bia Gaji',                    'parent_id' => $biayaOpTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5004-9',  'nama_akun' => 'Bia Materai, Pos, Ekspedisi', 'parent_id' => $biayaOpTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5004-10', 'nama_akun' => 'Bia Tol - Parkir',            'parent_id' => $biayaOpTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5004-11', 'nama_akun' => 'Bia Ongkir',                  'parent_id' => $biayaOpTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // ---- 5005 Biaya Operasional Kantor Tidak Tetap ----
        $biayaOpTidakTetap = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '5005'],
            [
                'nama_akun' => 'Biaya Operasional Kantor Tidak Tetap',
                'parent_id' => $biaya->id,
                'kategori_akun_id' => 5,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '5005-1', 'nama_akun' => 'Biaya Perlengkapan',                        'parent_id' => $biayaOpTidakTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5005-2', 'nama_akun' => 'Bia Atribut Karyawan',                      'parent_id' => $biayaOpTidakTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5005-3', 'nama_akun' => 'Bia Entertain',                             'parent_id' => $biayaOpTidakTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5005-4', 'nama_akun' => 'Bia Rekrutment, Pelatihan, & Sertifikasi',  'parent_id' => $biayaOpTidakTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5005-5', 'nama_akun' => 'P3K',                                       'parent_id' => $biayaOpTidakTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5005-6', 'nama_akun' => 'Bia Perjalanan Dinas',                      'parent_id' => $biayaOpTidakTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // ---- 5006 Bia Pemeliharaan Aset & Inventaris ----
        $biayaPemeliharaan = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '5006'],
            [
                'nama_akun' => 'Bia Pemeliharaan Aset & Inventaris',
                'parent_id' => $biaya->id,
                'kategori_akun_id' => 5,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '5006-1', 'nama_akun' => 'Bia Pemeliharaan Kendaraan',          'parent_id' => $biayaPemeliharaan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5006-2', 'nama_akun' => 'Bia Pemeliharaan Aset & Inventaris',  'parent_id' => $biayaPemeliharaan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5006-3', 'nama_akun' => 'Bia Pemeliharaan Kantor',             'parent_id' => $biayaPemeliharaan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5006-4', 'nama_akun' => 'Bia Pemeliharaan Gudang',             'parent_id' => $biayaPemeliharaan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5006-5', 'nama_akun' => 'Bia Sewa Kantor',                     'parent_id' => $biayaPemeliharaan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // ---- 5007 Biaya Perijinan ----
        $biayaPerijinan = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '5007'],
            [
                'nama_akun' => 'Biaya Perijinan',
                'parent_id' => $biaya->id,
                'kategori_akun_id' => 5,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '5007-1', 'nama_akun' => 'PBG/IMB',             'parent_id' => $biayaPerijinan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5007-2', 'nama_akun' => 'Pecah SHGB',          'parent_id' => $biayaPerijinan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5007-3', 'nama_akun' => 'Sert. Induk',         'parent_id' => $biayaPerijinan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5007-4', 'nama_akun' => 'Splitzing IMB',       'parent_id' => $biayaPerijinan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5007-5', 'nama_akun' => 'PKKPR',               'parent_id' => $biayaPerijinan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5007-6', 'nama_akun' => 'Jembatan',            'parent_id' => $biayaPerijinan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5007-7', 'nama_akun' => 'Pertimbangan Teknis', 'parent_id' => $biayaPerijinan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5007-8', 'nama_akun' => 'Uji Air Bersih',      'parent_id' => $biayaPerijinan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5007-9', 'nama_akun' => 'Bia2 Akomodasi',      'parent_id' => $biayaPerijinan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // ---- 5008 Bia Kelurahan (leaf) ----
        AkunKeuangan::updateOrCreate(['kode_akun' => '5008'], ['nama_akun' => 'Bia Kelurahan',             'parent_id' => $biaya->id, 'kategori_akun_id' => 5, 'is_leaf' => true]);
        // ---- 5009 Biaya Lain-Lain (leaf) ----
        AkunKeuangan::updateOrCreate(['kode_akun' => '5009'], ['nama_akun' => 'Biaya Lain - Lain',         'parent_id' => $biaya->id, 'kategori_akun_id' => 5, 'is_leaf' => true]);
        // ---- 5010 Biaya CSR & Sumbangan Warga (leaf) ----
        AkunKeuangan::updateOrCreate(['kode_akun' => '5010'], ['nama_akun' => 'Biaya CSR & Sumbangan Warga', 'parent_id' => $biaya->id, 'kategori_akun_id' => 5, 'is_leaf' => true]);
        // ---- 5011 Biaya Web & Aplikasi (leaf) ----
        AkunKeuangan::updateOrCreate(['kode_akun' => '5011'], ['nama_akun' => 'Biaya Web & Aplikasi',      'parent_id' => $biaya->id, 'kategori_akun_id' => 5, 'is_leaf' => true]);
        // ---- 5012 Biaya Arsitek & Konsultan (leaf) ----
        AkunKeuangan::updateOrCreate(['kode_akun' => '5012'], ['nama_akun' => 'Biaya Arsitek & Konsultan', 'parent_id' => $biaya->id, 'kategori_akun_id' => 5, 'is_leaf' => true]);
        // ---- 5013 Beban Bunga Bank (leaf) ----
        AkunKeuangan::updateOrCreate(['kode_akun' => '5013'], ['nama_akun' => 'Beban Bunga Bank',          'parent_id' => $biaya->id, 'kategori_akun_id' => 5, 'is_leaf' => true]);
        // ---- 5014 Beban Bunga KYG PPT (leaf) ----
        AkunKeuangan::updateOrCreate(['kode_akun' => '5014'], ['nama_akun' => 'Beban Bunga KYG PPT',       'parent_id' => $biaya->id, 'kategori_akun_id' => 5, 'is_leaf' => true]);
        // ---- 5015 Biaya Admin dan Provisi (leaf) ----
        AkunKeuangan::updateOrCreate(['kode_akun' => '5015'], ['nama_akun' => 'Biaya Admin dan Provisi',   'parent_id' => $biaya->id, 'kategori_akun_id' => 5, 'is_leaf' => true]);

        // ---- 5016 Biaya Estate Management (punya anak) ----
        $biayaEM = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '5016'],
            [
                'nama_akun' => 'Biaya Estate Management',
                'parent_id' => $biaya->id,
                'kategori_akun_id' => 5,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '5016-1', 'nama_akun' => 'Bia Gaji Tukang EM',               'parent_id' => $biayaEM->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5016-2', 'nama_akun' => 'EM Air Tandon',                     'parent_id' => $biayaEM->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5016-3', 'nama_akun' => 'EM Listrik',                        'parent_id' => $biayaEM->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5016-4', 'nama_akun' => 'Bia Waker/Sekuriti',                'parent_id' => $biayaEM->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5016-5', 'nama_akun' => 'EM Wifi / CCTV',                    'parent_id' => $biayaEM->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5016-6', 'nama_akun' => 'EM Kebersihan Lingkungan',          'parent_id' => $biayaEM->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5016-7', 'nama_akun' => 'Biaya Estate Management lainnya',   'parent_id' => $biayaEM->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // ---- 5018 Biaya Operasional Produksi ----
        $biayaOpProduksi = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '5018'],
            [
                'nama_akun' => 'Biaya Operasional Produksi',
                'parent_id' => $biaya->id,
                'kategori_akun_id' => 5,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '5018-1', 'nama_akun' => 'Biaya BBM',                          'parent_id' => $biayaOpProduksi->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5018-2', 'nama_akun' => 'Biaya Konsumsi',                     'parent_id' => $biayaOpProduksi->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5018-3', 'nama_akun' => 'Biaya Token',                        'parent_id' => $biayaOpProduksi->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5018-4', 'nama_akun' => 'Biaya P3K',                          'parent_id' => $biayaOpProduksi->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5018-5', 'nama_akun' => 'Biaya ATK',                          'parent_id' => $biayaOpProduksi->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5018-6', 'nama_akun' => 'Biaya Perlengkapan Produksi',        'parent_id' => $biayaOpProduksi->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5018-7', 'nama_akun' => 'Biaya Ongkir',                       'parent_id' => $biayaOpProduksi->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5018-8', 'nama_akun' => 'Biaya Operasional Produksi Lainnya', 'parent_id' => $biayaOpProduksi->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // ---- 5019 Beban Bunga Proyek Lain (leaf) ----
        AkunKeuangan::updateOrCreate(['kode_akun' => '5019'], ['nama_akun' => 'Beban Bunga Proyek Lain', 'parent_id' => $biaya->id, 'kategori_akun_id' => 5, 'is_leaf' => true]);

        // ---- 5020 Biaya Pembelian Tanah ----
        $biayaPembelianTanah = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '5020'],
            [
                'nama_akun' => 'Biaya Pembelian Tanah',
                'parent_id' => $biaya->id,
                'kategori_akun_id' => 5,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '5020-1', 'nama_akun' => 'Sertifikat Induk & IMB Induk',                 'parent_id' => $biayaPembelianTanah->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5020-2', 'nama_akun' => 'Pajak ( PBB, PPh, BPHTB & Sert )',            'parent_id' => $biayaPembelianTanah->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5020-3', 'nama_akun' => 'Biaya Pendirian PT',                           'parent_id' => $biayaPembelianTanah->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5020-4', 'nama_akun' => 'Fee Broker Saham',                             'parent_id' => $biayaPembelianTanah->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5020-5', 'nama_akun' => 'Fee Pembebasan Lahan',                         'parent_id' => $biayaPembelianTanah->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5020-6', 'nama_akun' => 'Bi. Lain - Lain Terkait Pembelian Tanah',     'parent_id' => $biayaPembelianTanah->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // ---- 5022 Biaya Sarana dan Prasarana ----
        $biayaSarana = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '5022'],
            [
                'nama_akun' => 'Biaya Sarana dan Prasarana',
                'parent_id' => $biaya->id,
                'kategori_akun_id' => 5,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '5022-1',  'nama_akun' => 'Gate, Pagar & Pos Penjagaan',                                        'parent_id' => $biayaSarana->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5022-2',  'nama_akun' => 'Jalan, Saluran, & Gorong - Gorong',                                  'parent_id' => $biayaSarana->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5022-3',  'nama_akun' => 'Pemeliharaan PJU',                                                   'parent_id' => $biayaSarana->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5022-4',  'nama_akun' => 'Urugan',                                                             'parent_id' => $biayaSarana->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5022-5',  'nama_akun' => 'Pemeliharaan Tanaman',                                               'parent_id' => $biayaSarana->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5022-6',  'nama_akun' => 'Bi. Lain-lain terkait Sarana & Prasarana (Konsumsi & Operasional)', 'parent_id' => $biayaSarana->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5022-7',  'nama_akun' => 'Tempat Ibadah',                                                     'parent_id' => $biayaSarana->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5022-8',  'nama_akun' => 'Pasar dan Pujasera',                                                 'parent_id' => $biayaSarana->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5022-9',  'nama_akun' => 'Cut & Fill',                                                        'parent_id' => $biayaSarana->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5022-10', 'nama_akun' => 'Paving',                                                            'parent_id' => $biayaSarana->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5022-11', 'nama_akun' => 'Plengsengan & Pagar Batas',                                         'parent_id' => $biayaSarana->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5022-12', 'nama_akun' => 'Rumah Tandon',                                                      'parent_id' => $biayaSarana->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5022-13', 'nama_akun' => 'Sarana Olah Raga',                                                  'parent_id' => $biayaSarana->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5022-14', 'nama_akun' => 'Biaya Gaji Tukang ASA',                                             'parent_id' => $biayaSarana->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5022-15', 'nama_akun' => 'Biaya Gaji Tukang LHR',                                             'parent_id' => $biayaSarana->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }

        // ---- 5023 Biaya Pembangunan Unit ----
        $biayaPembangunan = AkunKeuangan::updateOrCreate(
            ['kode_akun' => '5023'],
            [
                'nama_akun' => 'Biaya Pembangunan Unit',
                'parent_id' => $biaya->id,
                'kategori_akun_id' => 5,
                'is_leaf' => false,
            ]
        );

        foreach ([
            ['kode_akun' => '5023-1', 'nama_akun' => 'Bi. Pembangunan Unit',               'parent_id' => $biayaPembangunan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5023-2', 'nama_akun' => 'Air',                                 'parent_id' => $biayaPembangunan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5023-3', 'nama_akun' => 'Listrik',                             'parent_id' => $biayaPembangunan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5023-4', 'nama_akun' => 'Bi. Lain - Lain Terkait Lapangan',   'parent_id' => $biayaPembangunan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ] as $item) {
            AkunKeuangan::updateOrCreate(['kode_akun' => $item['kode_akun']], $item);
        }



    }
}
