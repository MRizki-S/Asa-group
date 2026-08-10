<?php

namespace Database\Seeders\Permissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ProduksiPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // -- Properti: Master QC & RAP --
            'produksi.properti.master-qc-rap.read',
            'produksi.properti.master-qc-rap.detail',
            'produksi.properti.master-qc-rap.create',
            'produksi.properti.master-qc-rap.edit',
            'produksi.properti.master-qc-rap.delete',

            // -- Properti: Permintaan Dibangun --
            'produksi.properti.permintaan-dibangun.read',
            'produksi.properti.permintaan-dibangun.confirm',
            'produksi.properti.permintaan-dibangun.edit',
            'produksi.properti.permintaan-dibangun.batal',

            // -- Properti: Pembangunan Unit --
            'produksi.properti.pembangunan-unit.read',
            'produksi.properti.pembangunan-unit.detail',
            'produksi.properti.pembangunan-unit.edit-task',
            'produksi.properti.pembangunan-unit.edit-status',
            'produksi.properti.pembangunan-unit.edit-serah-terima',
            'produksi.properti.pembangunan-unit.termin',
            'produksi.properti.pembangunan-unit.akumulasi-barang',
            'produksi.properti.pembangunan-unit.akumulasi-upah',
            'produksi.properti.pembangunan-unit.read-servis',
            'produksi.properti.pembangunan-unit.create-servis',

            // -- Kawasan: Buat Pembangunan --
            'produksi.kawasan.buat-pembangunan.read',
            'produksi.kawasan.buat-pembangunan.create',
            'produksi.kawasan.buat-pembangunan.edit',
            'produksi.kawasan.buat-pembangunan.delete',
            'produksi.kawasan.buat-pembangunan.create-periode',

            // -- Kawasan: Pembangunan Kawasan --
            'produksi.kawasan.pembangunan-kawasan.read',
            'produksi.kawasan.pembangunan-kawasan.detail',
            'produksi.kawasan.pembangunan-kawasan.edit-status',
            'produksi.kawasan.pembangunan-kawasan.termin',

            // -- Kontraktor: Proyek Baru --
            'produksi.kontraktor.proyek-baru.read',
            'produksi.kontraktor.proyek-baru.create',
            'produksi.kontraktor.proyek-baru.edit',
            'produksi.kontraktor.proyek-baru.delete',
            'produksi.kontraktor.proyek-baru.proses',

            // -- Kontraktor: Pembangunan Proyek --
            'produksi.kontraktor.pembangunan-proyek.read',
            'produksi.kontraktor.pembangunan-proyek.detail',
            'produksi.kontraktor.pembangunan-proyek.edit-status',
            'produksi.kontraktor.pembangunan-proyek.termin',

            // -- Manajemen Upah: Penamaan Upah --
            'produksi.manajemen-upah.penamaan-upah.read',
            'produksi.manajemen-upah.penamaan-upah.create',
            'produksi.manajemen-upah.penamaan-upah.edit',
            'produksi.manajemen-upah.penamaan-upah.delete',

            // -- Manajemen Upah: Upah Borongan (Produksi) --
            'produksi.manajemen-upah.upah-borongan.read',
            'produksi.manajemen-upah.upah-borongan.confirm',

            // -- Keuangan: Upah Borongan (Manager & Akuntan) --
            'keuangan.upah-borongan.manager.read',
            'keuangan.upah-borongan.manager.confirm',
            'keuangan.upah-borongan.akuntan.read',
            'keuangan.upah-borongan.akuntan.confirm',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }
    }
}
