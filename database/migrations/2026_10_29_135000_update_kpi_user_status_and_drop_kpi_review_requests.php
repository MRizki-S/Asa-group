<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ubah enum status pada tabel kpi_user agar mendukung status alur baru
        DB::statement("ALTER TABLE `kpi_user` MODIFY COLUMN `status` ENUM('draft', 'menunggu_review', 'revisi', 'final') NOT NULL DEFAULT 'draft'");

        // 2. Tambah kolom catatan_revisi, finalized_by, dan finalized_at di tabel kpi_user
        Schema::table('kpi_user', function (Blueprint $table) {
            if (!Schema::hasColumn('kpi_user', 'catatan_revisi')) {
                $table->text('catatan_revisi')->nullable()->after('status');
            }
            if (!Schema::hasColumn('kpi_user', 'finalized_by')) {
                $table->foreignId('finalized_by')->nullable()->after('catatan_revisi')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('kpi_user', 'finalized_at')) {
                $table->timestamp('finalized_at')->nullable()->after('finalized_by');
            }
        });

        // 3. Migrasi data pending review dari kpi_review_requests ke status 'menunggu_review'
        if (Schema::hasTable('kpi_review_requests')) {
            DB::statement("
                UPDATE `kpi_user`
                SET `status` = 'menunggu_review'
                WHERE `status` = 'draft'
                AND `id` IN (
                    SELECT `kpi_user_id` FROM `kpi_review_requests` WHERE `direspon_pada` IS NULL
                )
            ");

            // 4. Drop tabel kpi_review_requests karena alur sudah terpadu langsung di kpi_user
            Schema::dropIfExists('kpi_review_requests');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Buat kembali tabel kpi_review_requests
        if (!Schema::hasTable('kpi_review_requests')) {
            Schema::create('kpi_review_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kpi_user_id')->constrained('kpi_user')->onDelete('cascade');
                $table->timestamp('direspon_pada')->nullable();
                $table->timestamps();
            });
        }

        // 2. Hapus kolom tambahan di kpi_user
        Schema::table('kpi_user', function (Blueprint $table) {
            if (Schema::hasColumn('kpi_user', 'finalized_by')) {
                $table->dropForeign(['finalized_by']);
                $table->dropColumn('finalized_by');
            }
            if (Schema::hasColumn('kpi_user', 'catatan_revisi')) {
                $table->dropColumn('catatan_revisi');
            }
            if (Schema::hasColumn('kpi_user', 'finalized_at')) {
                $table->dropColumn('finalized_at');
            }
        });

        // 3. Kembalikan enum status di kpi_user
        DB::statement("UPDATE `kpi_user` SET `status` = 'draft' WHERE `status` IN ('menunggu_review', 'revisi')");
        DB::statement("ALTER TABLE `kpi_user` MODIFY COLUMN `status` ENUM('draft', 'final') NOT NULL DEFAULT 'draft'");
    }
};

