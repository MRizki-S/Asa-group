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
        Schema::create('unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perumahaan_id')->constrained('perumahaan')->onDelete('cascade');
            $table->foreignId('tahap_id')->constrained('tahap')->onDelete('cascade');
            $table->foreignId('blok_id')->constrained('blok')->onDelete('cascade');
            $table->foreignId('type_id')->constrained('type')->onDelete('cascade');
            $table->string('nama_unit');
            $table->string('slug')->unique();
            $table->enum('kualifikasi_dasar', ['standar', 'kelebihan_tanah'])
                            ->default('standar');
            $table->string('luas_kelebihan', 50)->nullable();
            $table->decimal('nominal_kelebihan', 15, 2)->nullable();
            $table->foreignId('tahap_kualifikasi_id')->nullable()->constrained('tahap_kualifikasi')->onDelete('set null');
            $table->enum('status_unit', ['available', 'booked', 'sold'])->default('available');
            $table->decimal('harga_final', 15, 2);
            $table->decimal('harga_jual', 15, 2)->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit');
    }
};
