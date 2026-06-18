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
        Schema::create('produksi_barang_rakitan', function (Blueprint $table) {
            $table->id();

            $table->string('nomor_rakitan', 100)->unique();
            $table->date('tanggal_rakitan');

            $table->enum('stock_type', ['HUB', 'UBS']);
            $table->foreignId('ubs_id')
                ->nullable()
                ->constrained('ubs')
                ->nullOnDelete();

            $table->foreignId('barang_rakitan_id')
                ->constrained('barang_rakitan')
                ->restrictOnDelete();

            $table->foreignId('barang_hasil_id')
                ->constrained('master_barang')
                ->restrictOnDelete();

            $table->foreignId('satuan_hasil_id')
                ->constrained('master_satuan')
                ->restrictOnDelete();

            $table->decimal('qty_hasil', 18, 3);
            $table->decimal('qty_hasil_base', 18, 3);

            $table->decimal('total_biaya', 18, 2)->default(0);
            $table->decimal('harga_satuan', 18, 2)->default(0);
            $table->decimal('harga_satuan_base', 18, 2)->default(0);

            $table->enum('status', ['active', 'cancelled'])->default('active');
            $table->text('keterangan')->nullable();

            $table->foreignId('created_by')
                ->constrained('users');

            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('cancel_reason')->nullable();

            $table->foreignId('nota_barang_masuk_id')
                ->nullable()
                ->constrained('nota_barang_masuk')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('tanggal_rakitan');
            $table->index('status');
            $table->index(['stock_type', 'ubs_id']);
            $table->index('barang_hasil_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produksi_barang_rakitan');
    }
};
