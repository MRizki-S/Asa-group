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
            $table->foreignId('return_id')->constrained('pembangunan_unit_barang_return');
            $table->foreignId('barang_id')->constrained('master_barang');
            $table->string('nama_barang');
            $table->foreignId('satuan_id')->nullable()->constrained('master_satuan')->nullOnDelete();
            $table->string('satuan')->nullable();
            
            $table->decimal('jumlah_input', 18, 3)->default(0);
            $table->decimal('jumlah_base', 18, 3)->default(0);
            $table->decimal('jumlah_layak_base', 18, 3)->default(0);
            $table->decimal('jumlah_rusak_base', 18, 3)->default(0);
            
            $table->decimal('harga_satuan_snapshot', 18, 2)->default(0);
            $table->decimal('harga_total_snapshot', 18, 2)->default(0);
            
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('return_id');
            $table->index('barang_id');
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
