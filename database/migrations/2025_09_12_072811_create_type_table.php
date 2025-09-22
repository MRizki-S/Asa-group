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
        Schema::create('type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perumahaan_id')->constrained('perumahaan')->onDelete('cascade');
            $table->string('nama_type');
            $table->string('slug')->unique();
            $table->decimal('luas_bangunan', 10, 2);
            $table->decimal('luas_tanah', 10, 2);
            $table->decimal('harga_dasar', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type');
    }
};
