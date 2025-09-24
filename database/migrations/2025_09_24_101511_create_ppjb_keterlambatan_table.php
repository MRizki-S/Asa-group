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
        Schema::create('ppjb_keterlambatan', function (Blueprint $table) {
            $table->id();
            $table->decimal('persentase_denda', 5, 2)->default(0); // contoh: 2.50%
            $table->boolean('status_aktif')->default(false);
            $table->enum('status_pengajuan', ['pending', 'acc', 'tolak'])->default('pending');
            $table->foreignId('diajukan_oleh')->constrained('users')->onDelete('cascade');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppjb_keterlambatan');
    }
};
