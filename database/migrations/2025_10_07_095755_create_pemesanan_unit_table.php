<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pemesanan_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perumahaan_id')->constrained('perumahaan');
            $table->foreignId('tahap_id')->constrained('tahap');
            $table->foreignId('unit_id')->constrained('unit');
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('sales_id')->constrained('users');
            $table->date('tanggal_pemesanan');
            $table->enum('cara_bayar', ['cash', 'kpr']);
            $table->enum('status_pengajuan', ['pending', 'acc', 'tolak'])->default('pending');
            $table->enum('status_pemesanan', ['proses', 'LPA', 'serah_terima', 'batal'])->nullable();
            $table->decimal('harga_normal', 15, 2)->default(0);
            $table->decimal('harga_cash', 15, 2)->nullable();
            $table->decimal('total_tagihan', 15, 2)->default(0);
            $table->decimal('sisa_tagihan', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan_unit');
    }
};
