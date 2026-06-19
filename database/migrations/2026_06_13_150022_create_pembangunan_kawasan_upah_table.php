<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_kawasan_upah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembangunan_kawasan_id')->constrained('pembangunan_kawasan')->onDelete('cascade');
            $table->string('nama_upah');
            $table->decimal('total_nominal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_kawasan_upah');
    }
};