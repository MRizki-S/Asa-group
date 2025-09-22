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
        Schema::create('customer_booking', function (Blueprint $table) {
            $table->id();

            // relasi ke user (1:1, karena tiap customer cuma bisa punya 1 booking aktif)
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');

            // relasi ke etalase
            $table->foreignId('perumahaan_id')->constrained('perumahaan')->onDelete('cascade');
            $table->foreignId('tahap_id')->constrained('tahap')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('unit')->onDelete('cascade');

            // tambahan field
            $table->string('slug')->unique();
            $table->date('tanggal_booking');
            $table->date('tanggal_expired');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_booking');
    }
};
