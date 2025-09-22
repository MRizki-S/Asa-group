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
            Schema::create('kualifikasi_blok', function (Blueprint $table) {
                $table->id();
                $table->string('nama_kualifikasi_blok');
                $table->string('slug')->unique();
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kualifikasi_blok_table');
    }
};
