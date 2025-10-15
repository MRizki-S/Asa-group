<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pemesanan_unit_cicilan', function (Blueprint $table) {
            // 1️ Hapus foreign key lama
            $table->dropForeign(['pemesanan_unit_cara_bayar_id']);

            // 2️ Hapus kolom lama
            $table->dropColumn('pemesanan_unit_cara_bayar_id');

            // 3️ Tambah kolom baru untuk relasi ke pemesanan_unit
            $table->foreignId('pemesanan_unit_id')
                ->after('id')
                ->constrained('pemesanan_unit')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan_unit_cicilan', function (Blueprint $table) {
            // rollback ke struktur lama (optional)
            $table->dropForeign(['pemesanan_unit_id']);
            $table->dropColumn('pemesanan_unit_id');

            $table->foreignId('pemesanan_unit_cara_bayar_id')
                ->constrained('pemesanan_unit_cara_bayar')
                ->onDelete('cascade');
        });
    }
};

