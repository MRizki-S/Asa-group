<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pembangunan_kawasan_termin_upah_harian', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('pembangunan_kawasan_id');
            $table->unsignedBigInteger('upah_harian_tukang_alokasi_id');
            $table->unsignedBigInteger('tukang_id');

            $table->date('tanggal');

            $table->enum('jenis', [
                'normal',
                'lembur',
            ]);

            $table->unsignedTinyInteger('jam_kerja');

            $table->decimal('nominal', 15, 2);

            $table->timestamps();

            // Foreign Key
            $table->foreign('pembangunan_kawasan_id', 'fk_pktuh_kawasan')
                ->references('id')
                ->on('pembangunan_kawasan')
                ->cascadeOnDelete();

            $table->foreign('upah_harian_tukang_alokasi_id', 'fk_pktuh_alokasi')
                ->references('id')
                ->on('upah_harian_tukang_alokasi')
                ->restrictOnDelete();

            $table->foreign('tukang_id', 'fk_pktuh_tukang')
                ->references('id')
                ->on('master_tukang')
                ->restrictOnDelete();

            // Index
            $table->index(
                ['pembangunan_kawasan_id', 'tanggal'],
                'idx_pktuh_kawasan_tgl'
            );

            $table->index(
                ['tukang_id', 'tanggal'],
                'idx_pktuh_tukang_tgl'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembangunan_kawasan_termin_upah_harian');
    }
};
