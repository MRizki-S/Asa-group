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
        Schema::create('pembangunan_unit_barang_return', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembangunan_unit_id')->constrained('pembangunan_unit');
            $table->foreignId('pembangunan_unit_qc_id')->nullable()->constrained('pembangunan_unit_qc');
            $table->string('nomor_return')->unique();
            $table->dateTime('tanggal_return');
            $table->text('catatan')->nullable();
            
            $table->enum('status',[
                'draft',
                'diproses',
                'selesai',
                'ditolak'
            ])->default('draft');
            
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('acc_by')->nullable()->constrained('users')->restrictOnDelete();
            
            $table->dateTime('acc_at')->nullable();
            $table->text('alasan_tolak')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['pembangunan_unit_id', 'pembangunan_unit_qc_id'], 'idx_return_unit_qc');
            $table->index('status', 'idx_return_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembangunan_unit_barang_return');
    }
};
