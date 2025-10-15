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
        Schema::create('pemesanan_unit_cash_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_unit_cash_id')->constrained('pemesanan_unit_cash')->onDelete('cascade');
            $table->string('nama_dokumen');
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
        Schema::dropIfExists('pemesanan_unit_cash_dokumen');
    }
};
