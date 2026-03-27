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
        Schema::create('pembangunan_unit_rap_upah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembangunan_unit_id')->constrained('pembangunan_unit')->cascadeOnDelete();
            $table->foreignId('pembangunan_unit_qc_id')->constrained('pembangunan_unit_qc')->cascadeOnDelete();
            $table->foreignId('master_rap_upah_id')->nullable()->constrained('master_rap_upah')->onDelete('set null');
            $table->string('nama_upah');
            $table->string('nominal_standar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembangunan_unit_rap_upah');
    }
};
