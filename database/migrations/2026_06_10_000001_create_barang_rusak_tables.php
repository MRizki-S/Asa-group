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
        Schema::create('barang_rusak', function (Blueprint $table) {
            $table->id();

            $table->string('nomor_barang_rusak', 100)->unique();
            $table->date('tgl_rusak');

            $table->enum('stock_type', ['HUB', 'UBS']);
            $table->foreignId('ubs_id')
                ->nullable()
                ->constrained('ubs')
                ->nullOnDelete();

            $table->foreignId('barang_id')
                ->constrained('master_barang');

            $table->foreignId('satuan_id')
                ->constrained('master_satuan');

            $table->decimal('qty_out', 18, 3);
            $table->decimal('qty_base', 18, 3);

            $table->enum('status', ['posted', 'cancelled'])->default('posted');
            $table->text('keterangan')->nullable();

            $table->foreignId('created_by')
                ->constrained('users');

            $table->timestamp('posted_at')->nullable();

            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('cancel_reason')->nullable();

            $table->timestamps();

            $table->index('tgl_rusak');
            $table->index('status');
            $table->index(['stock_type', 'ubs_id']);
            $table->index(['barang_id', 'status']);
        });

        Schema::create('barang_rusak_fifo', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barang_rusak_id')
                ->constrained('barang_rusak')
                ->cascadeOnDelete();

            $table->foreignId('nota_barang_masuk_detail_id')
                ->constrained('nota_barang_masuk_detail')
                ->restrictOnDelete();

            $table->decimal('qty_base_diambil', 18, 3);
            $table->decimal('harga_satuan_base', 18, 2);
            $table->decimal('harga_total', 18, 2);

            $table->timestamps();

            $table->index('barang_rusak_id');
            $table->index('nota_barang_masuk_detail_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_rusak_fifo');
        Schema::dropIfExists('barang_rusak');
    }
};
