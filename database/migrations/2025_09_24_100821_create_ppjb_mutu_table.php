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
        Schema::create('ppjb_mutu', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mutu');
             $table->string('slug')->unique(); 
            $table->decimal('nominal_mutu', 15, 2)->default(0);
            $table->boolean('status_aktif')->default(false);
            $table->enum('status_pengajuan', ['pending', 'acc', 'tolak'])->default('pending');
            $table->foreignId('diajukan_oleh')->constrained('users')->onDelete('cascade');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('tanggal_pengajuan')->nullable();
            $table->dateTime('tanggal_acc')->nullable();
            $table->text('catatan_penolakan')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppjb_mutu');
    }
};
