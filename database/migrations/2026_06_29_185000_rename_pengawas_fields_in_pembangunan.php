<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function run(): void
    {
        if (Schema::hasColumn('pembangunan_kawasan', 'pengawas_kawasan')) {
            Schema::table('pembangunan_kawasan', function (Blueprint $table) {
                $table->renameColumn('pengawas_kawasan', 'pengawas_id');
            });
        }

        if (Schema::hasColumn('pembangunan_proyek', 'pengawas_unit')) {
            Schema::table('pembangunan_proyek', function (Blueprint $table) {
                $table->renameColumn('pengawas_unit', 'pengawas_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pembangunan_kawasan', 'pengawas_id')) {
            Schema::table('pembangunan_kawasan', function (Blueprint $table) {
                $table->renameColumn('pengawas_id', 'pengawas_kawasan');
            });
        }

        if (Schema::hasColumn('pembangunan_proyek', 'pengawas_id')) {
            Schema::table('pembangunan_proyek', function (Blueprint $table) {
                $table->renameColumn('pengawas_id', 'pengawas_unit');
            });
        }
    }
};
