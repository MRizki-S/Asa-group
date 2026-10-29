<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ubah enum jenis_order di ketiga tabel order agar mendukung 'cash'
        DB::statement("ALTER TABLE `pembangunan_unit_barang_order` MODIFY COLUMN `jenis_order` ENUM('stock', 'direct', 'cash') NOT NULL DEFAULT 'stock'");
        DB::statement("ALTER TABLE `pembangunan_kawasan_barang_order` MODIFY COLUMN `jenis_order` ENUM('stock', 'direct', 'cash') NOT NULL DEFAULT 'stock'");
        DB::statement("ALTER TABLE `pembangunan_proyek_barang_order` MODIFY COLUMN `jenis_order` ENUM('stock', 'direct', 'cash') NOT NULL DEFAULT 'stock'");

        // 2. Registrasi permission baru untuk aksi-cash
        $newPermissions = [
            'gudang.permintaan-barang.pemb-unit.aksi-cash',
            'gudang.permintaan-barang.pemb-kawasan.aksi-cash',
            'gudang.permintaan-barang.pemb-mangoon.aksi-cash',
            'gudang.permintaan-barang.aksi-cash',
        ];

        foreach ($newPermissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm],
                ['guard_name' => 'web']
            );
        }

        // Reset permission cache agar langsung terbaca di session/auth
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `pembangunan_unit_barang_order` MODIFY COLUMN `jenis_order` ENUM('stock', 'direct') NOT NULL DEFAULT 'stock'");
        DB::statement("ALTER TABLE `pembangunan_kawasan_barang_order` MODIFY COLUMN `jenis_order` ENUM('stock', 'direct') NOT NULL DEFAULT 'stock'");
        DB::statement("ALTER TABLE `pembangunan_proyek_barang_order` MODIFY COLUMN `jenis_order` ENUM('stock', 'direct') NOT NULL DEFAULT 'stock'");

        $newPermissions = [
            'gudang.permintaan-barang.pemb-unit.aksi-cash',
            'gudang.permintaan-barang.pemb-kawasan.aksi-cash',
            'gudang.permintaan-barang.pemb-mangoon.aksi-cash',
            'gudang.permintaan-barang.aksi-cash',
        ];

        Permission::whereIn('name', $newPermissions)->delete();
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
