<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_proyek_barang_return_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('pembangunan_proyek_barang_returns', 'id', 'ppbrd_return_fk')->onDelete('cascade');
            $table->foreignId('order_detail_id')->constrained('pembangunan_proyek_barang_order_detail', 'id', 'ppbrd_order_detail_fk')->onDelete('cascade');
            $table->decimal('jumlah_return', 18, 3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_proyek_barang_return_details');
    }
};