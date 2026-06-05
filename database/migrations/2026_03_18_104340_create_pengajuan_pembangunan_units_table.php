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
        Schema::create('pengajuan_pembangunan_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perumahaan_id')->constrained('perumahaan')->cascadeOnDelete();
            $table->foreignId('pembangunan_unit_id')->constrained('pembangunan_unit')->cascadeOnDelete();
            $table->foreignId('diajukan_oleh')->constrained('users')->cascadeOnDelete();
            $table->foreignId('direspon_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status_pengajuan', ['pending', 'dibangun'])->default('pending');
            $table->timestamp('tanggal_diajukan');
            $table->timestamp('tanggal_direspon')->nullable();
            $table->timestamps();
        });

        // Schema::create('pengajuan_pembangunan_unit', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('unit_id')->constrained('unit')->cascadeOnDelete();
        //     $table->foreignId('perumahaan_id')->constrained('perumahaan')->cascadeOnDelete();
        //     $table->foreignId('tahap_id')->constrained('tahap')->cascadeOnDelete();
        //     $table->foreignId('qc_container_id')->constrained('master_qc_container')->cascadeOnDelete();
        //     $table->foreignId('diajukan_oleh')->constrained('users')->cascadeOnDelete();
        //     $table->foreignId('direspon_oleh')->nullable()->constrained('users')->nullOnDelete();
        //     $table->enum('status_pengajuan', ['pending', 'dibangun'])->default('pending');
        //     $table->timestamp('tanggal_diajukan');
        //     $table->timestamp('tanggal_direspon')->nullable();
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_pembangunan_unit');
    }
};
