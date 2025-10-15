<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan_unit_cash_dokumen', function (Blueprint $table) {
            // Tambah kolom relasi baru kalau belum ada
            if (!Schema::hasColumn('pemesanan_unit_cash_dokumen', 'pemesanan_unit_cash_id')) {
                $table->foreignId('pemesanan_unit_cash_id')
                      ->after('id')
                      ->nullable()
                      ->constrained('pemesanan_unit_cash')
                      ->onDelete('cascade');
            }
        });
    }   

    public function down(): void
    {
        Schema::table('pemesanan_unit_cash_dokumen', function (Blueprint $table) {
            if (Schema::hasColumn('pemesanan_unit_cash_dokumen', 'pemesanan_unit_cash_id')) {
                $table->dropForeign(['pemesanan_unit_cash_id']);
                $table->dropColumn('pemesanan_unit_cash_id');
            }
        });
    }
};
