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
        Schema::create('stock_gudang_hub', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')
                ->constrained('master_barang')
                ->cascadeOnDelete();

            $table->decimal('jumlah_stock', 18, 2)->default(0);
            $table->decimal('minimal_stock', 18, 2)->default(0);

            $table->timestamps();

            $table->unique('barang_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_gudang_hub');
    }
};
