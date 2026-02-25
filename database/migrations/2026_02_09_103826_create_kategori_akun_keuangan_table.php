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
         Schema::create('kategori_akun_keuangan', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama'); // ASET, BEBAN, dll
            $table->enum('normal_balance', ['debit', 'kredit']);
            $table->enum('laporan', ['neraca', 'laba_rugi']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_akun_keuangan');
    }
};
