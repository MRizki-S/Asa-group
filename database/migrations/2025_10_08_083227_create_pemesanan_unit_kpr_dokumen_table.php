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
        Schema::create('pemesanan_unit_kpr_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_unit_kpr_id')->constrained('pemesanan_unit_kpr')->onDelete('cascade');
            $table->foreignId('master_kpr_dokumen_id')->constrained('master_kpr_dokumen');
            $table->boolean('status')->default(false);
            $table->timestamp('tanggal_update')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanan_unit_kpr_dokumen');
    }
};
