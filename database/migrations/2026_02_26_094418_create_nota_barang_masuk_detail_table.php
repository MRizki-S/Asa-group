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
        Schema::create('nota_barang_masuk_detail', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nota_id')
                ->constrained('nota_barang_masuk')
                ->cascadeOnDelete();

            $table->foreignId('barang_id')
                ->constrained('master_barang')
                ->cascadeOnDelete();

            $table->string('merk')->nullable();

            $table->integer('jumlah_masuk');
            $table->integer('jumlah_sisa'); // FIFO layer

            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('harga_total', 15, 2);

            $table->timestamps();
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
