<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_supplier', function (Blueprint $table) {
            $table->id();
            $table->string('kode_supplier', 50)->unique();
            $table->string('nama_supplier', 255);
            $table->string('kategori_supplier', 100)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('npwp', 50)->nullable();
            $table->string('telepon', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('alamat')->nullable();
            $table->string('rekening_bank', 100)->nullable();
            $table->string('no_rekening', 100)->nullable();
            $table->timestamps();
        });

        // Seed initial suppliers
        DB::table('master_supplier')->insert([
            [
                'kode_supplier' => 'SPL-0001',
                'nama_supplier' => 'PT Semen Indonesia',
                'kategori_supplier' => 'Semen',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode_supplier' => 'SPL-0002',
                'nama_supplier' => 'PT Besi Jaya',
                'kategori_supplier' => 'Besi',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode_supplier' => 'SPL-0003',
                'nama_supplier' => 'Produksi Rakitan Internal',
                'kategori_supplier' => 'Internal',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode_supplier' => 'SPL-0004',
                'nama_supplier' => 'Return Barang Proyek',
                'kategori_supplier' => 'Internal',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_supplier');
    }
};
