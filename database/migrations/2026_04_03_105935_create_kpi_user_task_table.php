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
        Schema::create('kpi_user_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_user_komponen_id');
            $table->foreignId('task_id');
            $table->string('nama_task');
            $table->decimal('target', 8, 2)->nullable();
            $table->decimal('tercapai', 8, 2)->nullable();
            $table->decimal('nilai', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_user_task');
    }
};
