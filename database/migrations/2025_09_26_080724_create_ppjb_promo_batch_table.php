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
        Schema::create('ppjb_promo_batch', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['cash', 'kpr']);
            $table->boolean('status_aktif')->default(false);
            $table->enum('status_pengajuan', ['pending', 'acc', 'tolak'])->default('pending');
            $table->foreignId('diajukan_oleh')->constrained('users');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users');
            $table->timestamp('tanggal_pengajuan')->useCurrent();
            $table->timestamp('tanggal_acc')->nullable();
            $table->text('catatan_penolakan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppjb_promo_batch');
    }
};
