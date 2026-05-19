<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up(): void
    {
        Schema::create('pemesanan_unit_fee_agent', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pemesanan_unit_id')
                ->constrained('pemesanan_unit')
                ->cascadeOnDelete();

            $table->foreignId('master_agent_fee_id')
                ->constrained('master_agent_fee')
                ->cascadeOnDelete();

            // 🔥 snapshot nominal saat transaksi
            $table->decimal('nominal_snapshot', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan_unit_fee_agent');
    }
};
