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
        Schema::create('marketing_target_quarter', function (Blueprint $table) {
            $table->id();

            $table->foreignId('perumahaan_id')
                ->constrained('perumahaan')
                ->cascadeOnDelete();

            $table->year('tahun');
            $table->enum('quarter', ['Q1', 'Q2', 'Q3', 'Q4']);

            $table->unsignedInteger('target_penjualan_quarter')->default(0);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['perumahaan_id', 'tahun', 'quarter'], 'mtq_unique_perumahaan_tahun_quarter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_target_quarter');
    }
};
