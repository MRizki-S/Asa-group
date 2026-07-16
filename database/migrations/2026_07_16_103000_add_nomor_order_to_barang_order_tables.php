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
        Schema::table('pembangunan_unit_barang_order', function (Blueprint $table) {
            $table->string('nomor_order')->nullable()->unique()->after('id');
        });

        Schema::table('pembangunan_kawasan_barang_order', function (Blueprint $table) {
            $table->string('nomor_order')->nullable()->unique()->after('id');
        });

        Schema::table('pembangunan_proyek_barang_order', function (Blueprint $table) {
            $table->string('nomor_order')->nullable()->unique()->after('id');
        });

        // Jika nanti sudah ada tabel servis unit
        // Schema::table('pembangunan_servis_barang_order', function (Blueprint $table) {
        //     $table->string('nomor_order')->nullable()->unique()->after('id');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembangunan_unit_barang_order', function (Blueprint $table) {
            $table->dropColumn('nomor_order');
        });

        Schema::table('pembangunan_kawasan_barang_order', function (Blueprint $table) {
            $table->dropColumn('nomor_order');
        });

        Schema::table('pembangunan_proyek_barang_order', function (Blueprint $table) {
            $table->dropColumn('nomor_order');
        });

        // Schema::table('pembangunan_servis_barang_order', function (Blueprint $table) {
        //     $table->dropColumn('nomor_order');
        // });
    }
};