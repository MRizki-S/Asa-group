<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE pengajuan_pembangunan_unit MODIFY COLUMN status_pengajuan ENUM('pending', 'dibangun', 'selesai') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE pengajuan_pembangunan_unit MODIFY COLUMN status_pengajuan ENUM('pending', 'dibangun') NOT NULL DEFAULT 'pending'");
    }
};
