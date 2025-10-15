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
        Schema::create('pemesanan_unit_cash', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_unit_id')->constrained('pemesanan_unit')->onDelete('cascade');
            $table->decimal('harga_rumah', 15, 2);
            $table->decimal('luas_kelebihan', 15, 2)->nullable();
            $table->decimal('nominal_kelebihan', 15, 2)->nullable();
            $table->decimal('harga_jadi', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanan_unit_cash');
    }
};
