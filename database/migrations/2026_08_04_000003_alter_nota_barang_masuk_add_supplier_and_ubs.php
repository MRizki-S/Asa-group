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
            $table->dropColumn('supplier');
            
            $table->foreignId('supplier_id')
                ->nullable()
                ->after('tanggal_nota')
                ->constrained('master_supplier')
                ->nullOnDelete();

            $table->string('stock_type', 50)
                ->default('UBS')
                ->after('cara_bayar');

            $table->foreignId('ubs_id')
                ->nullable()
                ->after('stock_type')
                ->constrained('ubs')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nota_barang_masuk', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
            $table->dropForeign(['ubs_id']);
            $table->dropColumn('ubs_id');
            $table->dropColumn('stock_type');

            $table->string('supplier', 255)->nullable()->after('tanggal_nota');
        });
    }
};
