<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan_unit_pembatalan', function (Blueprint $table) {
            $table->decimal('nominal_potongan_kpr', 15, 2)->nullable()->after('persentase_potongan');
            $table->decimal('nominal_potongan_cash', 15, 2)->nullable()->after('nominal_potongan_kpr');
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan_unit_pembatalan', function (Blueprint $table) {
            $table->dropColumn(['nominal_potongan_kpr', 'nominal_potongan_cash']);
        });
    }
};
