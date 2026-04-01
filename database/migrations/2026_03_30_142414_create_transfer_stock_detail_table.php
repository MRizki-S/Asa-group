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
        Schema::create('transfer_stock_detail', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('transfer_id');
            $table->unsignedBigInteger('barang_id');

            $table->decimal('qty', 18, 3);        // input user
            $table->unsignedBigInteger('satuan_id');
            $table->decimal('qty_base', 18, 3);   // hasil konversi

            $table->string('nama_barang_snapshot')->nullable();

            $table->timestamps();

            // FK
            $table->foreign('transfer_id')->references('id')->on('transfer_stock')->cascadeOnDelete();
            $table->foreign('barang_id')->references('id')->on('master_barang')->cascadeOnDelete();
            $table->foreign('satuan_id')->references('id')->on('master_satuan')->restrictOnDelete();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_stock_detail');
    }
};
