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
        Schema::create('pembangunan_unit_qc_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembangunan_unit_qc_id')->constrained('pembangunan_unit_qc')->cascadeOnDelete();
            $table->string('tugas');
            $table->boolean('selesai')->default(false);
            $table->enum('keterangan_selesai', [
                'sesuai',
                'sesuai dengan catatan',
                'belum sesuai'
            ])->default('belum sesuai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembangunan_unit_qc_task');
    }
};
