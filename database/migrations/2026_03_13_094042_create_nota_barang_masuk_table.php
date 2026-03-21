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
        Schema::create('nota_barang_masuk', function (Blueprint $table) {
            $table->id();

            $table->string('nomor_nota', 100)->unique();
            $table->date('tanggal_nota');

            $table->string('supplier', 255)->nullable();

            $table->enum('cara_bayar', ['cash', 'hutang'])->nullable();

            $table->enum('status', ['draft', 'posted'])->default('draft');

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamp('posted_at')->nullable();

            $table->timestamps();   

            // indexing 
            $table->index('tanggal_nota');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_barang_masuk');
    }
};
