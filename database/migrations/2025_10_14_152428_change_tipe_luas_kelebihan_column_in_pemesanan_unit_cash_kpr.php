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
        // Pemesanan Unit Cash
        Schema::table('pemesanan_unit_cash', function (Blueprint $table) {
            $table->string('luas_kelebihan', 50)->nullable()->change();
        });

        // Pemesanan Unit KPR
        Schema::table('pemesanan_unit_kpr', function (Blueprint $table) {
            $table->string('luas_kelebihan', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke decimal 15,2
        Schema::table('pemesanan_unit_cash', function (Blueprint $table) {
            $table->decimal('luas_kelebihan', 15, 2)->nullable()->change();
        });

        Schema::table('pemesanan_unit_kpr', function (Blueprint $table) {
            $table->decimal('luas_kelebihan', 10, 2)->nullable()->change();
        });
    }
};
