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
        Schema::create('pembangunan_unit_termin_upah_harian', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('pembangunan_unit_id');

            $table->foreign('pembangunan_unit_id', 'fk_putuh_unit')
                ->references('id')
                ->on('pembangunan_unit')
                ->cascadeOnDelete();


            $table->unsignedBigInteger('upah_harian_tukang_alokasi_id');

            $table->foreign('upah_harian_tukang_alokasi_id', 'fk_putuh_alokasi')
                ->references('id')
                ->on('upah_harian_tukang_alokasi')
                ->restrictOnDelete();


            $table->unsignedBigInteger('tukang_id');

            $table->foreign('tukang_id', 'fk_putuh_tukang')
                ->references('id')
                ->on('master_tukang')
                ->restrictOnDelete();

            $table->date('tanggal');

            $table->enum('jenis', [
                'normal',
                'lembur',
            ]);

            $table->unsignedTinyInteger('jam_kerja');

            $table->decimal('nominal', 15, 2);

            $table->timestamps();

            // index
            $table->index(
                ['pembangunan_unit_id', 'tanggal'],
                'idx_putuh_unit_tgl'
            );

            $table->index(
                ['tukang_id', 'tanggal'],
                'idx_putuh_tukang_tgl'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembangunan_unit_termin_upah_harian');
    }
};