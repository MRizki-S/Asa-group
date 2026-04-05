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
        Schema::create('kpi_komponen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->string('nama_komponen');
            $table->enum('tipe_perhitungan', ['KEPATUHAN', 'DEVIASI_BUDGET', "SELISIH_STOK", "KONDISI_LANGSUNG", "AKKUMULASI_NILAI"]);
            $table->string('label_total');
            $table->string('label_tercapai');
            $table->string('label_tidak_tercapai');
            $table->boolean('is_active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_komponen');
    }
};
