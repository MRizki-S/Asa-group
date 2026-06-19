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
        Schema::create('barang_rakitan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barang_hasil_id')
                ->constrained('master_barang')
                ->restrictOnDelete();

            $table->foreignId('satuan_hasil_id')
                ->constrained('master_satuan')
                ->restrictOnDelete();

            $table->decimal('qty_hasil', 18, 3);
            $table->decimal('qty_hasil_base', 18, 3);

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('keterangan')->nullable();

            $table->foreignId('created_by')
                ->constrained('users');

            $table->timestamps();

            $table->index('status');
            $table->index('barang_hasil_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_rakitan');
    }
};
