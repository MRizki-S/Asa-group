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
        Schema::create('master_tukang', function (Blueprint $table) {
            $table->id();

            $table->string('kode')->unique();
            $table->string('nama_tukang');

            $table->decimal('gaji_harian_default', 15, 2)->default(0);
            $table->unsignedTinyInteger('jam_kerja_default')->default(8);

            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_tukang');
    }
};