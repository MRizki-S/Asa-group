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
        Schema::create('kpi_indicators', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe_perhitungan', ['KEPATUHAN', 'DEVIASI_BUDGET', "SELISIH_STOK", "KONDISI_LANGSUNG", "AKKUMULASI_NILAI"]);
            $table->enum('tipe_indikator', ['range', 'select'])->default('range');
            $table->integer('skor')->nullable();
            $table->decimal('batas_atas', 8, 2)->nullable();
            $table->decimal('batas_bawah', 8, 2)->nullable();
            $table->string('option')->nullable();
            $table->decimal('nilai', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_indicators');
    }
};
