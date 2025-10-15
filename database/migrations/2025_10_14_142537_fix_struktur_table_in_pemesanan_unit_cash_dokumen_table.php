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
         Schema::table('pemesanan_unit_cash_dokumen', function (Blueprint $table) {
            // Pastikan foreign key-nya di-drop dulu sebelum hapus kolom
            if (Schema::hasColumn('pemesanan_unit_cash_dokumen', 'pemesanan_unit_id')) {
                $table->dropForeign(['pemesanan_unit_id']);
                $table->dropColumn('pemesanan_unit_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemesanan_unit_cash_dokumen', function (Blueprint $table) {
            //
        });
    }
};
