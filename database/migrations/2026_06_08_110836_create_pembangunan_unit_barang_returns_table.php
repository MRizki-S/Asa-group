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
        Schema::create('pembangunan_unit_barang_return', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('pembangunan_unit_barang_order')->onDelete('cascade');
            $table->enum('status', ['diajukan', 'disetujui', 'ditolak'])->default('diajukan');
            $table->foreignId('diajukan_oleh')->constrained('users')->onDelete('cascade');
            $table->dateTime('tanggal_diajukan')->default(now());
            $table->foreignId('direspon_oleh')->nullable()->constrained('users')->onDelete('cascade');
            $table->dateTime('tanggal_direspon')->nullable();
            $table->text('alasan_ditolak')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembangunan_unit_barang_return');
    }
};
