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
        if (Schema::hasTable('pembangunan_unit_barang_order') && !Schema::hasColumn('pembangunan_unit_barang_order', 'acc_by')) {
            Schema::table('pembangunan_unit_barang_order', function (Blueprint $table) {
                $table->foreignId('acc_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pembangunan_unit_barang_order') && Schema::hasColumn('pembangunan_unit_barang_order', 'acc_by')) {
            Schema::table('pembangunan_unit_barang_order', function (Blueprint $table) {
                $table->dropForeign(['acc_by']);
                $table->dropColumn('acc_by');
            });
        }
    }
};
