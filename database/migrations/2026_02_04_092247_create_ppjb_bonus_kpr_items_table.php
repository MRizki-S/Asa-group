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
        Schema::create('ppjb_bonus_kpr_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('ppjb_bonus_kpr_batch')->onDelete('cascade');
            $table->string('nama_bonus');
            $table->decimal('nominal_bonus', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppjb_bonus_kpr_items');
    }
};
