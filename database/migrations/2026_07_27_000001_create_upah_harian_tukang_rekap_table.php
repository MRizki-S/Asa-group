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
        Schema::create('upah_harian_tukang_rekap', function (Blueprint $table) {
            $table->id();

            $table->foreignId('upah_harian_tukang_id')
                ->constrained('upah_harian_tukang')
                ->cascadeOnDelete();

            $table->foreignId('tukang_id')
                ->constrained('master_tukang')
                ->restrictOnDelete();

            $table->decimal('total_upah_normal', 15, 2)->default(0);
            $table->decimal('total_upah_lembur', 15, 2)->default(0);
            $table->decimal('total_upah', 15, 2)->default(0);

            // Bon / Pinjaman — diisi oleh keuangan
            $table->decimal('bon', 15, 2)->default(0);

            // Diterima = total_upah - bon
            $table->decimal('total_diterima', 15, 2)->default(0);

            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->unique(
                ['upah_harian_tukang_id', 'tukang_id'],
                'uq_uht_rekap'
            );

            $table->index('upah_harian_tukang_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upah_harian_tukang_rekap');
    }
};
