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
        Schema::table('pembangunan_proyek_barang_order', function (Blueprint $table) {
            $table->foreignId('ubs_id')->nullable()->constrained('ubs')->nullOnDelete();
        });
        
        Schema::table('pembangunan_kawasan_barang_order', function (Blueprint $table) {
            $table->foreignId('ubs_id')->nullable()->constrained('ubs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pembangunan_proyek_barang_order', function (Blueprint $table) {
            $table->dropForeign(['ubs_id']);
            $table->dropColumn('ubs_id');
        });
        
        Schema::table('pembangunan_kawasan_barang_order', function (Blueprint $table) {
            $table->dropForeign(['ubs_id']);
            $table->dropColumn('ubs_id');
        });
    }
};
