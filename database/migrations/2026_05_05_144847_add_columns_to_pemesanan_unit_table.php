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
        Schema::table('pemesanan_unit', function (Blueprint $table) {
            // Cek dulu apakah kolom sudah ada sebelum ditambahkan (untuk menghindari error jika sudah ada di DB)
            if (!Schema::hasColumn('pemesanan_unit', 'agent_id')) {
                $table->foreignId('agent_id')->nullable()->after('sales_id')->constrained('master_agent');
            }
            if (!Schema::hasColumn('pemesanan_unit', 'source')) {
                $table->enum('source', ['internal', 'agent'])->default('internal')->after('agent_id');
            }
            if (!Schema::hasColumn('pemesanan_unit', 'input_by')) {
                $table->foreignId('input_by')->nullable()->after('source')->constrained('users');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemesanan_unit', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropForeign(['input_by']);
            $table->dropColumn(['agent_id', 'source', 'input_by']);
        });
    }
};
