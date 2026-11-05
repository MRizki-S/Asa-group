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
        Schema::table('pembangunan_lain_barang_order', function (Blueprint $table) {
            if (!Schema::hasColumn('pembangunan_lain_barang_order', 'nomor_nbk')) {
                $table->string('nomor_nbk', 100)->nullable()->after('nomor_order');
            }
            if (!Schema::hasColumn('pembangunan_lain_barang_order', 'catatan_gudang')) {
                $table->text('catatan_gudang')->nullable()->after('catatan');
            }
            $table->unsignedBigInteger('pengawas_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembangunan_lain_barang_order', function (Blueprint $table) {
            if (Schema::hasColumn('pembangunan_lain_barang_order', 'nomor_nbk')) {
                $table->dropColumn('nomor_nbk');
            }
            if (Schema::hasColumn('pembangunan_lain_barang_order', 'catatan_gudang')) {
                $table->dropColumn('catatan_gudang');
            }
        });
    }
};

