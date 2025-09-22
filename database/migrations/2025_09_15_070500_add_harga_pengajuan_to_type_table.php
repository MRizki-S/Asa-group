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
        Schema::table('type', function (Blueprint $table) {
            $table->bigInteger('harga_diajukan')->nullable()->after('harga_dasar'); // harga baru yang diajukan

            $table->enum('status_pengajuan', ['pending', 'acc', 'tolak'])->default('pending')->after('harga_diajukan');
            $table->foreignId('diajukan_oleh')->nullable()->constrained('users');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users');
            $table->timestamp('tanggal_pengajuan')->nullable()->after('disetujui_oleh');
            $table->timestamp('tanggal_acc')->nullable()->after('tanggal_pengajuan');
            $table->text('catatan_penolakan')->nullable()->after('tanggal_acc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('type', function (Blueprint $table) {
            //
        });
    }
};
