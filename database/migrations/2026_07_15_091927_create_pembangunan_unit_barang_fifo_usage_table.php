<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pembangunan_unit_barang_fifo_usage', function (Blueprint $table) {
            $table->id();

            // Order yang memakai barang
            $table->foreignId('order_detail_id');

            $table->foreign('order_detail_id', 'fk_pu_fifo_order_detail')
                ->references('id')
                ->on('pembangunan_unit_barang_order_detail')
                ->cascadeOnDelete();


            // Layer FIFO asal barang
            $table->foreignId('nota_barang_masuk_detail_id');

            $table->foreign('nota_barang_masuk_detail_id', 'fk_pu_fifo_nota_detail')
                ->references('id')
                ->on('nota_barang_masuk_detail')
                ->restrictOnDelete();

            // Qty yang dipakai order
            $table->decimal('jumlah_base', 18, 3)->default(0);

            // Qty yang sudah direturn
            $table->decimal('jumlah_return_base', 18, 3)->default(0);

            // Snapshot harga
            $table->decimal('harga_satuan_snapshot', 18, 2)->default(0);
            $table->decimal('harga_total_snapshot', 18, 2)->default(0);

            $table->timestamps();

            $table->index('order_detail_id', 'idx_pu_fifo_order_dtl');
            $table->index('nota_barang_masuk_detail_id', 'idx_pu_fifo_nota_dtl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembangunan_unit_barang_fifo_usage');
    }
};
