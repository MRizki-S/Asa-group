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
        Schema::create('ppjb_mutu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('ppjb_mutu_batch');
            $table->string('nama_mutu');
            $table->decimal('nominal_mutu', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppjb_mutu_items');
    }
};
    