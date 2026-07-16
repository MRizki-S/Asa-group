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
        Schema::table('pembangunan_unit_barang_order_detail', function (Blueprint $table) {
            if (!Schema::hasColumn('pembangunan_unit_barang_order_detail', 'jumlah_return_base')) {
                $table->decimal('jumlah_return_base', 18, 3)->default(0)->after('jumlah_return');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembangunan_unit_barang_order_detail', function (Blueprint $table) {
            if (Schema::hasColumn('pembangunan_unit_barang_order_detail', 'jumlah_return_base')) {
                $table->dropColumn('jumlah_return_base');
            }
        });
    }
};
