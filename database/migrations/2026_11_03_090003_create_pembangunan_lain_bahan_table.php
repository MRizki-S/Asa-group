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
        // Akumulasi Bahan Terpakai Pembangunan Lain-lain (Sumber Data Laporan & Cetak Termin)
        Schema::create('pembangunan_lain_bahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembangunan_lain_id')->constrained('pembangunan_lain')->cascadeOnDelete();
            $table->foreignId('pembangunan_lain_sub_id')->constrained('pembangunan_lain_sub')->cascadeOnDelete();
            $table->foreignId('barang_id')->nullable()->constrained('master_barang')->nullOnDelete();
            $table->string('nama_barang')->nullable();
            $table->string('satuan', 50)->nullable();
            $table->decimal('jumlah_pakai', 18, 3)->default(0);
            $table->decimal('harga_total_snapshot', 18, 2)->default(0);
            $table->timestamps();

            $table->index(['pembangunan_lain_id', 'pembangunan_lain_sub_id'], 'idx_plb_lain_sub');
            $table->index('barang_id', 'idx_plb_barang_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembangunan_lain_bahan');
    }
};

