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
        Schema::create('master_rap_bahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained('type')->cascadeOnDelete();
            $table->foreignId('master_qc_container_id')->constrained('master_qc_container')->cascadeOnDelete();
            $table->foreignId('master_qc_urutan_id')->constrained('master_qc_urutan')->cascadeOnDelete();
            // $table->foreignId('barang_id')->constrained('barang')->cascadeOnDelete();
            $table->decimal('jumlah_kebutuhan_standar', 15, 2);
            $table->string('satuan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_rap_bahan');
    }
};
