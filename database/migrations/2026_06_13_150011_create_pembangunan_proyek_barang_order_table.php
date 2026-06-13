<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_proyek_barang_order', function (Blueprint $group) {
            $group->id();
            $group->foreignId('pembangunan_proyek_id')->constrained('pembangunan_proyek')->cascadeOnDelete();
            $group->enum('jenis_order', ['stock', 'direct'])->default('stock');
            $group->text('catatan')->nullable();
            $group->dateTime('tanggal_diajukan');
            $group->enum('status_order', ['diproses', 'selesai', 'ditolak', 'pengembalian'])->default('diproses');
            $group->dateTime('tanggal_selesai')->nullable();
            $group->foreignId('created_by')->constrained('users');
            $group->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_proyek_barang_order');
    }
};