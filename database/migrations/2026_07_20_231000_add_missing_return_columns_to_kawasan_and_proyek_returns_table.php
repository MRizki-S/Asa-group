<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembangunan_kawasan_barang_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('pembangunan_kawasan_barang_returns', 'nomor_return')) {
                $table->string('nomor_return')->nullable()->after('order_id');
            }
            if (!Schema::hasColumn('pembangunan_kawasan_barang_returns', 'tanggal_return')) {
                $table->dateTime('tanggal_return')->nullable();
            }
            if (!Schema::hasColumn('pembangunan_kawasan_barang_returns', 'catatan')) {
                $table->text('catatan')->nullable();
            }
            if (!Schema::hasColumn('pembangunan_kawasan_barang_returns', 'acc_by')) {
                $table->unsignedBigInteger('acc_by')->nullable();
            }
            if (!Schema::hasColumn('pembangunan_kawasan_barang_returns', 'acc_at')) {
                $table->dateTime('acc_at')->nullable();
            }
            if (!Schema::hasColumn('pembangunan_kawasan_barang_returns', 'alasan_tolak')) {
                $table->text('alasan_tolak')->nullable();
            }
        });

        Schema::table('pembangunan_proyek_barang_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('pembangunan_proyek_barang_returns', 'nomor_return')) {
                $table->string('nomor_return')->nullable()->after('order_id');
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_returns', 'tanggal_return')) {
                $table->dateTime('tanggal_return')->nullable();
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_returns', 'catatan')) {
                $table->text('catatan')->nullable();
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_returns', 'acc_by')) {
                $table->unsignedBigInteger('acc_by')->nullable();
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_returns', 'acc_at')) {
                $table->dateTime('acc_at')->nullable();
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_returns', 'alasan_tolak')) {
                $table->text('alasan_tolak')->nullable();
            }
        });

        Schema::table('pembangunan_kawasan_barang_return_details', function (Blueprint $table) {
            if (!Schema::hasColumn('pembangunan_kawasan_barang_return_details', 'barang_id')) {
                $table->unsignedBigInteger('barang_id')->nullable();
            }
            if (!Schema::hasColumn('pembangunan_kawasan_barang_return_details', 'satuan_id')) {
                $table->unsignedBigInteger('satuan_id')->nullable();
            }
            if (!Schema::hasColumn('pembangunan_kawasan_barang_return_details', 'satuan')) {
                $table->string('satuan')->nullable();
            }
            if (!Schema::hasColumn('pembangunan_kawasan_barang_return_details', 'nama_barang')) {
                $table->string('nama_barang')->nullable();
            }
            if (!Schema::hasColumn('pembangunan_kawasan_barang_return_details', 'jumlah_input')) {
                $table->decimal('jumlah_input', 18, 3)->default(0);
            }
            if (!Schema::hasColumn('pembangunan_kawasan_barang_return_details', 'jumlah_base')) {
                $table->decimal('jumlah_base', 18, 3)->default(0);
            }
            if (!Schema::hasColumn('pembangunan_kawasan_barang_return_details', 'jumlah_layak_base')) {
                $table->decimal('jumlah_layak_base', 18, 3)->default(0);
            }
            if (!Schema::hasColumn('pembangunan_kawasan_barang_return_details', 'jumlah_rusak_base')) {
                $table->decimal('jumlah_rusak_base', 18, 3)->default(0);
            }
            if (!Schema::hasColumn('pembangunan_kawasan_barang_return_details', 'harga_satuan_snapshot')) {
                $table->decimal('harga_satuan_snapshot', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('pembangunan_kawasan_barang_return_details', 'harga_total_snapshot')) {
                $table->decimal('harga_total_snapshot', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('pembangunan_kawasan_barang_return_details', 'keterangan')) {
                $table->text('keterangan')->nullable();
            }
        });

        Schema::table('pembangunan_proyek_barang_return_details', function (Blueprint $table) {
            if (!Schema::hasColumn('pembangunan_proyek_barang_return_details', 'barang_id')) {
                $table->unsignedBigInteger('barang_id')->nullable();
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_return_details', 'satuan_id')) {
                $table->unsignedBigInteger('satuan_id')->nullable();
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_return_details', 'satuan')) {
                $table->string('satuan')->nullable();
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_return_details', 'nama_barang')) {
                $table->string('nama_barang')->nullable();
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_return_details', 'jumlah_input')) {
                $table->decimal('jumlah_input', 18, 3)->default(0);
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_return_details', 'jumlah_base')) {
                $table->decimal('jumlah_base', 18, 3)->default(0);
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_return_details', 'jumlah_layak_base')) {
                $table->decimal('jumlah_layak_base', 18, 3)->default(0);
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_return_details', 'jumlah_rusak_base')) {
                $table->decimal('jumlah_rusak_base', 18, 3)->default(0);
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_return_details', 'harga_satuan_snapshot')) {
                $table->decimal('harga_satuan_snapshot', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_return_details', 'harga_total_snapshot')) {
                $table->decimal('harga_total_snapshot', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_return_details', 'keterangan')) {
                $table->text('keterangan')->nullable();
            }
        });

        try {
            DB::statement("ALTER TABLE pembangunan_kawasan_barang_returns MODIFY COLUMN status VARCHAR(50) DEFAULT 'diproses'");
            DB::statement("ALTER TABLE pembangunan_proyek_barang_returns MODIFY COLUMN status VARCHAR(50) DEFAULT 'diproses'");
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
    }
};
