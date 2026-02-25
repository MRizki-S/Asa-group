<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('akun_keuangan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_akun')->unique();
            $table->string('nama_akun');

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('akun_keuangan')
                ->nullOnDelete();

            $table->foreignId('kategori_akun_id')
                ->constrained('kategori_akun_keuangan')
                ->restrictOnDelete();

            $table->boolean('is_leaf')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akun_keuangan');
    }
};
