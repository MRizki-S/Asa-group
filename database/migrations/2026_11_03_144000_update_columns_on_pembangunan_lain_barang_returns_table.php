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
        Schema::table('pembangunan_lain_barang_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('pembangunan_lain_barang_returns', 'status')) {
                $table->enum('status', ['diproses', 'selesai', 'ditolak'])->default('diproses')->after('ubs_id');
            }
            if (!Schema::hasColumn('pembangunan_lain_barang_returns', 'catatan')) {
                $table->text('catatan')->nullable()->after('tanggal_return');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembangunan_lain_barang_returns', function (Blueprint $table) {
            if (Schema::hasColumn('pembangunan_lain_barang_returns', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('pembangunan_lain_barang_returns', 'catatan')) {
                $table->dropColumn('catatan');
            }
        });
    }
};

