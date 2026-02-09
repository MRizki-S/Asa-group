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
        $aset = AkunKeuangan::create([
            'kode_akun' => '1000',
            'nama_akun' => 'Aset',
            'parent_id' => null,
            'kategori_akun_id' => 1,
            'is_leaf' => false,
        ]);

        // ASET LANCAR
        $asetLancar = AkunKeuangan::create([
            'kode_akun' => '1100',
            'nama_akun' => 'Aset Lancar',
            'parent_id' => $aset->id,
            'kategori_akun_id' => 1,
            'is_leaf' => false,
        ]);

        // KAS
        $kas = AkunKeuangan::create([
            'kode_akun' => '1101',
            'nama_akun' => 'Kas',
            'parent_id' => $asetLancar->id,
            'kategori_akun_id' => 1,
            'is_leaf' => false,
        ]);

        AkunKeuangan::insert([
            [
                'kode_akun' => '1101-1',
                'nama_akun' => 'Kas Kecil Operasional Kantor',
                'parent_id' => $kas->id,
                'kategori_akun_id' => 1,
                'is_leaf' => true,
            ],
            [
                'kode_akun' => '1101-2',
                'nama_akun' => 'Kas Kecil Operasional Produksi',
                'parent_id' => $kas->id,
                'kategori_akun_id' => 1,
                'is_leaf' => true,
            ],
            [
                'kode_akun' => '1101-3',
                'nama_akun' => 'Kas Kecil Material Produksi',
                'parent_id' => $kas->id,
                'kategori_akun_id' => 1,
                'is_leaf' => true,
            ],
            [
                'kode_akun' => '1101-4',
                'nama_akun' => 'Kas Kecil Kasbon Tukang',
                'parent_id' => $kas->id,
                'kategori_akun_id' => 1,
                'is_leaf' => true,
            ],
        ]);

        // BANK
        $bank = AkunKeuangan::create([
            'kode_akun' => '1102',
            'nama_akun' => 'Bank',
            'parent_id' => $asetLancar->id,
            'kategori_akun_id' => 1,
            'is_leaf' => false,
        ]);

        AkunKeuangan::insert([
            ['kode_akun' => '1102-1', 'nama_akun' => 'BCA an. Alvin Zakaria', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-2', 'nama_akun' => 'BCA xx91', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-3', 'nama_akun' => 'BCA xx92', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-4', 'nama_akun' => 'BRI an. Alvin Bhakti Mandiri', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-5', 'nama_akun' => 'BRI an. Alvin Zakaria', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-6', 'nama_akun' => 'BRI an. Alvin Zakaria', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-7', 'nama_akun' => 'Bank Jatim Syariah', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-8', 'nama_akun' => 'BTN an. Alvin Bhakti Mandiri', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-9', 'nama_akun' => 'BTN an. Alvin Zakaria', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-10', 'nama_akun' => 'BTN Syariah an. Alvin Zakaria', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-11', 'nama_akun' => 'BNI an. Alvin Bhakti Mandiri', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-12', 'nama_akun' => 'Mandiri an. Alvin Bhakti Mandiri', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1102-13', 'nama_akun' => 'Mandiri an. Alvin Bhakti Mandiri', 'parent_id' => $bank->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
        ]);

        // PIUTANG
        $piutang = AkunKeuangan::create([
            'kode_akun' => '1103',
            'nama_akun' => 'Piutang',
            'parent_id' => $asetLancar->id,
            'kategori_akun_id' => 1,
            'is_leaf' => false,
        ]);

        AkunKeuangan::insert([
            ['kode_akun' => '1103-1', 'nama_akun' => 'Piutang Cash Keras/Tahap', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-2', 'nama_akun' => 'Piutang DP KPR', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-3', 'nama_akun' => 'Piutang Realisasi KPR', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-4', 'nama_akun' => 'Piutang Dana Ditahan', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-5', 'nama_akun' => 'Piutang SBUM', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1103-6', 'nama_akun' => 'Piutang Karyawan', 'parent_id' => $piutang->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
        ]);

        // PERSEDIAAN
        $persediaan = AkunKeuangan::create([
            'kode_akun' => '1104',
            'nama_akun' => 'Persediaan',
            'parent_id' => $asetLancar->id,
            'kategori_akun_id' => 1,
            'is_leaf' => false,
        ]);

        AkunKeuangan::insert([
            ['kode_akun' => '1104-1', 'nama_akun' => 'Persediaan Material Gudang', 'parent_id' => $persediaan->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1104-2', 'nama_akun' => 'Persediaan Rumah Jadi', 'parent_id' => $persediaan->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
            ['kode_akun' => '1104-3', 'nama_akun' => 'Persediaan Tanah Matang', 'parent_id' => $persediaan->id, 'kategori_akun_id' => 1, 'is_leaf' => true],
        ]);

        // ASET TETAP
        $asetTetap = AkunKeuangan::create([
            'kode_akun' => '1200',
            'nama_akun' => 'Aset Tetap',
            'parent_id' => $aset->id,
            'kategori_akun_id' => 1,
            'is_leaf' => false,
        ]);

        $tanah = AkunKeuangan::create([
            'kode_akun' => '1201',
            'nama_akun' => 'Tanah',
            'parent_id' => $asetTetap->id,
            'kategori_akun_id' => 1,
            'is_leaf' => false,
        ]);

        AkunKeuangan::insert([
            [
                'kode_akun' => '1201-1',
                'nama_akun' => 'Tanah A',
                'parent_id' => $tanah->id,
                'kategori_akun_id' => 1,
                'is_leaf' => true,
            ],
            [
                'kode_akun' => '1201-2',
                'nama_akun' => 'Tanah B',
                'parent_id' => $tanah->id,
                'kategori_akun_id' => 1,
                'is_leaf' => true,
            ],
            [
                'kode_akun' => '1201-3',
                'nama_akun' => 'Tanah C',
                'parent_id' => $tanah->id,
                'kategori_akun_id' => 1,
                'is_leaf' => true,
            ],
        ]);

        // Bangunan
        AkunKeuangan::create([
            'kode_akun' => '1202',
            'nama_akun' => 'Bangunan',
            'parent_id' => $asetTetap->id,
            'kategori_akun_id' => 1,
            'is_leaf' => true,
        ]);

        // Inventaris & Peralatan Produksi
        AkunKeuangan::create([
            'kode_akun' => '1203',
            'nama_akun' => 'Inventaris & Peralatan Produksi',
            'parent_id' => $asetTetap->id,
            'kategori_akun_id' => 1,
            'is_leaf' => true,
        ]);

        // Akumulasi Penyusutan (kontra aset)
        AkunKeuangan::create([
            'kode_akun' => '1204',
            'nama_akun' => 'Akumulasi Penyusutan',
            'parent_id' => $asetTetap->id,
            'kategori_akun_id' => 1,
            'is_leaf' => true,
        ]);




        // ============================
        // 2000 KEWAJIBAN
        // kategori_akun_id = 2
        // ============================

        $kewajiban = AkunKeuangan::create([
            'kode_akun' => '2000',
            'nama_akun' => 'Kewajiban',
            'parent_id' => null,
            'kategori_akun_id' => 2,
            'is_leaf' => false,
        ]);

        $kewajibanJangkaPanjang = AkunKeuangan::create([
            'kode_akun' => '2100',
            'nama_akun' => 'Kewajiban Jangka Panjang',
            'parent_id' => $kewajiban->id,
            'kategori_akun_id' => 2,
            'is_leaf' => false,
        ]);

        AkunKeuangan::insert([
            [
                'kode_akun' => '2101',
                'nama_akun' => 'Utang Bank',
                'parent_id' => $kewajibanJangkaPanjang->id,
                'kategori_akun_id' => 2,
                'is_leaf' => true,
            ],
            [
                'kode_akun' => '2102',
                'nama_akun' => 'Utang Tanah',
                'parent_id' => $kewajibanJangkaPanjang->id,
                'kategori_akun_id' => 2,
                'is_leaf' => true,
            ],
            [
                'kode_akun' => '2103',
                'nama_akun' => 'Utang Fee Pembebasan Lahan',
                'parent_id' => $kewajibanJangkaPanjang->id,
                'kategori_akun_id' => 2,
                'is_leaf' => true,
            ],
            [
                'kode_akun' => '2104',
                'nama_akun' => 'Utang Pajak Penjualan',
                'parent_id' => $kewajibanJangkaPanjang->id,
                'kategori_akun_id' => 2,
                'is_leaf' => true,
            ],
        ]);




        // ============================
        // 3000 EKUITAS
        // kategori_akun_id = 3
        // ============================

        $ekuitas = AkunKeuangan::create([
            'kode_akun' => '3000',
            'nama_akun' => 'Ekuitas',
            'parent_id' => null,
            'kategori_akun_id' => 3,
            'is_leaf' => false,
        ]);

        AkunKeuangan::insert([
            [
                'kode_akun' => '3100',
                'nama_akun' => 'Modal',
                'parent_id' => $ekuitas->id,
                'kategori_akun_id' => 3,
                'is_leaf' => true,
            ],
            [
                'kode_akun' => '3200',
                'nama_akun' => 'Prive',
                'parent_id' => $ekuitas->id,
                'kategori_akun_id' => 3,
                'is_leaf' => true,
            ],
        ]);



        // ============================
        // 4000 PENDAPATAN
        // kategori_akun_id = 4
        // ============================

        $pendapatan = AkunKeuangan::create([
            'kode_akun' => '4000',
            'nama_akun' => 'Pendapatan',
            'parent_id' => null,
            'kategori_akun_id' => 4,
            'is_leaf' => false,
        ]);

        // PENJUALAN RUMAH
        $penjualanRumah = AkunKeuangan::create([
            'kode_akun' => '4100',
            'nama_akun' => 'Penjualan Rumah',
            'parent_id' => $pendapatan->id,
            'kategori_akun_id' => 4,
            'is_leaf' => false,
        ]);

        AkunKeuangan::insert([
            [
                'kode_akun' => '4101',
                'nama_akun' => 'Penjualan Rumah Cash Keras/Tahap',
                'parent_id' => $penjualanRumah->id,
                'kategori_akun_id' => 4,
                'is_leaf' => true,
            ],
            [
                'kode_akun' => '4102',
                'nama_akun' => 'Penjualan Rumah KPR',
                'parent_id' => $penjualanRumah->id,
                'kategori_akun_id' => 4,
                'is_leaf' => true,
            ],
            [
                'kode_akun' => '4103',
                'nama_akun' => 'Penambahan Bangunan',
                'parent_id' => $penjualanRumah->id,
                'kategori_akun_id' => 4,
                'is_leaf' => true,
            ],
            [
                'kode_akun' => '4104',
                'nama_akun' => 'Kelebihan Tanah',
                'parent_id' => $penjualanRumah->id,
                'kategori_akun_id' => 4,
                'is_leaf' => true,
            ],
        ]);

        // PENDAPATAN LAIN-LAIN
        $pendapatanLain = AkunKeuangan::create([
            'kode_akun' => '4200',
            'nama_akun' => 'Pendapatan Lain-Lain',
            'parent_id' => $pendapatan->id,
            'kategori_akun_id' => 4,
            'is_leaf' => false,
        ]);

        AkunKeuangan::insert([
            [
                'kode_akun' => '4201',
                'nama_akun' => 'Admin Pembatalan',
                'parent_id' => $pendapatanLain->id,
                'kategori_akun_id' => 4,
                'is_leaf' => true,
            ],
            [
                'kode_akun' => '4202',
                'nama_akun' => 'Penjualan Tanah Kavling',
                'parent_id' => $pendapatanLain->id,
                'kategori_akun_id' => 4,
                'is_leaf' => true,
            ],
            [
                'kode_akun' => '4203',
                'nama_akun' => 'Penjualan Aset Tetap',
                'parent_id' => $pendapatanLain->id,
                'kategori_akun_id' => 4,
                'is_leaf' => true,
            ],
            [
                'kode_akun' => '4204',
                'nama_akun' => 'Denda Atas Keterlambatan Bayar',
                'parent_id' => $pendapatanLain->id,
                'kategori_akun_id' => 4,
                'is_leaf' => true,
            ],
        ]);




        // ============================
        // 5000 BIAYA
        // kategori_akun_id = 5
        // ============================

        $biaya = AkunKeuangan::create([
            'kode_akun' => '5000',
            'nama_akun' => 'Biaya',
            'parent_id' => null,
            'kategori_akun_id' => 5,
            'is_leaf' => false,
        ]);

        // ---- 5001 Biaya Pajak Penjualan ----
        $biayaPajak = AkunKeuangan::create([
            'kode_akun' => '5001',
            'nama_akun' => 'Biaya Pajak Penjualan',
            'parent_id' => $biaya->id,
            'kategori_akun_id' => 5,
            'is_leaf' => false,
        ]);

        AkunKeuangan::insert([
            ['kode_akun' => '5001-1', 'nama_akun' => 'AJB', 'parent_id' => $biayaPajak->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5001-2', 'nama_akun' => 'PPH', 'parent_id' => $biayaPajak->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5001-3', 'nama_akun' => 'BPHTB', 'parent_id' => $biayaPajak->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5001-4', 'nama_akun' => 'PBB', 'parent_id' => $biayaPajak->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5001-5', 'nama_akun' => 'PPN', 'parent_id' => $biayaPajak->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ]);

        // ---- 5002 Biaya Realisasi KPR ----
        $biayaKpr = AkunKeuangan::create([
            'kode_akun' => '5002',
            'nama_akun' => 'Biaya Realisasi KPR',
            'parent_id' => $biaya->id,
            'kategori_akun_id' => 5,
            'is_leaf' => false,
        ]);

        AkunKeuangan::insert([
            ['kode_akun' => '5002-1', 'nama_akun' => 'Bia Realisasi', 'parent_id' => $biayaKpr->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5002-2', 'nama_akun' => 'PIJB / PPJB', 'parent_id' => $biayaKpr->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5002-3', 'nama_akun' => 'Bia LPA', 'parent_id' => $biayaKpr->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5002-4', 'nama_akun' => 'Cetak Plat KPR', 'parent_id' => $biayaKpr->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ]);

        // ---- 5003 Biaya Pemasaran ----
        $biayaPemasaran = AkunKeuangan::create([
            'kode_akun' => '5003',
            'nama_akun' => 'Biaya Pemasaran',
            'parent_id' => $biaya->id,
            'kategori_akun_id' => 5,
            'is_leaf' => false,
        ]);

        AkunKeuangan::insert([
            ['kode_akun' => '5003-1', 'nama_akun' => 'Fee & Komisi Penjualan', 'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-2', 'nama_akun' => 'Bia Promosi Online', 'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-3', 'nama_akun' => 'Sponsorship - Event', 'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-4', 'nama_akun' => 'Banner', 'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-5', 'nama_akun' => 'Dinding Reklame', 'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-6', 'nama_akun' => 'Open Table', 'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-7', 'nama_akun' => 'Bia Gimmick', 'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-8', 'nama_akun' => 'Bia Konsumsi', 'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-9', 'nama_akun' => 'Brosur', 'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-10', 'nama_akun' => 'Ft. Copy. Pricelist', 'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5003-11', 'nama_akun' => 'Sewa Alat & Talent', 'parent_id' => $biayaPemasaran->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ]);

        // ---- 5004 Biaya Operasional Kantor Tetap ----
        $biayaOpTetap = AkunKeuangan::create([
            'kode_akun' => '5004',
            'nama_akun' => 'Biaya Operasional Kantor Tetap',
            'parent_id' => $biaya->id,
            'kategori_akun_id' => 5,
            'is_leaf' => false,
        ]);

        AkunKeuangan::insert([
            ['kode_akun' => '5004-1', 'nama_akun' => 'Bia Admin & Transfer Bank', 'parent_id' => $biayaOpTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5004-2', 'nama_akun' => 'Bia Cetak Berkas', 'parent_id' => $biayaOpTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5004-3', 'nama_akun' => 'Bia BBM', 'parent_id' => $biayaOpTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5004-4', 'nama_akun' => 'Biaya ATK - Materai', 'parent_id' => $biayaOpTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5004-5', 'nama_akun' => 'Biaya Pantry', 'parent_id' => $biayaOpTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5004-6', 'nama_akun' => 'Bia Utilitas', 'parent_id' => $biayaOpTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5004-7', 'nama_akun' => 'Bia BPJS', 'parent_id' => $biayaOpTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5004-8', 'nama_akun' => 'Bia Gaji', 'parent_id' => $biayaOpTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ]);

        // ---- 5005 Biaya Operasional Kantor Tidak Tetap ----
        $biayaOpTidakTetap = AkunKeuangan::create([
            'kode_akun' => '5005',
            'nama_akun' => 'Biaya Operasional Kantor Tidak Tetap',
            'parent_id' => $biaya->id,
            'kategori_akun_id' => 5,
            'is_leaf' => false,
        ]);

        AkunKeuangan::insert([
            ['kode_akun' => '5005-1', 'nama_akun' => 'Biaya Perlengkapan', 'parent_id' => $biayaOpTidakTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5005-2', 'nama_akun' => 'Bia Atribut Karyawan', 'parent_id' => $biayaOpTidakTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5005-3', 'nama_akun' => 'Bia Entertain', 'parent_id' => $biayaOpTidakTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5005-4', 'nama_akun' => 'Bia Pelatihan & Sertifikasi', 'parent_id' => $biayaOpTidakTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5005-5', 'nama_akun' => 'P3K', 'parent_id' => $biayaOpTidakTetap->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ]);

        // ---- 5006 Biaya Pemeliharaan Aset & Inventaris ----
        $biayaPemeliharaan = AkunKeuangan::create([
            'kode_akun' => '5006',
            'nama_akun' => 'Biaya Pemeliharaan Aset & Inventaris',
            'parent_id' => $biaya->id,
            'kategori_akun_id' => 5,
            'is_leaf' => false,
        ]);

        AkunKeuangan::insert([
            ['kode_akun' => '5006-1', 'nama_akun' => 'Bia Pemeliharaan Kendaraan', 'parent_id' => $biayaPemeliharaan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5006-2', 'nama_akun' => 'Bia Pemeliharaan Aset & Inventaris', 'parent_id' => $biayaPemeliharaan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5006-3', 'nama_akun' => 'Bia Pemeliharaan Kantor', 'parent_id' => $biayaPemeliharaan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5006-4', 'nama_akun' => 'Bia Pemeliharaan Gudang', 'parent_id' => $biayaPemeliharaan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ]);

        // ---- 5007 Biaya Perijinan ----
        $biayaPerijinan = AkunKeuangan::create([
            'kode_akun' => '5007',
            'nama_akun' => 'Biaya Perijinan',
            'parent_id' => $biaya->id,
            'kategori_akun_id' => 5,
            'is_leaf' => false,
        ]);

        AkunKeuangan::insert([
            ['kode_akun' => '5007-1', 'nama_akun' => 'IMB', 'parent_id' => $biayaPerijinan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5007-2', 'nama_akun' => 'Pecah SHGB', 'parent_id' => $biayaPerijinan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5007-3', 'nama_akun' => 'Sert. Induk', 'parent_id' => $biayaPerijinan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5007-4', 'nama_akun' => 'PBB', 'parent_id' => $biayaPerijinan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5007-5', 'nama_akun' => 'PKKPR', 'parent_id' => $biayaPerijinan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5007-6', 'nama_akun' => 'Jembatan', 'parent_id' => $biayaPerijinan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5007-7', 'nama_akun' => 'Pertimbangan Teknis', 'parent_id' => $biayaPerijinan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5007-8', 'nama_akun' => 'Uji Air Bersih', 'parent_id' => $biayaPerijinan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5007-9', 'nama_akun' => 'Bia2 Akomodasi', 'parent_id' => $biayaPerijinan->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ]);

        // ---- 5008 Biaya Kelurahan ----
        AkunKeuangan::create([
            'kode_akun' => '5008',
            'nama_akun' => 'Bia Kelurahan',
            'parent_id' => $biaya->id,
            'kategori_akun_id' => 5,
            'is_leaf' => true,
        ]);

        // ---- 5009 Biaya Lain-lain ----
        AkunKeuangan::create([
            'kode_akun' => '5009',
            'nama_akun' => 'Biaya Lain - Lain',
            'parent_id' => $biaya->id,
            'kategori_akun_id' => 5,
            'is_leaf' => true,
        ]);

        // ---- 5010 Biaya CSR & Sumbangan Warga ----
        AkunKeuangan::create([
            'kode_akun' => '5010',
            'nama_akun' => 'Biaya CSR & Sumbangan Warga',
            'parent_id' => $biaya->id,
            'kategori_akun_id' => 5,
            'is_leaf' => true,
        ]);

        // ---- 5011 Biaya Web & Aplikasi ----
        AkunKeuangan::create([
            'kode_akun' => '5011',
            'nama_akun' => 'Biaya Web & Aplikasi',
            'parent_id' => $biaya->id,
            'kategori_akun_id' => 5,
            'is_leaf' => true,
        ]);

        // ---- 5012 Biaya Arsitek & Konsultan ----
        AkunKeuangan::create([
            'kode_akun' => '5012',
            'nama_akun' => 'Biaya Arsitek & Konsultan',
            'parent_id' => $biaya->id,
            'kategori_akun_id' => 5,
            'is_leaf' => true,
        ]);

        // ---- 5013 Beban Bunga Bank ----
        AkunKeuangan::create([
            'kode_akun' => '5013',
            'nama_akun' => 'Beban Bunga Bank',
            'parent_id' => $biaya->id,
            'kategori_akun_id' => 5,
            'is_leaf' => true,
        ]);

        // ---- 5014 Beban Bunga KYG PPT ----
        AkunKeuangan::create([
            'kode_akun' => '5014',
            'nama_akun' => 'Beban Bunga KYG PPT',
            'parent_id' => $biaya->id,
            'kategori_akun_id' => 5,
            'is_leaf' => true,
        ]);

        // ---- 5015 Biaya Admin dan Provisi ----
        AkunKeuangan::create([
            'kode_akun' => '5015',
            'nama_akun' => 'Biaya Admin dan Provisi',
            'parent_id' => $biaya->id,
            'kategori_akun_id' => 5,
            'is_leaf' => true,
        ]);

        // ---- 5016 Biaya Estate Management ----
        AkunKeuangan::create([
            'kode_akun' => '5016',
            'nama_akun' => 'Biaya Estate Management',
            'parent_id' => $biaya->id,
            'kategori_akun_id' => 5,
            'is_leaf' => true,
        ]);

        // ---- 5017 Pengembalian Uang Muka ----
        AkunKeuangan::create([
            'kode_akun' => '5017',
            'nama_akun' => 'Pengembalian Uang Muka',
            'parent_id' => $biaya->id,
            'kategori_akun_id' => 5,
            'is_leaf' => true,
        ]);

        // ---- 5018 Biaya Operasional Produksi ----
        $biayaOpProduksi = AkunKeuangan::create([
            'kode_akun' => '5018',
            'nama_akun' => 'Biaya Operasional Produksi',
            'parent_id' => $biaya->id,
            'kategori_akun_id' => 5,
            'is_leaf' => false,
        ]);

        AkunKeuangan::insert([
            ['kode_akun' => '5018-1', 'nama_akun' => 'Biaya BBM', 'parent_id' => $biayaOpProduksi->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5018-2', 'nama_akun' => 'Biaya Konsumsi', 'parent_id' => $biayaOpProduksi->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5018-3', 'nama_akun' => 'Biaya Token', 'parent_id' => $biayaOpProduksi->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5018-4', 'nama_akun' => 'Biaya P3K', 'parent_id' => $biayaOpProduksi->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
            ['kode_akun' => '5018-5', 'nama_akun' => 'Biaya Lain-Lain', 'parent_id' => $biayaOpProduksi->id, 'kategori_akun_id' => 5, 'is_leaf' => true],
        ]);


        // ============================
        // 6000 HARGA POKOK PENJUALAN
        // kategori_akun_id = 6
        // ============================

        // 6000 Harga Pokok Penjualan
        $hpp = AkunKeuangan::create([
            'kode_akun' => '6000',
            'nama_akun' => 'Harga Pokok Penjualan',
            'parent_id' => null,
            'kategori_akun_id' => 6,
            'is_leaf' => false,
        ]);

        // ---- 6100 Harga Pokok Penjualan Rumah ----
        AkunKeuangan::create([
            'kode_akun' => '6100',
            'nama_akun' => 'Harga Pokok Penjualan Rumah',
            'parent_id' => $hpp->id,
            'kategori_akun_id' => 6,
            'is_leaf' => true,
        ]);

        // ---- 6200 Harga Pokok Penjualan Tanah ----
        AkunKeuangan::create([
            'kode_akun' => '6200',
            'nama_akun' => 'Harga Pokok Penjualan Tanah',
            'parent_id' => $hpp->id,
            'kategori_akun_id' => 6,
            'is_leaf' => true,
        ]);
        
    }
}
