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
        // 1. Tabel snapshot item harga sudah termasuk biaya administrasi (KPR)
        Schema::create('pemesanan_unit_promo_admin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_unit_id')->constrained('pemesanan_unit')->onDelete('cascade');
            $table->string('nama_promo');
            $table->timestamps();
        });

        // 2. Tambah kolom catatan_biaya_administrasi di tabel pemesanan_unit_kpr
        Schema::table('pemesanan_unit_kpr', function (Blueprint $table) {
            $table->text('catatan_biaya_administrasi')->nullable()->after('total_biaya_administrasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanan_unit_promo_admin');

        Schema::table('pemesanan_unit_kpr', function (Blueprint $table) {
            if (Schema::hasColumn('pemesanan_unit_kpr', 'catatan_biaya_administrasi')) {
                $table->dropColumn('catatan_biaya_administrasi');
            }
        });
    }
};
