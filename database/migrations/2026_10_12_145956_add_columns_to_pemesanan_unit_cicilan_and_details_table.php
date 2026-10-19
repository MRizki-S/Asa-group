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
        Schema::table('pemesanan_unit_cicilan', function (Blueprint $table) {
            $table->string('tipe_cicilan', 50)->default('dp')->after('status_bayar');
            $table->string('keterangan')->nullable()->after('tipe_cicilan');
        });

        Schema::table('pemesanan_unit_kpr', function (Blueprint $table) {
            $table->decimal('total_biaya_administrasi', 15, 2)->default(0)->after('harga_total');
        });

        Schema::table('pemesanan_unit_cash', function (Blueprint $table) {
            $table->decimal('total_tambahan', 15, 2)->default(0)->after('harga_jadi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemesanan_unit_cicilan', function (Blueprint $table) {
            $table->dropColumn(['tipe_cicilan', 'keterangan']);
        });

        Schema::table('pemesanan_unit_kpr', function (Blueprint $table) {
            $table->dropColumn('total_biaya_administrasi');
        });

        Schema::table('pemesanan_unit_cash', function (Blueprint $table) {
            $table->dropColumn('total_tambahan');
        });
    }
};
