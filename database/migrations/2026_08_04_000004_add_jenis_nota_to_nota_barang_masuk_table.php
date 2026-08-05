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
        Schema::table('nota_barang_masuk', function (Blueprint $table) {
            $table->enum('jenis_nota', [
                'supplier',
                'produksi_rakitan',
                'return_barang',
                'adjustment_stock'
            ])->default('supplier')->after('tanggal_nota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nota_barang_masuk', function (Blueprint $table) {
            $table->dropColumn('jenis_nota');
        });
    }
};
