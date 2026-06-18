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
        Schema::create('produksi_barang_rakitan_detail', function (Blueprint $table) {
            $table->id();

            $table->foreignId('produksi_barang_rakitan_id');
            $table->foreign('produksi_barang_rakitan_id', 'pbr_detail_pbr_id_fk')
                ->references('id')
                ->on('produksi_barang_rakitan')
                ->cascadeOnDelete();

            $table->foreignId('barang_bahan_id')
                ->constrained('master_barang')
                ->restrictOnDelete();

            $table->foreignId('satuan_id')
                ->constrained('master_satuan')
                ->restrictOnDelete();

            $table->decimal('qty_pakai', 18, 3);
            $table->decimal('qty_pakai_base', 18, 3);
            $table->decimal('harga_total', 18, 2)->default(0);

            $table->timestamps();

            $table->index('produksi_barang_rakitan_id');
            $table->index('barang_bahan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produksi_barang_rakitan_detail');
    }
};
