<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adendum', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('pemesanan_unit_id');
            $table->enum('jenis', ['cara_bayar', 'ganti_unit', 'promo', 'combo']);
            $table->json('jenis_list')->nullable();

            $table->enum('status', ['pending', 'acc', 'tolak'])->default('pending');

            $table->unsignedBigInteger('diajukan_oleh');
            $table->unsignedBigInteger('disetujui_oleh')->nullable();

            $table->datetime('tanggal_adendum');

            $table->timestamps();

            // Foreign keys
            $table->foreign('pemesanan_unit_id')
                ->references('id')->on('pemesanan_unit')
                ->onDelete('cascade');

            $table->foreign('diajukan_oleh')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('disetujui_oleh')
                ->references('id')->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adendum');
    }
};
