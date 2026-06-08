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
        Schema::create('pembangunan_unit_barang_return_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('pembangunan_unit_barang_return')->onDelete('cascade');
            $table->foreignId('order_detail_id')->constrained('pembangunan_unit_barang_order_detail')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('master_barang')->onDelete('cascade');

            $table->decimal('jumlah_return', 18, 0)->default(0);
            $table->text('keterangan_return')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembangunan_unit_barang_return_detail');
    }
};
