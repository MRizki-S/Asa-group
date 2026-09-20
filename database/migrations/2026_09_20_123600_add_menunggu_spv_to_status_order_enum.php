<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `pembangunan_unit_barang_order` MODIFY COLUMN `status_order` ENUM('diproses', 'menunggu_spv', 'selesai', 'ditolak', 'pengembalian') NOT NULL DEFAULT 'diproses'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `pembangunan_unit_barang_order` MODIFY COLUMN `status_order` ENUM('diproses', 'selesai', 'ditolak', 'pengembalian') NOT NULL DEFAULT 'diproses'");
    }
};
