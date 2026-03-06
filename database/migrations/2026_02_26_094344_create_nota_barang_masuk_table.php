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
        Schema::create('nota_barang_masuk', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_nota')->unique();
            $table->date('tanggal_nota');
            $table->string('supplier')->nullable();
            $table->enum('cara_bayar', ['cash', 'hutang']);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_barang_masuk');
    }
};
