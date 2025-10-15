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
        Schema::create('pemesanan_unit_cara_bayar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_unit_id')->constrained('pemesanan_unit')->onDelete('cascade');
            $table->integer('jumlah_cicilan');
            $table->decimal('minimal_dp', 15, 2);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanan_unit_cara_bayar');
    }
};
