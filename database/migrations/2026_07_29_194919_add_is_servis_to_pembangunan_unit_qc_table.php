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
        Schema::table('pembangunan_unit_qc', function (Blueprint $table) {
            $table->boolean('is_servis')->default(false)->after('nama_qc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembangunan_unit_qc', function (Blueprint $table) {
            $table->dropColumn('is_servis');
        });
    }
};
