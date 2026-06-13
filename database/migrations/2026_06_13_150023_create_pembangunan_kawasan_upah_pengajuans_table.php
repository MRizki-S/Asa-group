<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembangunan_kawasan_upah_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembangunan_kawasan_id')->constrained('pembangunan_kawasan', 'id', 'pkup_kawasan_fk')->onDelete('cascade');
            $table->string('nama_upah');
            $table->decimal('nominal_diajukan', 18, 2);
            $table->text('catatan_pengawas')->nullable();
            $table->enum('status_pengajuan', [
                'req_mgr_produksi',
                'ditolak_mgr_produksi',
                'req_mgr_dukungan',
                'ditolak_mgr_dukungan',
                'req_akuntan',
                'ditolak_akuntan',
                'disetujui'
            ])->default('req_mgr_produksi');
            $table->dateTime('tanggal_diajukan')->nullable();
            $table->timestamp('disetujui_mgr_produksi')->nullable();
            $table->timestamp('disetujui_mgr_dukungan')->nullable();
            $table->timestamp('disetujui_akuntan')->nullable();
            $table->text('alasan_ditolak')->nullable();
            $table->timestamp('ditolak_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembangunan_kawasan_upah_pengajuan');
    }
};