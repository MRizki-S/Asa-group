<?php

namespace Database\Seeders\Permissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class MenuGudangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Stock Gudang
            'gudang.stock-barang.read',
            'gudang.stock-barang.freeze-stock',

            // Audit Log Stock
            'gudang.audit-log.read',

            // Transfer Stock
            'gudang.transfer-stock.read',
            'gudang.transfer-stock.create',
            'gudang.transfer-stock.delete',
            'gudang.transfer-stock.detail',
            'gudang.transfer-stock.action',
            'gudang.transfer-stock.edit-pengajuan',
            'gudang.transfer-stock.print-pdf',

            // Barang Rusak
            'gudang.barang-rusak.read',
            'gudang.barang-rusak.create',
            'gudang.barang-rusak.kembalikan-ke-stok',

            // Barang Rakitan - Komposisi Rakitan
            'gudang.barang-rakitan.komposisi-rakitan.read',
            'gudang.barang-rakitan.komposisi-rakitan.create',
            'gudang.barang-rakitan.komposisi-rakitan.edit',
            'gudang.barang-rakitan.komposisi-rakitan.delete',

            // Barang Rakitan - Produksi Rakitan
            'gudang.barang-rakitan.produksi-rakitan.read',
            'gudang.barang-rakitan.produksi-rakitan.create',
            'gudang.barang-rakitan.produksi-rakitan.detail',
            'gudang.barang-rakitan.produksi-rakitan.delete',

            // Master Gudang - Master Supplier
            'gudang.master-gudang.master-supplier.read',
            'gudang.master-gudang.master-supplier.create',
            'gudang.master-gudang.master-supplier.edit',
            'gudang.master-gudang.master-supplier.delete',

            // Master Gudang - Master Satuan
            'gudang.master-gudang.master-satuan.read',
            'gudang.master-gudang.master-satuan.create',
            'gudang.master-gudang.master-satuan.edit',
            'gudang.master-gudang.master-satuan.delete',

            // Master Gudang - Master Barang
            'gudang.master-gudang.master-barang.read',
            'gudang.master-gudang.master-barang.create',
            'gudang.master-gudang.master-barang.edit',
            'gudang.master-gudang.master-barang.delete',

            // Nota Masuk - Tambah Nota Masuk
            'gudang.nota-masuk.tambah.read',
            'gudang.nota-masuk.tambah.submit-draft',

            // Nota Masuk - Daftar Nota Masuk
            'gudang.nota-masuk.daftar-nota-masuk.read',
            'gudang.nota-masuk.daftar-nota-masuk.detail',

            // Nota Masuk - Draft Nota Masuk
            'gudang.nota-masuk.draft-nota-masuk.read',
            'gudang.nota-masuk.draft-nota-masuk.edit',
            'gudang.nota-masuk.draft-nota-masuk.update-perubahan',
            'gudang.nota-masuk.draft-nota-masuk.posting-to-stok',
            'gudang.nota-masuk.draft-nota-masuk.delete-draft',

            // Permintaan Order - Pembangunan Unit
            'gudang.permintaan-barang.pemb-unit.read',
            'gudang.permintaan-barang.pemb-unit.create',
            'gudang.permintaan-barang.pemb-unit.aksi',
            'gudang.permintaan-barang.pemb-unit.edit',
            'gudang.permintaan-barang.pemb-unit.delete',
            'gudang.permintaan-barang.pemb-unit.ajukan-kembali',
            'gudang.permintaan-barang.pemb-unit.history',

            // Permintaan Order - Pembangunan Kawasan
            'gudang.permintaan-barang.pemb-kawasan.read',
            'gudang.permintaan-barang.pemb-kawasan.create',
            'gudang.permintaan-barang.pemb-kawasan.aksi',
            'gudang.permintaan-barang.pemb-kawasan.edit',
            'gudang.permintaan-barang.pemb-kawasan.delete',
            'gudang.permintaan-barang.pemb-kawasan.ajukan-kembali',
            'gudang.permintaan-barang.pemb-kawasan.history',

            // Permintaan Order - Pembangunan Mangoon
            'gudang.permintaan-barang.pemb-mangoon.read',
            'gudang.permintaan-barang.pemb-mangoon.create',
            'gudang.permintaan-barang.pemb-mangoon.aksi',
            'gudang.permintaan-barang.pemb-mangoon.edit',
            'gudang.permintaan-barang.pemb-mangoon.delete',
            'gudang.permintaan-barang.pemb-mangoon.ajukan-kembali',
            'gudang.permintaan-barang.pemb-mangoon.history',

            // Return Barang - Return Unit
            'gudang.return-barang.return-unit.read',
            'gudang.return-barang.return-unit.create',
            'gudang.return-barang.return-unit.aksi',
            'gudang.return-barang.return-unit.history',

            // Return Barang - Return Kawasan
            'gudang.return-barang.return-kawasan.read',
            'gudang.return-barang.return-kawasan.create',
            'gudang.return-barang.return-kawasan.aksi',
            'gudang.return-barang.return-kawasan.history',

            // Return Barang - Return Mangoon
            'gudang.return-barang.return-mangoon.read',
            'gudang.return-barang.return-mangoon.create',
            'gudang.return-barang.return-mangoon.aksi',
            'gudang.return-barang.return-mangoon.history',

            // Upah Harian Tukang - Master Tukang
            'gudang.upah-harian-tukang.master-tukang.read',
            'gudang.upah-harian-tukang.master-tukang.create',
            'gudang.upah-harian-tukang.master-tukang.edit',
            'gudang.upah-harian-tukang.master-tukang.delete',

            // Upah Harian Tukang - Upah ABM
            'gudang.upah-harian-tukang.upah-abm.read',
            'gudang.upah-harian-tukang.upah-abm.detail',
            'gudang.upah-harian-tukang.upah-abm.create',
            'gudang.upah-harian-tukang.upah-abm.simpan-draft',
            'gudang.upah-harian-tukang.upah-abm.edit',
            'gudang.upah-harian-tukang.upah-abm.simpan-ajukan',
            'gudang.upah-harian-tukang.upah-abm.batalkan-pengajuan',

            // Upah Harian Tukang - Upah Mangoon
            'gudang.upah-harian-tukang.upah-mangoon.read',
            'gudang.upah-harian-tukang.upah-mangoon.detail',
            'gudang.upah-harian-tukang.upah-mangoon.create',
            'gudang.upah-harian-tukang.upah-mangoon.simpan-draft',
            'gudang.upah-harian-tukang.upah-mangoon.edit',
            'gudang.upah-harian-tukang.upah-mangoon.simpan-ajukan',
            'gudang.upah-harian-tukang.upah-mangoon.batalkan-pengajuan',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }
    }
}
