<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transfer_gudang_hub_ubs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barang_id')
                ->constrained('master_barang')
                ->cascadeOnDelete();

            $table->foreignId('ke_ubs_id')
                ->constrained('ubs')
                ->cascadeOnDelete();

            $table->date('tanggal_transfer');

            $table->integer('jumlah_kirim');

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_gudang_hub_ubs');
    }
};
