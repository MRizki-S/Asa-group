<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_proyek_barang_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembangunan_proyek_id')->constrained('pembangunan_proyek')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('pembangunan_proyek_barang_order')->onDelete('cascade');
            $table->text('alasan_return')->nullable();
            $table->enum('status_return', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->foreignId('created_by')->constrained('users');
            $table->dateTime('tanggal_return');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_proyek_barang_returns');
    }
};