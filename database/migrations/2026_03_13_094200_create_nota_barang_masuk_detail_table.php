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
        Schema::create('nota_barang_masuk_detail', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nota_id')
                ->constrained('nota_barang_masuk')
                ->cascadeOnDelete();

            $table->foreignId('barang_id')
                ->constrained('master_barang');

            $table->string('merk', 100)->nullable();

            $table->decimal('jumlah_input', 18, 3);
            $table->foreignId('satuan_id')->constrained('master_satuan');
            $table->decimal('jumlah_base', 18, 3);

            $table->decimal('harga_satuan', 18, 2);
            $table->decimal('harga_total', 18, 2);

            $table->decimal('jumlah_sisa', 18, 3);

            $table->timestamps();

            $table->index('nota_id');
            $table->index('barang_id');

            // penting untuk FIFO query
            $table->index(['barang_id', 'jumlah_sisa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_barang_masuk_detail');
    }
};
