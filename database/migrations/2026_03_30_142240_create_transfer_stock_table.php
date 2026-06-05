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
        Schema::create('transfer_stock', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transfer')->unique();
            $table->dateTime('tanggal_transfer');

            $table->enum('dari_stock_type', ['HUB', 'UBS']);
            $table->unsignedBigInteger('dari_ubs_id')->nullable();

            $table->enum('ke_stock_type', ['HUB', 'UBS']);
            $table->unsignedBigInteger('ke_ubs_id')->nullable();

            $table->text('keterangan')->nullable();

            $table->unsignedBigInteger('created_by');

            $table->timestamps();

            // Optional FK (kalau mau strict)
            $table->foreign('dari_ubs_id')->references('id')->on('ubs')->nullOnDelete();
            $table->foreign('ke_ubs_id')->references('id')->on('ubs')->nullOnDelete();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_stock');
    }
};
