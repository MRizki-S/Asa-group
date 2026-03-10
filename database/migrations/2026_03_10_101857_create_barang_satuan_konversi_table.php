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
        Schema::create('barang_satuan_konversi', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barang_id')
                ->constrained('master_barang')
                ->cascadeOnDelete();

            $table->foreignId('satuan_id')
                ->constrained('master_satuan')
                ->cascadeOnDelete();

            $table->decimal('konversi_ke_base', 18, 6);

            $table->boolean('is_default')->default(false);

            $table->timestamps();

            $table->unique(['barang_id', 'satuan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_satuan_konversi');
    }
};
