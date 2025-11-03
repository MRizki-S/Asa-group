<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pemesanan_unit_data_diri', function (Blueprint $table) {
            $table->string('no_ktp', 20)->nullable()->after('nama_pribadi');
            $table->string('pekerjaan', 100)->nullable()->after('no_ktp');
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan_unit_data_diri', function (Blueprint $table) {
            $table->dropColumn(['no_ktp', 'pekerjaan']);
        });
    }
};
