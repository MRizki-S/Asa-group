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
        Schema::create('pembangunan_unit_barang_return_fifo', function (Blueprint $table) {
            $table->id();

            $table->foreignId('return_detail_id')
                ->constrained('pembangunan_unit_barang_return_detail')
                ->cascadeOnDelete();

            $table->foreignId('fifo_usage_id')
                ->constrained('pembangunan_unit_barang_fifo_usage')
                ->restrictOnDelete();

            // Qty yang direturn dari layer FIFO ini
            $table->decimal('jumlah_base', 18, 3)->default(0);
            $table->decimal('jumlah_return_base', 18, 3)->default(0);

            // Hasil kualifikasi gudang dari layer ini
            $table->decimal('jumlah_layak_base', 18, 3)->default(0);
            $table->decimal('jumlah_rusak_base', 18, 3)->default(0);

            // Harga dari fifo_usage (snapshot sudah dibekukan di saat order ACC)
            $table->decimal('harga_satuan_snapshot', 18, 2)->default(0);
            $table->decimal('harga_total_snapshot', 18, 2)->default(0);

            $table->timestamps();

            // Indexes
            $table->index('return_detail_id', 'idx_retfifo_return_detail');
            $table->index('fifo_usage_id', 'idx_retfifo_fifo_usage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembangunan_unit_barang_return_fifo');
    }
};
