<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jurnal', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_jurnal')->unique();
            $table->date('tanggal');

            $table->foreignId('periode_id')
                ->constrained('periode_keuangan')
                ->restrictOnDelete();

            $table->enum('jenis_jurnal', [
                'saldo_awal',
                'umum',
                'penyesuaian',
                'penutup'
            ]);

            $table->enum('status', ['draft', 'posted'])->default('draft');

            $table->text('keterangan')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal');
    }
};
