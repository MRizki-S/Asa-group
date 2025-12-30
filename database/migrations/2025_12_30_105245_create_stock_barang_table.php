<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_barang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('barang_id');
            $table->decimal('jumlah_stock', 15, 3)->default(0);
            $table->timestamps();

            $table->foreign('barang_id')
                ->references('id')
                ->on('master_barang')
                ->cascadeOnDelete();

            // 1 barang = 1 row stock
            $table->unique('barang_id');
        });
    }

    public function down(): void
    {
        Schema::table('stock_barang', function (Blueprint $table) {
            $table->dropForeign(['barang_id']);
        });

        Schema::dropIfExists('stock_barang');
    }
};
