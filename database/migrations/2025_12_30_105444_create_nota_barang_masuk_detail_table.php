<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_barang_masuk_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nota_id');
            $table->unsignedBigInteger('barang_id');
            $table->string('merk')->nullable();
            $table->decimal('jumlah_masuk', 15, 3);
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('harga_total', 15, 2);
            $table->decimal('jumlah_sisa', 15, 3);
            $table->timestamps();

            $table->foreign('nota_id')
                ->references('id')
                ->on('nota_barang_masuk')
                ->cascadeOnDelete();

            $table->foreign('barang_id')
                ->references('id')
                ->on('master_barang')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('nota_barang_masuk_detail', function (Blueprint $table) {
            $table->dropForeign(['nota_id']);
            $table->dropForeign(['barang_id']);
        });

        Schema::dropIfExists('nota_barang_masuk_detail');
    }
};
