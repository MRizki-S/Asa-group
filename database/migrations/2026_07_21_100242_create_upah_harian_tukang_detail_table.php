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
        Schema::create('upah_harian_tukang_detail', function (Blueprint $table) {
            $table->id();

            $table->foreignId('upah_harian_tukang_id')
                ->constrained('upah_harian_tukang')
                ->cascadeOnDelete();

            $table->foreignId('tukang_id')
                ->constrained('master_tukang')
                ->restrictOnDelete();

            $table->date('tanggal');

            $table->boolean('status_kehadiran')->default(true);

            $table->decimal('gaji_harian_default_snapshot', 15, 2);

            $table->unsignedTinyInteger('jam_default_snapshot');

            $table->decimal('nominal_harian_final', 15, 2);

            $table->unsignedTinyInteger('jam_kerja')->default(0);

            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->unique(
                [
                    'upah_harian_tukang_id',
                    'tukang_id',
                    'tanggal'
                ],
                'uq_upah_tukang_detail'
            );

            $table->index('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upah_harian_tukang_detail');
    }
};