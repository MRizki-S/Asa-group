<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pembangunan_kawasan_barang_return_fifo')) {
            Schema::create('pembangunan_kawasan_barang_return_fifo', function (Blueprint $table) {
                $table->id();
                $table->foreignId('return_detail_id')->constrained('pembangunan_kawasan_barang_return_details')->onDelete('cascade');
                $table->foreignId('fifo_usage_id')->constrained('pembangunan_kawasan_barang_fifo_usage')->onDelete('cascade');
                $table->decimal('jumlah_base', 18, 3)->default(0);
                $table->decimal('jumlah_return_base', 18, 3)->default(0);
                $table->decimal('jumlah_layak_base', 18, 3)->default(0);
                $table->decimal('jumlah_rusak_base', 18, 3)->default(0);
                $table->decimal('harga_satuan_snapshot', 18, 2)->default(0);
                $table->decimal('harga_total_snapshot', 18, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pembangunan_proyek_barang_return_fifo')) {
            Schema::create('pembangunan_proyek_barang_return_fifo', function (Blueprint $table) {
                $table->id();
                $table->foreignId('return_detail_id')->constrained('pembangunan_proyek_barang_return_details')->onDelete('cascade');
                $table->foreignId('fifo_usage_id')->constrained('pembangunan_proyek_barang_fifo_usage')->onDelete('cascade');
                $table->decimal('jumlah_base', 18, 3)->default(0);
                $table->decimal('jumlah_return_base', 18, 3)->default(0);
                $table->decimal('jumlah_layak_base', 18, 3)->default(0);
                $table->decimal('jumlah_rusak_base', 18, 3)->default(0);
                $table->decimal('harga_satuan_snapshot', 18, 2)->default(0);
                $table->decimal('harga_total_snapshot', 18, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_proyek_barang_return_fifo');
        Schema::dropIfExists('pembangunan_kawasan_barang_return_fifo');
    }
};
