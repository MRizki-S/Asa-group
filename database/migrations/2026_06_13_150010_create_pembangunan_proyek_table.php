<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_proyek', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->foreignId('pengawas_unit')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('tanggal_mulai')->nullable();
            $table->timestamp('tanggal_selesai')->nullable();
            $table->enum('status_pembangunan', ['pending', 'proses', 'selesai', 'selesai dengan catatan'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_proyek');
    }
};