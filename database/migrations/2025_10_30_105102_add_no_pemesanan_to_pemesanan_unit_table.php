<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('pemesanan_unit', function (Blueprint $table) {
            $table->string('no_pemesanan', 10)->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('pemesanan_unit', function (Blueprint $table) {
            $table->dropColumn('no_pemesanan');
        });
    }
};

