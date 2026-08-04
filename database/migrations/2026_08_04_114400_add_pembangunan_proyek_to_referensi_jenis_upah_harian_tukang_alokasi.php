<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('upah_harian_tukang_alokasi')) {
            DB::statement("ALTER TABLE `upah_harian_tukang_alokasi` MODIFY COLUMN `referensi_jenis` ENUM('pembangunan_unit', 'pembangunan_kawasan', 'pembangunan_proyek') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('upah_harian_tukang_alokasi')) {
            DB::statement("ALTER TABLE `upah_harian_tukang_alokasi` MODIFY COLUMN `referensi_jenis` ENUM('pembangunan_unit', 'pembangunan_kawasan') NOT NULL");
        }
    }
};
