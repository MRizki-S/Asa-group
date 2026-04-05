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
        Schema::create('kpi_user_komponen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_user_id')->constrained('kpi_user')->onDelete('cascade');
            $table->foreignId('komponen_id')->constrained('kpi_komponen')->onDelete('cascade');
            $table->string('nama_komponen');
            $table->integer('bobot')->default(0);
            $table->decimal('total_target', 8, 2)->nullable();
            $table->decimal('total_tercapai', 8, 2)->nullable();
            $table->decimal('kepatuhan_percent', 8, 2)->nullable();
            $table->decimal('skor', 8, 2)->nullable();
            $table->decimal('nilai_akhir', 8, 2)->nullable();
            $table->text('catatan_tambahan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_user_komponen');
    }
};
