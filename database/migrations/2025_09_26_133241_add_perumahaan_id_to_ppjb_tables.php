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
        // Users → hanya untuk karyawan (kalau type = customer boleh null)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('perumahaan_id')
                ->nullable()
                ->after('type')
                ->constrained('perumahaan')
                ->nullOnDelete();
        });

        // Cara Bayar
        Schema::table('ppjb_cara_bayar', function (Blueprint $table) {
            $table->foreignId('perumahaan_id')
                ->after('minimal_dp')
                ->constrained('perumahaan')
                ->cascadeOnDelete();
        });

        // Keterlambatan
        Schema::table('ppjb_keterlambatan', function (Blueprint $table) {
            $table->foreignId('perumahaan_id')
                ->after('persentase_denda')
                ->constrained('perumahaan')
                ->cascadeOnDelete();
        });

        // Pembatalan
        Schema::table('ppjb_pembatalan', function (Blueprint $table) {
            $table->foreignId('perumahaan_id')
                ->after('persentase_potongan')
                ->constrained('perumahaan')
                ->cascadeOnDelete();
        });

        // Promo Batch
        Schema::table('ppjb_promo_batch', function (Blueprint $table) {
            $table->foreignId('perumahaan_id')
                ->after('tipe')
                ->constrained('perumahaan')
                ->cascadeOnDelete();
        });

        // Mutu Batch
        Schema::table('ppjb_mutu_batch', function (Blueprint $table) {
            $table->foreignId('perumahaan_id')
                ->after('id')
                ->constrained('perumahaan')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('perumahaan_id');
        });

        Schema::table('ppjb_cara_bayar', function (Blueprint $table) {
            $table->dropConstrainedForeignId('perumahaan_id');
        });

        Schema::table('ppjb_keterlambatan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('perumahaan_id');
        });

        Schema::table('ppjb_pembatalan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('perumahaan_id');
        });

        Schema::table('ppjb_promo_batch', function (Blueprint $table) {
            $table->dropConstrainedForeignId('perumahaan_id');
        });

        Schema::table('ppjb_mutu_batch', function (Blueprint $table) {
            $table->dropConstrainedForeignId('perumahaan_id');
        });
    }
};
