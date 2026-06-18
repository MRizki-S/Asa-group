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
        Schema::create('produksi_barang_rakitan_fifo', function (Blueprint $table) {
            $table->id();

            $table->foreignId('produksi_barang_rakitan_detail_id');
            $table->foreign('produksi_barang_rakitan_detail_id', 'pbr_fifo_pbr_detail_id_fk')
                ->references('id')
                ->on('produksi_barang_rakitan_detail')
                ->cascadeOnDelete();

            $table->foreignId('nota_barang_masuk_detail_id');
            $table->foreign('nota_barang_masuk_detail_id', 'pbr_fifo_nota_detail_id_fk')
                ->references('id')
                ->on('nota_barang_masuk_detail')
                ->restrictOnDelete();

            $table->decimal('qty_base_diambil', 18, 3);
            $table->decimal('harga_satuan_base', 18, 2);
            $table->decimal('harga_total', 18, 2);

            $table->timestamps();

            $table->index('produksi_barang_rakitan_detail_id', 'pbr_fifo_pbr_detail_id_idx');
            $table->index('nota_barang_masuk_detail_id', 'pbr_fifo_nota_detail_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produksi_barang_rakitan_fifo');
    }
};
