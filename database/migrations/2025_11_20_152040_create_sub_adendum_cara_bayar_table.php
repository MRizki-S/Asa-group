<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_adendum_cara_bayar', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('adendum_id');
            $table->unsignedBigInteger('pemesanan_unit_id');

            // Cara bayar lama & baru
            $table->string('cara_bayar_lama')->nullable(); // kpr/cash
            $table->string('cara_bayar_baru');            // kpr/cash

            // Snapshot data lama & baru
            $table->json('data_lama_json')->nullable();
            $table->json('data_baru_json')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('adendum_id')
                ->references('id')->on('adendum')
                ->onDelete('cascade');

            $table->foreign('pemesanan_unit_id')
                ->references('id')->on('pemesanan_unit')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_adendum_cara_bayar');
    }
};
