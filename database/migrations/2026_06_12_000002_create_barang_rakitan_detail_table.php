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
        Schema::create('barang_rakitan_detail', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barang_rakitan_id')
                ->constrained('barang_rakitan')
                ->cascadeOnDelete();

            $table->foreignId('barang_bahan_id')
                ->constrained('master_barang')
                ->restrictOnDelete();

            $table->foreignId('satuan_id')
                ->constrained('master_satuan')
                ->restrictOnDelete();

            $table->decimal('qty', 18, 3);
            $table->decimal('qty_base', 18, 3);

            $table->timestamps();

            $table->index('barang_rakitan_id');
            $table->index('barang_bahan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_rakitan_detail');
    }
};
