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
        Schema::create('kpi_komponen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->string('nama_komponen');
            $table->enum('tipe_perhitungan', ['KEPATUHAN', 'DEVIASI_BUDGET', "SELISIH_STOK", "KONDISI_LANGSUNG", "AKKUMULASI_NILAI"]);
            $table->string('label_total');
            $table->string('label_tercapai');
            $table->string('label_tidak_tercapai');
            $table->boolean('is_active');
            $table->timestamps();
        });


        Schema::create('kpi_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('komponen_id')->constrained('kpi_komponen')->onDelete('cascade');
            $table->string('nama_task');
            $table->timestamps();
        });

        Schema::create('kpi_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('bulan');
            $table->string('tahun');
            $table->enum('status', [
                'draft',
                'final'
            ]);
            $table->timestamps();
        });

        Schema::create('kpi_review_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_user_id')->constrained('kpi_user')->onDelete('cascade');
            $table->timestamp('direspon_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('kpi_user_komponen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_user_id')->constrained('kpi_user')->onDelete('cascade');
            $table->foreignId('komponen_id')->constrained('kpi_komponen')->onDelete('cascade');
            $table->string('nama_komponen');
            $table->integer('bobot')->default(0);
            $table->decimal('total_target', 15, 2)->nullable();
            $table->decimal('total_tercapai', 15, 2)->nullable();
            $table->decimal('kepatuhan_percent', 15, 2)->nullable();
            $table->decimal('skor', 8, 2)->nullable();
            $table->decimal('nilai_akhir', 8, 2)->nullable();
            $table->text('catatan_tambahan')->nullable();
            $table->boolean('nilai_tetap')->default(false);
            $table->timestamps();
        });


        Schema::create('kpi_user_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_user_komponen_id')->constrained('kpi_user_komponen')->onDelete('cascade');
            $table->string('nama_task');
            $table->decimal('target', 15, 2)->nullable();
            $table->decimal('tercapai', 15, 2)->nullable();
            $table->decimal('nilai', 15, 2)->nullable();
            $table->text('alasan_tidak_tercapai')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_komponen');
        Schema::dropIfExists('kpi_task');
        Schema::dropIfExists('kpi_user');
        Schema::dropIfExists('kpi_review_requests');
        Schema::dropIfExists('kpi_user_komponen');
        Schema::dropIfExists('kpi_user_task');
    }
};
