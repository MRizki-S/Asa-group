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
        Schema::create('pemesanan_unit_bonus_kpr', function (Blueprint $table) {
           $table->id();
            $table->foreignId('pemesanan_unit_id')->nullable()->constrained('pemesanan_unit')->onDelete('cascade');
            $table->string('nama_bonus')->nullable(); // bisa kosong kalau tidak ada data
            $table->decimal('nominal_bonus', 15, 2)->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanan_unit_bonus_kpr');
    }
};
