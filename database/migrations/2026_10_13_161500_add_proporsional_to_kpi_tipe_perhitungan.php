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
        // 1. Update ENUM di tabel kpi_komponen
        DB::statement("ALTER TABLE `kpi_komponen` MODIFY COLUMN `tipe_perhitungan` ENUM('KEPATUHAN', 'DEVIASI_BUDGET', 'SELISIH_STOK', 'KONDISI_LANGSUNG', 'AKKUMULASI_NILAI', 'PROPORSIONAL') NOT NULL");

        // 2. Update ENUM di tabel kpi_indicators
        DB::statement("ALTER TABLE `kpi_indicators` MODIFY COLUMN `tipe_perhitungan` ENUM('KEPATUHAN', 'DEVIASI_BUDGET', 'SELISIH_STOK', 'KONDISI_LANGSUNG', 'AKKUMULASI_NILAI', 'PROPORSIONAL') NOT NULL");

        // 3. Tambahkan 1 record referensi mode 'range' di tabel kpi_indicators
        $exists = DB::table('kpi_indicators')->where('tipe_perhitungan', 'PROPORSIONAL')->exists();
        if (!$exists) {
            DB::table('kpi_indicators')->insert([
                'tipe_perhitungan' => 'PROPORSIONAL',
                'tipe_indikator'   => 'range',
                'skor'             => null,
                'batas_bawah'      => null,
                'batas_atas'       => null,
                'option'           => 'Proporsional (Prorata Target)',
                'nilai'            => null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('kpi_indicators')->where('tipe_perhitungan', 'PROPORSIONAL')->delete();
        DB::statement("ALTER TABLE `kpi_komponen` MODIFY COLUMN `tipe_perhitungan` ENUM('KEPATUHAN', 'DEVIASI_BUDGET', 'SELISIH_STOK', 'KONDISI_LANGSUNG', 'AKKUMULASI_NILAI') NOT NULL");
        DB::statement("ALTER TABLE `kpi_indicators` MODIFY COLUMN `tipe_perhitungan` ENUM('KEPATUHAN', 'DEVIASI_BUDGET', 'SELISIH_STOK', 'KONDISI_LANGSUNG', 'AKKUMULASI_NILAI') NOT NULL");
    }
};
