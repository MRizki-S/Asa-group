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
        // 1. Master Induk Pembangunan Lain-lain
        Schema::create('pembangunan_lain', function (Blueprint $table) {
            $table->id();
            $table->string('nama_proyek');
            $table->enum('status', ['pending', 'proses', 'selesai'])->default('proses');
            $table->softDeletes();
            $table->timestamps();
        });

        // 2. Sub-Pekerjaan Pembangunan Lain-lain (Wajib, 1 Induk minimal memiliki 1 Sub)
        Schema::create('pembangunan_lain_sub', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembangunan_lain_id')->constrained('pembangunan_lain')->cascadeOnDelete();
            $table->string('nama_sub');
            $table->enum('status', ['pending', 'proses', 'selesai'])->default('proses');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->text('catatan')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('pembangunan_lain_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembangunan_lain_sub');
        Schema::dropIfExists('pembangunan_lain');
    }
};

