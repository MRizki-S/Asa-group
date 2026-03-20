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
        Schema::create('pembangunan_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('unit')->cascadeOnDelete();
            $table->foreignId('perumahaan_id')->constrained('perumahaan')->cascadeOnDelete();
            $table->foreignId('tahap_id')->constrained('tahap')->cascadeOnDelete();
            $table->foreignId('pengawas_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('qc_container_id')->nullable()->constrained('master_qc_container')->onDelete('set null');
            $table->timestamp('tanggal_mulai')->nullable();
            $table->timestamp('tanggal_selesai')->nullable();
            $table->enum('status_pembangunan', ['pending', 'proses', 'selesai'])->default('pending');
            $table->enum('status_serah_terima', ['pending', 'siap_serah_terima', 'siap_lpa'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembangunan_unit');
    }
};
