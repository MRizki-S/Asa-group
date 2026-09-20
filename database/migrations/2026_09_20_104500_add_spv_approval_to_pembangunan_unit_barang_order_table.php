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
        if (Schema::hasTable('pembangunan_unit_barang_order')) {
            Schema::table('pembangunan_unit_barang_order', function (Blueprint $table) {
                if (!Schema::hasColumn('pembangunan_unit_barang_order', 'gudang_by')) {
                    $table->foreignId('gudang_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('pembangunan_unit_barang_order', 'tanggal_gudang')) {
                    $table->dateTime('tanggal_gudang')->nullable()->after('gudang_by');
                }
                if (!Schema::hasColumn('pembangunan_unit_barang_order', 'catatan_gudang')) {
                    $table->text('catatan_gudang')->nullable()->after('tanggal_gudang');
                }
                if (!Schema::hasColumn('pembangunan_unit_barang_order', 'spv_by')) {
                    $table->foreignId('spv_by')->nullable()->after('catatan_gudang')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('pembangunan_unit_barang_order', 'tanggal_spv')) {
                    $table->dateTime('tanggal_spv')->nullable()->after('spv_by');
                }
                if (!Schema::hasColumn('pembangunan_unit_barang_order', 'nomor_nbk')) {
                    $table->string('nomor_nbk', 100)->nullable()->after('nomor_order');
                }
            });
        }

        if (Schema::hasTable('pembangunan_unit_barang_order_detail')) {
            Schema::table('pembangunan_unit_barang_order_detail', function (Blueprint $table) {
                if (!Schema::hasColumn('pembangunan_unit_barang_order_detail', 'jumlah_acc')) {
                    $table->decimal('jumlah_acc', 15, 3)->nullable()->after('jumlah_input');
                }
                if (!Schema::hasColumn('pembangunan_unit_barang_order_detail', 'jumlah_acc_base')) {
                    $table->decimal('jumlah_acc_base', 15, 3)->nullable()->after('jumlah_acc');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pembangunan_unit_barang_order')) {
            Schema::table('pembangunan_unit_barang_order', function (Blueprint $table) {
                $table->dropForeign(['gudang_by']);
                $table->dropForeign(['spv_by']);
                $table->dropColumn(['gudang_by', 'tanggal_gudang', 'catatan_gudang', 'spv_by', 'tanggal_spv', 'nomor_nbk']);
            });
        }

        if (Schema::hasTable('pembangunan_unit_barang_order_detail')) {
            Schema::table('pembangunan_unit_barang_order_detail', function (Blueprint $table) {
                $table->dropColumn(['jumlah_acc', 'jumlah_acc_base']);
            });
        }
    }
};
