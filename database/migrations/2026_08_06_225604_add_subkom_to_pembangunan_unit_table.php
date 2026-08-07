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
        Schema::table('pembangunan_unit', function (Blueprint $table) {
            $table->string('subcon')->nullable()->after('status_pembangunan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembangunan_unit', function (Blueprint $table) {
            $table->dropColumn('subcon');
        });
    }
};
