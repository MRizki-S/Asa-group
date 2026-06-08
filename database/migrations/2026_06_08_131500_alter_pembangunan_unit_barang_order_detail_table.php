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
            // Hapus kolom sistem return lama
            $table->dropColumn(['jumlah_return', 'keterangan_return']);

            // Tambah kolom sistem baru
            $table->decimal('jumlah_final', 18, 3)->nullable()->after('jumlah_base');
            $table->foreignId('nota_detail_id')->nullable()->after('jumlah_final')
                ->constrained('nota_barang_masuk_detail')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembangunan_unit_barang_order_detail', function (Blueprint $table) {
            // Hapus kolom baru
            $table->dropForeign(['nota_detail_id']);
            $table->dropColumn(['jumlah_final', 'nota_detail_id']);

            // Kembalikan kolom lama
            $table->decimal('jumlah_return', 18, 3)->default(0)->after('jumlah_base');
            $table->text('keterangan_return')->nullable()->after('jumlah_return');
        });
    }
};
