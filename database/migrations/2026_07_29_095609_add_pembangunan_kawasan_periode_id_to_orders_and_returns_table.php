<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembangunan_kawasan_barang_order', function (Blueprint $table) {
            $table->unsignedBigInteger('pembangunan_kawasan_periode_id')->nullable()->after('pembangunan_kawasan_id');
            $table->foreign('pembangunan_kawasan_periode_id', 'fk_kws_ord_periode')
                ->references('id')->on('pembangunan_kawasan_periode')
                ->nullOnDelete();
        });

        Schema::table('pembangunan_kawasan_barang_returns', function (Blueprint $table) {
            $table->unsignedBigInteger('pembangunan_kawasan_periode_id')->nullable()->after('pembangunan_kawasan_id');
            $table->foreign('pembangunan_kawasan_periode_id', 'fk_kws_ret_periode')
                ->references('id')->on('pembangunan_kawasan_periode')
                ->nullOnDelete();
        });

        // Backfill existing orders
        $orders = DB::table('pembangunan_kawasan_barang_order')->get();
        foreach ($orders as $ord) {
            $periode = DB::table('pembangunan_kawasan_periode')
                ->where('pembangunan_kawasan_id', $ord->pembangunan_kawasan_id)
                ->where('created_at', '<=', $ord->created_at)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$periode) {
                $periode = DB::table('pembangunan_kawasan_periode')
                    ->where('pembangunan_kawasan_id', $ord->pembangunan_kawasan_id)
                    ->orderBy('created_at', 'asc')
                    ->first();
            }

            if ($periode) {
                DB::table('pembangunan_kawasan_barang_order')
                    ->where('id', $ord->id)
                    ->update(['pembangunan_kawasan_periode_id' => $periode->id]);
            }
        }

        // Backfill existing returns
        $returns = DB::table('pembangunan_kawasan_barang_returns')->get();
        foreach ($returns as $ret) {
            $periode = DB::table('pembangunan_kawasan_periode')
                ->where('pembangunan_kawasan_id', $ret->pembangunan_kawasan_id)
                ->where('created_at', '<=', $ret->created_at)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$periode) {
                $periode = DB::table('pembangunan_kawasan_periode')
                    ->where('pembangunan_kawasan_id', $ret->pembangunan_kawasan_id)
                    ->orderBy('created_at', 'asc')
                    ->first();
            }

            if ($periode) {
                DB::table('pembangunan_kawasan_barang_returns')
                    ->where('id', $ret->id)
                    ->update(['pembangunan_kawasan_periode_id' => $periode->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('pembangunan_kawasan_barang_order', function (Blueprint $table) {
            $table->dropForeign('fk_kws_ord_periode');
            $table->dropColumn('pembangunan_kawasan_periode_id');
        });

        Schema::table('pembangunan_kawasan_barang_returns', function (Blueprint $table) {
            $table->dropForeign('fk_kws_ret_periode');
            $table->dropColumn('pembangunan_kawasan_periode_id');
        });
    }
};
