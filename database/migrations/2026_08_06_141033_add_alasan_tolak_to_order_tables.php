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
        if (!Schema::hasColumn('pembangunan_unit_barang_order', 'alasan_tolak')) {
            Schema::table('pembangunan_unit_barang_order', function (Blueprint $table) {
                $table->text('alasan_tolak')->nullable()->after('catatan');
            });
        }

        if (!Schema::hasColumn('pembangunan_kawasan_barang_order', 'alasan_tolak')) {
            Schema::table('pembangunan_kawasan_barang_order', function (Blueprint $table) {
                $table->text('alasan_tolak')->nullable()->after('catatan');
            });
        }

        if (!Schema::hasColumn('pembangunan_proyek_barang_order', 'alasan_tolak')) {
            Schema::table('pembangunan_proyek_barang_order', function (Blueprint $table) {
                $table->text('alasan_tolak')->nullable()->after('catatan');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pembangunan_unit_barang_order', 'alasan_tolak')) {
            Schema::table('pembangunan_unit_barang_order', function (Blueprint $table) {
                $table->dropColumn('alasan_tolak');
            });
        }

        if (Schema::hasColumn('pembangunan_kawasan_barang_order', 'alasan_tolak')) {
            Schema::table('pembangunan_kawasan_barang_order', function (Blueprint $table) {
                $table->dropColumn('alasan_tolak');
            });
        }

        if (Schema::hasColumn('pembangunan_proyek_barang_order', 'alasan_tolak')) {
            Schema::table('pembangunan_proyek_barang_order', function (Blueprint $table) {
                $table->dropColumn('alasan_tolak');
            });
        }
    }
};
