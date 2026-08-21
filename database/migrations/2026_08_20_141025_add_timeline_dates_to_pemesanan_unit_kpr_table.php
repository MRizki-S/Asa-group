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
        Schema::table('pemesanan_unit_kpr', function (Blueprint $table) {
            $table->date('tanggal_masuk_berkas')->nullable()->after('status_kpr');
            $table->date('tanggal_acc')->nullable()->after('tanggal_masuk_berkas');
            $table->date('tanggal_realisasi')->nullable()->after('tanggal_acc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemesanan_unit_kpr', function (Blueprint $table) {
            $table->dropColumn(['tanggal_masuk_berkas', 'tanggal_acc', 'tanggal_realisasi']);
        });
    }
};
