<?php

namespace Database\Seeders\Permissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PembangunanLainPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // ==========================================
            // 1. Modul Produksi: Pembangunan Lain-lain
            // ==========================================
            'produksi.lain.pembangunan-lain.read',
            'produksi.lain.pembangunan-lain.create',
            'produksi.lain.pembangunan-lain.edit',
            'produksi.lain.pembangunan-lain.delete',
            'produksi.lain.pembangunan-lain.termin',

            // ==========================================
            // 2. Modul Gudang: Permintaan Barang Keluar
            // ==========================================
            'gudang.permintaan-barang.pemb-lain.read',
            'gudang.permintaan-barang.pemb-lain.create',
            'gudang.permintaan-barang.pemb-lain.aksi-stock',
            'gudang.permintaan-barang.pemb-lain.aksi-direct',
            'gudang.permintaan-barang.pemb-lain.aksi-cash',
            'gudang.permintaan-barang.pemb-lain.edit',
            'gudang.permintaan-barang.pemb-lain.delete',
            'gudang.permintaan-barang.pemb-lain.ajukan-kembali',
            'gudang.permintaan-barang.pemb-lain.history',

            // ==========================================
            // 3. Modul Gudang: Retur Material Proyek
            // ==========================================
            'gudang.return-barang.return-lain.read',
            'gudang.return-barang.return-lain.create',
            'gudang.return-barang.return-lain.aksi',
            'gudang.return-barang.return-lain.history',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // Berikan semua hak akses ke Superadmin
        $superadmin = Role::where('name', 'Superadmin')->first();
        if ($superadmin) {
            $superadmin->givePermissionTo($permissions);
        }

        // Catatan: Role lain di-assign secara manual oleh user via UI Manajemen Role / Hak Akses
    }
}

