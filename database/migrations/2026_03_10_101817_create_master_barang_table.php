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
        Schema::create('master_barang', function (Blueprint $table) {
            $table->id();

            $table->string('kode_barang', 50)->unique();
            $table->string('nama_barang', 255);

            $table->foreignId('base_unit_id')
                ->constrained('master_satuan')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->boolean('is_stock')->default(true);

            $table->timestamps();

            $table->index('base_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_barang');
    }
};
