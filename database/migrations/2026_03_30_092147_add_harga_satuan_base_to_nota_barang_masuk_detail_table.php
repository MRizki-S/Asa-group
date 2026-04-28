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
        Schema::table('nota_barang_masuk_detail', function (Blueprint $table) {
            $table->decimal('harga_satuan_base', 15, 2)
                ->after('harga_satuan')
                ->comment('Harga per base unit (hasil konversi)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nota_barang_masuk_detail', function (Blueprint $table) {
            $table->dropColumn('harga_satuan_base');
        });
    }
};
