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
        Schema::create('pengajuan_pembatalan_pemesanan_unit', function (Blueprint $table) {
            $table->id();

            // 🔗 Relasi ke pemesanan unit
            $table->foreignId('pemesanan_unit_id')
                ->constrained('pemesanan_unit')
                ->onDelete('cascade');

            // 📝 Data pengajuan
            $table->text('alasan_pembatalan');
            $table->text('alasan_detail')->nullable();
            $table->string('bukti_pembatalan')->nullable();
            $table->boolean('pengecualian_potongan')->default(false);

            // 🧭 Status utama dan per level approval
            $table->enum('status_pengajuan', ['pending', 'acc', 'tolak'])->default('pending');
            $table->enum('status_mgr_pemasaran', ['pending', 'acc', 'tolak'])->default('pending');
            $table->enum('status_mgr_keuangan', ['pending', 'acc', 'tolak'])->default('pending');

            // 👥 User terkait
            $table->foreignId('diajukan_oleh')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('disetujui_pemasaran_oleh')
                ->nullable()
                ->constrained('users', 'id', 'fk_pembatalan_pemasaran_user')
                ->nullOnDelete();

            $table->foreignId('disetujui_keuangan_oleh')
                ->nullable()
                ->constrained('users', 'id', 'fk_pembatalan_keuangan_user')
                ->nullOnDelete();

            // 🗓️ Tanggal-tanggal penting
            $table->timestamp('tanggal_pengajuan')->useCurrent();
            $table->timestamp('tanggal_acc_pemasaran')->nullable();
            $table->timestamp('tanggal_acc_keuangan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_pembatalan_pemesanan_unit');
    }
};
