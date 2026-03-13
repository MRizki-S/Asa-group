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
        Schema::create('stock_ledger', function (Blueprint $table) {
            $table->id();

            $table->dateTime('tanggal');

            $table->foreignId('barang_id')
                ->constrained('master_barang');

            $table->enum('stock_type', ['HUB', 'UBS']);

            $table->foreignId('ubs_id')
                ->nullable()
                ->constrained('ubs');

            $table->enum('tipe', [
                'masuk',
                'keluar',
                'transfer',
                'koreksi',
                'return'
            ]);

            $table->string('ref_type', 50);
            $table->unsignedBigInteger('ref_id');

            $table->decimal('qty_masuk', 18, 3)->default(0);
            $table->decimal('qty_keluar', 18, 3)->default(0);

            $table->decimal('harga_satuan', 18, 2)->nullable();

            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();

            $table->index('barang_id');
            $table->index(['ref_type', 'ref_id']);
            $table->index(['barang_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ledger');
    }
};
