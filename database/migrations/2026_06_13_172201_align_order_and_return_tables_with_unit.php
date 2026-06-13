<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Order Details Proyek
        Schema::table('pembangunan_proyek_barang_order_detail', function (Blueprint $table) {
            $table->decimal('jumlah_final', 18, 3)->nullable()->after('jumlah_input');
            $table->unsignedBigInteger('nota_detail_id')->nullable()->after('jumlah_final');
        });

        // Order Details Kawasan
        Schema::table('pembangunan_kawasan_barang_order_detail', function (Blueprint $table) {
            $table->decimal('jumlah_final', 18, 3)->nullable()->after('jumlah_input');
            $table->unsignedBigInteger('nota_detail_id')->nullable()->after('jumlah_final');
        });

        // Returns Proyek
        Schema::table('pembangunan_proyek_barang_returns', function (Blueprint $table) {
            $table->renameColumn('status_return', 'status');
            $table->renameColumn('created_by', 'diajukan_oleh');
            $table->renameColumn('tanggal_return', 'tanggal_diajukan');
            $table->unsignedBigInteger('direspon_oleh')->nullable();
            $table->timestamp('tanggal_direspon')->nullable();
            $table->text('alasan_ditolak')->nullable();
        });
        Schema::table('pembangunan_proyek_barang_returns', function (Blueprint $table) {
            $table->dropColumn('alasan_return');
        });

        // Returns Kawasan
        Schema::table('pembangunan_kawasan_barang_returns', function (Blueprint $table) {
            $table->renameColumn('status_return', 'status');
            $table->renameColumn('created_by', 'diajukan_oleh');
            $table->renameColumn('tanggal_return', 'tanggal_diajukan');
            $table->unsignedBigInteger('direspon_oleh')->nullable();
            $table->timestamp('tanggal_direspon')->nullable();
            $table->text('alasan_ditolak')->nullable();
        });
        Schema::table('pembangunan_kawasan_barang_returns', function (Blueprint $table) {
            $table->dropColumn('alasan_return');
        });
    }

    public function down(): void
    {
        // ... (down method can be sparse for this quick fix)
    }
};
