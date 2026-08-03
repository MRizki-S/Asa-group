<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('pembangunan_kawasan_bahan', 'pembangunan_kawasan_periode_id')) {
            Schema::table('pembangunan_kawasan_bahan', function (Blueprint $table) {
                $table->unsignedBigInteger('pembangunan_kawasan_periode_id')->nullable()->after('pembangunan_kawasan_id');
                $table->foreign('pembangunan_kawasan_periode_id', 'fk_kws_bhn_periode')
                    ->references('id')
                    ->on('pembangunan_kawasan_periode')
                    ->onDelete('set null');
            });
        }

        // Backfill data lama jika ada
        $bahans = DB::table('pembangunan_kawasan_bahan')->get();
        foreach ($bahans as $bhn) {
            $periode = DB::table('pembangunan_kawasan_periode')
                ->where('pembangunan_kawasan_id', $bhn->pembangunan_kawasan_id)
                ->where('tanggal_mulai', '<=', $bhn->created_at)
                ->orderBy('tanggal_mulai', 'desc')
                ->first();

            if (!$periode) {
                $periode = DB::table('pembangunan_kawasan_periode')
                    ->where('pembangunan_kawasan_id', $bhn->pembangunan_kawasan_id)
                    ->orderBy('tanggal_mulai', 'asc')
                    ->first();
            }

            if ($periode) {
                DB::table('pembangunan_kawasan_bahan')
                    ->where('id', $bhn->id)
                    ->update(['pembangunan_kawasan_periode_id' => $periode->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembangunan_kawasan_bahan', function (Blueprint $table) {
            $table->dropForeign('fk_kws_bhn_periode');
            $table->dropColumn('pembangunan_kawasan_periode_id');
        });
    }
};
