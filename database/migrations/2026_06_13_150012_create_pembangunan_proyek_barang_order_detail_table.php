<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_proyek_barang_order_detail', function (Blueprint $group) {
            $group->id();
            $group->foreignId('order_id')->constrained('pembangunan_proyek_barang_order')->onDelete('cascade');
            $group->foreignId('barang_id')->nullable()->constrained('master_barang')->onDelete('set null');
            $group->foreignId('satuan_id')->nullable()->constrained('master_satuan')->onDelete('set null');
            $group->unsignedBigInteger('ubs_id')->nullable();

            $group->decimal('jumlah_input', 18, 3);
            $group->string('nama_barang')->nullable();
            $group->string('satuan')->nullable();
            $group->decimal('jumlah_base', 18, 3);

            $group->boolean('konfirmasi')->default(false);

            $group->decimal('jumlah_return', 18, 3)->default(0);
            $group->text('keterangan_return')->nullable();

            $group->decimal('harga_satuan_snapshot', 18, 2)->nullable();
            $group->decimal('harga_total_snapshot', 18, 2)->nullable();

            $group->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_proyek_barang_order_detail');
    }
};