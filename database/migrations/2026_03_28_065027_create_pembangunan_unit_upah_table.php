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
        Schema::create('pembangunan_unit_upah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembangunan_unit_id')
                ->constrained('pembangunan_unit')
                ->onDelete('cascade');

            $table->foreignId('pembangunan_unit_qc_id')
                ->constrained('pembangunan_unit_qc')
                ->onDelete('cascade');

            $table->string('nama_upah');
            $table->decimal('total_nominal', 15, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembangunan_unit_upah');
    }
};
