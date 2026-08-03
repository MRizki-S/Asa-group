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
        Schema::create('upah_harian_tukang_alokasi', function (Blueprint $table) {
            $table->id();

            $table->foreignId('upah_harian_tukang_detail_id')
                ->constrained('upah_harian_tukang_detail')
                ->cascadeOnDelete();

            $table->enum('referensi_jenis', [
                'pembangunan_unit',
                'pembangunan_kawasan',
                'pembangunan_proyek',
            ]);

            // ID mengikuti referensi_jenis
            $table->unsignedBigInteger('referensi_id');

            $table->enum('jenis', [
                'normal',
                'lembur',
            ]);

            $table->unsignedTinyInteger('jam_kerja');

            $table->decimal('tarif_per_jam', 15, 2);

            $table->decimal('subtotal', 15, 2);

            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->index(
                [
                    'referensi_jenis',
                    'referensi_id'
                ],
                'idx_uht_alokasi_ref'
            );

            $table->index('jenis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upah_harian_tukang_alokasi');
    }
};