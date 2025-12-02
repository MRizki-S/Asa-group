<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('pembangunan_unit', function (Blueprint $table) {
            $table->id();

            // relasi unit
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();

            // relasi perumahaan
            $table->foreignId('perumahaan_id')->constrained('perumahaan')->cascadeOnDelete();

            // relasi company
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            // tanggal
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();

            // status pembangunan
            $table->enum('status_pembangunan', ['proses', 'selesai'])->default('proses');

            // status serah terima
            $table->enum('status_serah_terima', [
                'pending',
                'siap_serah_terima',
                'lpa'
            ])->default('pending');

            // pengawas
            $table->foreignId('pengawas_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembangunan_unit');
    }
};
