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
        Schema::table('pembangunan_unit_upah_pengajuan', function (Blueprint $table) {
            if (!Schema::hasColumn('pembangunan_unit_upah_pengajuan', 'nomor_pengajuan')) {
                $table->string('nomor_pengajuan')->nullable()->after('id');
            }
        });

        // Backfill nomor_pengajuan untuk data lama
        $items = DB::table('pembangunan_unit_upah_pengajuan')->whereNull('nomor_pengajuan')->orderBy('id')->get();
        foreach ($items as $item) {
            $tgl = $item->tanggal_diajukan ? \Carbon\Carbon::parse($item->tanggal_diajukan)->format('ymd') : \Carbon\Carbon::parse($item->created_at)->format('ymd');
            $noUnik = str_pad($item->id, 4, '0', STR_PAD_LEFT);
            $nomor = "UBT-{$tgl}-{$noUnik}";

            DB::table('pembangunan_unit_upah_pengajuan')
                ->where('id', $item->id)
                ->update(['nomor_pengajuan' => $nomor]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembangunan_unit_upah_pengajuan', function (Blueprint $table) {
            if (Schema::hasColumn('pembangunan_unit_upah_pengajuan', 'nomor_pengajuan')) {
                $table->dropColumn('nomor_pengajuan');
            }
        });
    }
};
