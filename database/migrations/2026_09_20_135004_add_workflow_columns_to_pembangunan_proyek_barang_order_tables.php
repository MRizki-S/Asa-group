<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah 'menunggu_spv' ke enum status_order pada pembangunan_proyek_barang_order
        DB::statement("ALTER TABLE `pembangunan_proyek_barang_order` MODIFY COLUMN `status_order` ENUM('diproses', 'menunggu_spv', 'selesai', 'ditolak', 'pengembalian') NOT NULL DEFAULT 'diproses'");

        // 2. Tambah kolom approval SPV dan NBK pada pembangunan_proyek_barang_order
        Schema::table('pembangunan_proyek_barang_order', function (Blueprint $table) {
            if (!Schema::hasColumn('pembangunan_proyek_barang_order', 'nomor_nbk')) {
                $table->string('nomor_nbk', 50)->nullable()->after('nomor_order');
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_order', 'gudang_by')) {
                $table->foreignId('gudang_by')->nullable()->after('status_order')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_order', 'tanggal_gudang')) {
                $table->dateTime('tanggal_gudang')->nullable()->after('gudang_by');
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_order', 'catatan_gudang')) {
                $table->text('catatan_gudang')->nullable()->after('tanggal_gudang');
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_order', 'spv_by')) {
                $table->foreignId('spv_by')->nullable()->after('catatan_gudang')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_order', 'tanggal_spv')) {
                $table->dateTime('tanggal_spv')->nullable()->after('spv_by');
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_order', 'acc_by')) {
                $table->foreignId('acc_by')->nullable()->after('tanggal_spv')->constrained('users')->nullOnDelete();
            }
        });

        // 3. Tambah kolom kuantitas rilis fisik gudang pada detail
        Schema::table('pembangunan_proyek_barang_order_detail', function (Blueprint $table) {
            if (!Schema::hasColumn('pembangunan_proyek_barang_order_detail', 'jumlah_acc')) {
                $table->decimal('jumlah_acc', 12, 3)->nullable()->after('jumlah_input');
            }
            if (!Schema::hasColumn('pembangunan_proyek_barang_order_detail', 'jumlah_acc_base')) {
                $table->decimal('jumlah_acc_base', 12, 3)->nullable()->after('jumlah_base');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pembangunan_proyek_barang_order_detail', function (Blueprint $table) {
            if (Schema::hasColumn('pembangunan_proyek_barang_order_detail', 'jumlah_acc')) {
                $table->dropColumn('jumlah_acc');
            }
            if (Schema::hasColumn('pembangunan_proyek_barang_order_detail', 'jumlah_acc_base')) {
                $table->dropColumn('jumlah_acc_base');
            }
        });

        Schema::table('pembangunan_proyek_barang_order', function (Blueprint $table) {
            if (Schema::hasColumn('pembangunan_proyek_barang_order', 'nomor_nbk')) {
                $table->dropColumn('nomor_nbk');
            }
            if (Schema::hasColumn('pembangunan_proyek_barang_order', 'gudang_by')) {
                $table->dropConstrainedForeignId('gudang_by');
            }
            if (Schema::hasColumn('pembangunan_proyek_barang_order', 'tanggal_gudang')) {
                $table->dropColumn('tanggal_gudang');
            }
            if (Schema::hasColumn('pembangunan_proyek_barang_order', 'catatan_gudang')) {
                $table->dropColumn('catatan_gudang');
            }
            if (Schema::hasColumn('pembangunan_proyek_barang_order', 'spv_by')) {
                $table->dropConstrainedForeignId('spv_by');
            }
            if (Schema::hasColumn('pembangunan_proyek_barang_order', 'tanggal_spv')) {
                $table->dropColumn('tanggal_spv');
            }
            if (Schema::hasColumn('pembangunan_proyek_barang_order', 'acc_by')) {
                $table->dropConstrainedForeignId('acc_by');
            }
        });

        DB::statement("ALTER TABLE `pembangunan_proyek_barang_order` MODIFY COLUMN `status_order` ENUM('diproses', 'selesai', 'ditolak', 'pengembalian') NOT NULL DEFAULT 'diproses'");
    }
};
