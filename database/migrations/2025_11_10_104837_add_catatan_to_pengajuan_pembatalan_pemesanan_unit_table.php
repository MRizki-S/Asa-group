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
        Schema::table('pengajuan_pembatalan_pemesanan_unit', function (Blueprint $table) {
            // setelah status_mgr_pemasaran dan status_mgr_keuangan
            $table->text('catatan_mgr_pemasaran')
                ->nullable()
                ->after('status_mgr_pemasaran');

            $table->text('catatan_mgr_keuangan')
                ->nullable()
                ->after('status_mgr_keuangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_pembatalan_pemesanan_unit', function (Blueprint $table) {
            $table->dropColumn(['catatan_mgr_pemasaran', 'catatan_mgr_keuangan']);
        });
    }
};
