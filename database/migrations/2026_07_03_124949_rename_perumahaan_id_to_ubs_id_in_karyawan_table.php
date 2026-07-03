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
        if (Schema::hasColumn('karyawan', 'perumahaan_id')) {
            Schema::table('karyawan', function (Blueprint $table) {
                $table->dropForeign(['perumahaan_id']);
            });

            Schema::table('karyawan', function (Blueprint $table) {
                $table->renameColumn('perumahaan_id', 'ubs_id');
            });

            Schema::table('karyawan', function (Blueprint $table) {
                $table->foreign('ubs_id')->references('id')->on('ubs')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('karyawan', 'ubs_id')) {
            Schema::table('karyawan', function (Blueprint $table) {
                $table->dropForeign(['ubs_id']);
            });

            Schema::table('karyawan', function (Blueprint $table) {
                $table->renameColumn('ubs_id', 'perumahaan_id');
            });

            Schema::table('karyawan', function (Blueprint $table) {
                $table->foreign('perumahaan_id')->references('id')->on('ubs')->onDelete('set null');
            });
        }
    }
};
