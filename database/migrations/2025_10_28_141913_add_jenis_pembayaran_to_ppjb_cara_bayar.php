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
        Schema::table('ppjb_cara_bayar', function (Blueprint $table) {
             // jenis pembayaran: KPR atau CASH
            if (! Schema::hasColumn('ppjb_cara_bayar', 'jenis_pembayaran')) {
                $table->enum('jenis_pembayaran', ['KPR', 'CASH'])
                      ->default('CASH')
                      ->after('perumahaan_id');
            }

            // nama cara bayar (boleh null sementara, app logic yang validasi)
            if (! Schema::hasColumn('ppjb_cara_bayar', 'nama_cara_bayar')) {
                $table->string('nama_cara_bayar')->nullable()->after('jenis_pembayaran');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppjb_cara_bayar', function (Blueprint $table) {
            // hapus kolom dengan urutan terbalik
            if (Schema::hasColumn('ppjb_cara_bayar', 'nama_cara_bayar')) {
                $table->dropColumn('nama_cara_bayar');
            }

            if (Schema::hasColumn('ppjb_cara_bayar', 'jenis_pembayaran')) {
                $table->dropColumn('jenis_pembayaran');
            }
        });
    }
};
