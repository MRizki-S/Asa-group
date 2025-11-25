<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan_unit_cicilan', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('tanggal_pembayaran');
            $table->unsignedBigInteger('adendum_id')->nullable()->after('is_active');

            $table->foreign('adendum_id')
                ->references('id')
                ->on('adendum')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan_unit_cicilan', function (Blueprint $table) {
            $table->dropForeign(['adendum_id']);
            $table->dropColumn(['is_active', 'adendum_id']);
        });
    }
};
