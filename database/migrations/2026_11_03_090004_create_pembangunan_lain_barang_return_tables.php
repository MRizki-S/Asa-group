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
        // 1. Header Retur Barang Pembangunan Lain-lain
        Schema::create('pembangunan_lain_barang_returns', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_return', 100)->nullable();
            $table->foreignId('pembangunan_lain_id')->constrained('pembangunan_lain')->restrictOnDelete();
            $table->foreignId('pembangunan_lain_sub_id')->constrained('pembangunan_lain_sub')->restrictOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('pembangunan_lain_barang_order')->nullOnDelete();
            $table->foreignId('ubs_id')->nullable()->constrained('ubs')->nullOnDelete();
            $table->enum('status_return', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->dateTime('tanggal_return');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('acc_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('acc_at')->nullable();
            $table->text('alasan_return')->nullable();
            $table->text('alasan_tolak')->nullable();
            $table->timestamps();

            $table->index(['pembangunan_lain_id', 'pembangunan_lain_sub_id'], 'idx_plbr_lain_sub');
            $table->index('status_return', 'idx_plbr_status');
            $table->index('tanggal_return', 'idx_plbr_tgl_return');
        });

        // 2. Detail Item Retur Barang
        Schema::create('pembangunan_lain_barang_return_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('pembangunan_lain_barang_returns')->cascadeOnDelete();
            $table->foreignId('order_detail_id')->nullable()->constrained('pembangunan_lain_barang_order_detail')->nullOnDelete();
            $table->foreignId('barang_id')->nullable()->constrained('master_barang')->nullOnDelete();
            $table->foreignId('satuan_id')->nullable()->constrained('master_satuan')->nullOnDelete();
            $table->string('nama_barang')->nullable();
            $table->string('satuan', 50)->nullable();
            $table->decimal('jumlah_input', 18, 3)->default(0);
            $table->decimal('jumlah_base', 18, 3)->default(0);
            $table->decimal('jumlah_layak_base', 18, 3)->default(0);
            $table->decimal('jumlah_rusak_base', 18, 3)->default(0);
            $table->decimal('harga_satuan_snapshot', 18, 2)->default(0);
            $table->decimal('harga_total_snapshot', 18, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('return_id', 'idx_plbrd_return_id');
            $table->index('order_detail_id', 'idx_plbrd_order_dtl_id');
            $table->index('barang_id', 'idx_plbrd_barang_id');
        });

        // 3. FIFO Return Layer Pembangunan Lain-lain
        Schema::create('pembangunan_lain_barang_return_fifo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_detail_id');
            $table->foreign('return_detail_id', 'fk_pl_ret_fifo_ret_dtl')
                ->references('id')
                ->on('pembangunan_lain_barang_return_details')
                ->cascadeOnDelete();

            $table->foreignId('fifo_usage_id');
            $table->foreign('fifo_usage_id', 'fk_pl_ret_fifo_usage')
                ->references('id')
                ->on('pembangunan_lain_barang_fifo_usage')
                ->cascadeOnDelete();

            $table->decimal('jumlah_base', 18, 3)->default(0);
            $table->decimal('jumlah_return_base', 18, 3)->default(0);
            $table->decimal('jumlah_layak_base', 18, 3)->default(0);
            $table->decimal('jumlah_rusak_base', 18, 3)->default(0);
            $table->decimal('harga_satuan_snapshot', 18, 2)->default(0);
            $table->decimal('harga_total_snapshot', 18, 2)->default(0);
            $table->timestamps();

            $table->index('return_detail_id', 'idx_pl_ret_fifo_rd_id');
            $table->index('fifo_usage_id', 'idx_pl_ret_fifo_fu_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembangunan_lain_barang_return_fifo');
        Schema::dropIfExists('pembangunan_lain_barang_return_details');
        Schema::dropIfExists('pembangunan_lain_barang_returns');
    }
};

