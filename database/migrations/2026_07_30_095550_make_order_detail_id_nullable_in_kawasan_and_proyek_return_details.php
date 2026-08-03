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
        Schema::table('pembangunan_kawasan_barang_return_details', function (Blueprint $table) {
            $table->unsignedBigInteger('order_detail_id')->nullable()->change();
        });

        Schema::table('pembangunan_proyek_barang_return_details', function (Blueprint $table) {
            $table->unsignedBigInteger('order_detail_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembangunan_kawasan_barang_return_details', function (Blueprint $table) {
            $table->unsignedBigInteger('order_detail_id')->nullable(false)->change();
        });

        Schema::table('pembangunan_proyek_barang_return_details', function (Blueprint $table) {
            $table->unsignedBigInteger('order_detail_id')->nullable(false)->change();
        });
    }
};
