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
        Schema::create('upah_harian_tukang', function (Blueprint $table) {
            $table->id();

            $table->string('nomor_upah_harian')->unique();

            $table->enum('jenis_referensi', [
                'perumahan',
                'mangoon',
            ]);

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');

            $table->enum('status', [
                'draft',
                'diajukan',
                'disetujui',
                'ditolak'
            ])->default('draft');

            $table->text('alasan_penolakan')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('jenis_referensi');

            $table->index([
                'tanggal_mulai',
                'tanggal_selesai'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upah_harian_tukang');
    }
};