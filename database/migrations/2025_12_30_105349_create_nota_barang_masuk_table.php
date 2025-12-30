<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_barang_masuk', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_nota')->unique();
            $table->date('tanggal_nota');
            $table->string('supplier')->nullable(); // nama toko
            $table->enum('cara_bayar', ['cash', 'hutang']);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('nota_barang_masuk', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::dropIfExists('nota_barang_masuk');
    }
};
