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
        // 1. Create pembangunan_kawasan_barang_fifo_usage table
        Schema::create('pembangunan_kawasan_barang_fifo_usage', function (Blueprint $table) {
            $table->id();

            // Order detail that used the goods
            $table->foreignId('order_detail_id');
            $table->foreign('order_detail_id', 'fk_pk_fifo_order_detail')
                ->references('id')
                ->on('pembangunan_kawasan_barang_order_detail')
                ->cascadeOnDelete();

            // FIFO layer origin
            $table->foreignId('nota_barang_masuk_detail_id');
            $table->foreign('nota_barang_masuk_detail_id', 'fk_pk_fifo_nota_detail')
                ->references('id')
                ->on('nota_barang_masuk_detail')
                ->restrictOnDelete();

            // Qty used in base unit
            $table->decimal('jumlah_base', 18, 3)->default(0);

            // Qty returned in base unit
            $table->decimal('jumlah_return_base', 18, 3)->default(0);

            // Price snapshots
            $table->decimal('harga_satuan_snapshot', 18, 2)->default(0);
            $table->decimal('harga_total_snapshot', 18, 2)->default(0);

            $table->timestamps();

            $table->index('order_detail_id', 'idx_pk_fifo_order_dtl');
            $table->index('nota_barang_masuk_detail_id', 'idx_pk_fifo_nota_dtl');
        });

        // 2. Create pembangunan_proyek_barang_fifo_usage table
        Schema::create('pembangunan_proyek_barang_fifo_usage', function (Blueprint $table) {
            $table->id();

            // Order detail that used the goods
            $table->foreignId('order_detail_id');
            $table->foreign('order_detail_id', 'fk_pp_fifo_order_detail')
                ->references('id')
                ->on('pembangunan_proyek_barang_order_detail')
                ->cascadeOnDelete();

            // FIFO layer origin
            $table->foreignId('nota_barang_masuk_detail_id');
            $table->foreign('nota_barang_masuk_detail_id', 'fk_pp_fifo_nota_detail')
                ->references('id')
                ->on('nota_barang_masuk_detail')
                ->restrictOnDelete();

            // Qty used in base unit
            $table->decimal('jumlah_base', 18, 3)->default(0);

            // Qty returned in base unit
            $table->decimal('jumlah_return_base', 18, 3)->default(0);

            // Price snapshots
            $table->decimal('harga_satuan_snapshot', 18, 2)->default(0);
            $table->decimal('harga_total_snapshot', 18, 2)->default(0);

            $table->timestamps();

            $table->index('order_detail_id', 'idx_pp_fifo_order_dtl');
            $table->index('nota_barang_masuk_detail_id', 'idx_pp_fifo_nota_dtl');
        });

        // 3. Add jumlah_return_base column to order detail tables
        Schema::table('pembangunan_kawasan_barang_order_detail', function (Blueprint $table) {
            $table->decimal('jumlah_return_base', 18, 3)->default(0)->after('jumlah_return');
        });

        Schema::table('pembangunan_proyek_barang_order_detail', function (Blueprint $table) {
            $table->decimal('jumlah_return_base', 18, 3)->default(0)->after('jumlah_return');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembangunan_proyek_barang_order_detail', function (Blueprint $table) {
            $table->dropColumn('jumlah_return_base');
        });

        Schema::table('pembangunan_kawasan_barang_order_detail', function (Blueprint $table) {
            $table->dropColumn('jumlah_return_base');
        });

        Schema::dropIfExists('pembangunan_proyek_barang_fifo_usage');
        Schema::dropIfExists('pembangunan_kawasan_barang_fifo_usage');
    }
};
