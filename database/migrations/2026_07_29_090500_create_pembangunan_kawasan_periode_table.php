<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_kawasan_periode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembangunan_kawasan_id')->constrained('pembangunan_kawasan')->cascadeOnDelete();
            $table->foreignId('pengawas_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('tanggal_mulai')->nullable();
            $table->timestamp('tanggal_selesai')->nullable();
            $table->enum('status', ['proses', 'selesai', 'selesai dengan catatan'])->default('proses');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_kawasan_periode');
    }
};
