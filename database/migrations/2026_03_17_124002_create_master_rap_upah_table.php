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
        Schema::create('master_rap_upah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained('type')->cascadeOnDelete();
            $table->foreignId('master_qc_container_id')->constrained('master_qc_container')->cascadeOnDelete();
            $table->foreignId('master_qc_urutan_id')->constrained('master_qc_urutan')->cascadeOnDelete();
            $table->foreignId('master_upah_id')->constrained('master_upah')->cascadeOnDelete();
            $table->string('nominal_standar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_rap_upah');
    }
};
