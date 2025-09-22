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
        Schema::create('blok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perumahaan_id')->constrained('perumahaan')->onDelete('cascade');
            $table->foreignId('tahap_id')->constrained('tahap')->onDelete('cascade');
            $table->string('nama_blok');
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blok');
    }
};
