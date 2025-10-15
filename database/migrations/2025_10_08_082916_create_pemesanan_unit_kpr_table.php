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
        Schema::create('pemesanan_unit_kpr', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_unit_id')->constrained('pemesanan_unit')->onDelete('cascade');
            $table->decimal('dp_rumah_induk', 15, 2)->default(0);
            $table->decimal('dp_dibayarkan_pembeli', 15, 2)->default(0);
            $table->decimal('sbum_dari_pemerintah', 15, 2)->default(0);
            $table->decimal('luas_kelebihan', 10, 2)->nullable();
            $table->decimal('nominal_kelebihan', 15, 2)->nullable();
            $table->decimal('total_dp', 15, 2)->default(0);
            $table->decimal('harga_kpr', 15, 2)->default(0);
            $table->decimal('harga_total', 15, 2)->default(0);
            $table->enum('status_kpr', ['proses', 'acc', 'realisasi'])->default('proses');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanan_unit_kpr');
    }
};
