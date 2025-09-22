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
        Schema::create('tahap_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahap_id')->constrained('tahap')->onDelete('cascade');
            $table->foreignId('type_id')->constrained('type')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tahap_type');
    }
};
