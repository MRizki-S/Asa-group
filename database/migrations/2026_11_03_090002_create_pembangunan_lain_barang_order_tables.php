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
        // 1. Header Order Barang Keluar Pembangunan Lain-lain
        Schema::create('pembangunan_lain_barang_order', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_order', 100)->nullable();
            $table->foreignId('pembangunan_lain_id')->constrained('pembangunan_lain')->restrictOnDelete();
            $table->foreignId('pembangunan_lain_sub_id')->constrained('pembangunan_lain_sub')->restrictOnDelete();
            $table->foreignId('pengawas_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('ubs_id')->nullable()->constrained('ubs')->nullOnDelete();
            $table->enum('jenis_order', ['stock', 'direct', 'cash'])->default('stock');
            $table->enum('status_order', ['diproses', 'menunggu_spv', 'selesai', 'ditolak', 'pengembalian'])->default('diproses');
            $table->dateTime('tanggal_diajukan');
            $table->dateTime('tanggal_spv')->nullable();
            $table->dateTime('tanggal_gudang')->nullable();
            $table->dateTime('tanggal_selesai')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('spv_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('gudang_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('acc_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->text('alasan_tolak')->nullable();
            $table->timestamps();

            $table->index(['pembangunan_lain_id', 'pembangunan_lain_sub_id'], 'idx_plbo_lain_sub');
            $table->index('status_order', 'idx_plbo_status');
            $table->index('tanggal_diajukan', 'idx_plbo_tgl_diajukan');
        });

        // 2. Detail Item Barang Keluar
        Schema::create('pembangunan_lain_barang_order_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('pembangunan_lain_barang_order')->cascadeOnDelete();
            $table->foreignId('barang_id')->nullable()->constrained('master_barang')->nullOnDelete();
            $table->foreignId('satuan_id')->nullable()->constrained('master_satuan')->nullOnDelete();
            $table->string('nama_barang')->nullable();
            $table->string('satuan', 50)->nullable();
            $table->decimal('jumlah_input', 18, 3)->default(0);
            $table->decimal('jumlah_base', 18, 3)->default(0);
            $table->boolean('konfirmasi')->default(false);
            $table->decimal('harga_satuan_snapshot', 18, 2)->default(0);
            $table->decimal('harga_total_snapshot', 18, 2)->default(0);
            $table->decimal('jumlah_return', 18, 3)->default(0);
            $table->decimal('jumlah_return_base', 18, 3)->default(0);
            $table->text('keterangan_return')->nullable();
            $table->timestamps();

            $table->index('order_id', 'idx_plbod_order_id');
            $table->index('barang_id', 'idx_plbod_barang_id');
        });

        // 3. FIFO Usage Layer Pembangunan Lain-lain
        Schema::create('pembangunan_lain_barang_fifo_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_detail_id');
            $table->foreign('order_detail_id', 'fk_pl_fifo_order_detail')
                ->references('id')
                ->on('pembangunan_lain_barang_order_detail')
                ->cascadeOnDelete();

            $table->foreignId('nota_barang_masuk_detail_id');
            $table->foreign('nota_barang_masuk_detail_id', 'fk_pl_fifo_nota_detail')
                ->references('id')
                ->on('nota_barang_masuk_detail')
                ->restrictOnDelete();

            $table->decimal('jumlah_base', 18, 3)->default(0);
            $table->decimal('jumlah_return_base', 18, 3)->default(0);
            $table->decimal('harga_satuan_snapshot', 18, 2)->default(0);
            $table->decimal('harga_total_snapshot', 18, 2)->default(0);
            $table->timestamps();

            $table->index('order_detail_id', 'idx_pl_fifo_order_dtl');
            $table->index('nota_barang_masuk_detail_id', 'idx_pl_fifo_nota_dtl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembangunan_lain_barang_fifo_usage');
        Schema::dropIfExists('pembangunan_lain_barang_order_detail');
        Schema::dropIfExists('pembangunan_lain_barang_order');
    }
};

