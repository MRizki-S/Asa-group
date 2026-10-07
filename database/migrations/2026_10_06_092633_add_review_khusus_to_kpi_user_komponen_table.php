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
        Schema::table('kpi_user_komponen', function (Blueprint $table) {
            $table->boolean('is_review_khusus')->default(false)->after('nilai_tetap');
            $table->text('alasan_review_khusus')->nullable()->after('is_review_khusus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpi_user_komponen', function (Blueprint $table) {
            $table->dropColumn(['is_review_khusus', 'alasan_review_khusus']);
        });
    }
};
