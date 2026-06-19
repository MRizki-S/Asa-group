<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembangunan_proyek_barang_return_details', function (Blueprint $table) {
            $table->unsignedBigInteger('barang_id')->nullable();
            $table->string('satuan', 50)->nullable();
            $table->text('keterangan_return')->nullable();
        });

        Schema::table('pembangunan_kawasan_barang_return_details', function (Blueprint $table) {
            $table->unsignedBigInteger('barang_id')->nullable();
            $table->string('satuan', 50)->nullable();
            $table->text('keterangan_return')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pembangunan_proyek_barang_return_details', function (Blueprint $table) {
            $table->dropColumn(['barang_id', 'satuan', 'keterangan_return']);
        });

        Schema::table('pembangunan_kawasan_barang_return_details', function (Blueprint $table) {
            $table->dropColumn(['barang_id', 'satuan', 'keterangan_return']);
        });
    }
};
