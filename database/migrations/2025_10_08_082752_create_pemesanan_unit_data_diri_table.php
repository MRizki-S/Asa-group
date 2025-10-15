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
        Schema::create('pemesanan_unit_data_diri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_unit_id')->constrained('pemesanan_unit')->onDelete('cascade');
            $table->string('nama_pribadi');
            $table->string('no_hp');
            $table->string('provinsi_code');
            $table->string('provinsi_nama');
            $table->string('kota_code');
            $table->string('kota_nama');
            $table->string('kecamatan_code');
            $table->string('kecamatan_nama');
            $table->string('desa_code');
            $table->string('desa_nama');
            $table->string('rt');
            $table->string('rw');
            $table->text('alamat_detail');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanan_unit_data_diri');
    }
};
