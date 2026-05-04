<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up(): void
    {
        Schema::create('master_agent_fee', function (Blueprint $table) {
            $table->id();
            $table->string('judul_fee');
            $table->decimal('nominal', 15, 2);

            $table->enum('status_pengajuan', ['pending', 'acc', 'reject'])
                ->default('pending');

            $table->foreignId('diajukan_oleh')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('disetujui_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_agent_fee');
    }
};
