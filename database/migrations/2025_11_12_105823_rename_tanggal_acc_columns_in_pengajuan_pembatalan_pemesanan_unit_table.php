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
            $table->renameColumn('tanggal_acc_pemasaran', 'tanggal_respon_pemasaran');
            $table->renameColumn('tanggal_acc_keuangan', 'tanggal_respon_keuangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_pembatalan_pemesanan_unit', function (Blueprint $table) {
            $table->renameColumn('tanggal_respon_pemasaran', 'tanggal_acc_pemasaran');
            $table->renameColumn('tanggal_respon_keuangan', 'tanggal_acc_keuangan');
        });
    }
};
