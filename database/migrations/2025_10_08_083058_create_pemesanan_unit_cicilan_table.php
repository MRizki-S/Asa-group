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
        Schema::create('pemesanan_unit_cicilan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_unit_cara_bayar_id')->constrained('pemesanan_unit_cara_bayar')->onDelete('cascade');
            $table->integer('pembayaran_ke');
            $table->date('tanggal_jatuh_tempo');
            $table->decimal('nominal', 15, 2);
            $table->enum('status_bayar', ['pending', 'lunas', 'telat'])->default('pending');
            $table->date('tanggal_pembayaran')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanan_unit_cicilan');
    }
};
