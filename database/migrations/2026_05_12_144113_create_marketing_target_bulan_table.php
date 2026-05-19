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
        Schema::create('marketing_target_bulan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('marketing_target_quarter_id')
                ->constrained('marketing_target_quarter')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('bulan');
            $table->unsignedInteger('target_penjualan_bulan')->default(0);

            $table->timestamps();

            $table->unique(['marketing_target_quarter_id', 'bulan'], 'mtb_unique_quarter_bulan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_target_bulan');
    }
};
