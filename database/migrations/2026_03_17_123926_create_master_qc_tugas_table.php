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
        Schema::create('master_qc_tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_qc_urutan_id')->constrained('master_qc_urutan')->cascadeOnDelete();
            $table->string('tugas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_qc_tugas');
    }
};
