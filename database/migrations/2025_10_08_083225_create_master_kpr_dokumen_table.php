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
        Schema::create('master_kpr_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_id')->nullable()->constrained('master_bank')->onDelete('set null');
            $table->enum('kategori', ['data_diri', 'data_kerja', 'form_bank', 'developer']);
            $table->string('nama_dokumen');
            $table->boolean('wajib')->default(true);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_kpr_dokumen');
    }
};
