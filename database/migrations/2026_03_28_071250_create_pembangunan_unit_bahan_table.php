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
        Schema::create('pembangunan_unit_bahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembangunan_unit_id')
                ->constrained('pembangunan_unit')
                ->onDelete('cascade');

            $table->foreignId('pembangunan_unit_qc_id')
                ->constrained('pembangunan_unit_qc')
                ->onDelete('cascade');

            $table->foreignId('barang_id')
                ->nullable()
                ->constrained('master_barang')
                ->onDelete('set null');

            $table->string('nama_barang');
            $table->string('satuan');

            $table->decimal('jumlah_pakai', 18, 3)->default(0);
            $table->decimal('harga_total_snapshot', 18, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembangunan_unit_bahan');
    }
};
