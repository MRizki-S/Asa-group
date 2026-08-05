<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nomor nota tidak lagi di-generate saat create.
     * Draft disimpan dengan nomor_nota = NULL.
     * Nomor nota (NOTA-YYYYMMDD-NNNN) baru di-generate saat posting.
     */
    public function up(): void
    {
        Schema::table('nota_barang_masuk', function (Blueprint $table) {
            $table->string('nomor_nota', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('nota_barang_masuk', function (Blueprint $table) {
            $table->string('nomor_nota', 100)->nullable(false)->change();
        });
    }
};
