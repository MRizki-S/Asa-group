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
        // Update existing 'selesai dengan catatan' to 'selesai'
        DB::table('pembangunan_proyek')
            ->where('status_pembangunan', 'selesai dengan catatan')
            ->update(['status_pembangunan' => 'selesai']);

        DB::table('pembangunan_kawasan')
            ->where('status_pembangunan', 'selesai dengan catatan')
            ->update(['status_pembangunan' => 'selesai']);

        // Alter status_pembangunan enum
        DB::statement("ALTER TABLE pembangunan_proyek MODIFY COLUMN status_pembangunan ENUM('pending', 'proses', 'selesai') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE pembangunan_kawasan MODIFY COLUMN status_pembangunan ENUM('pending', 'proses', 'selesai') NOT NULL DEFAULT 'pending'");

        // Drop catatan column
        Schema::table('pembangunan_proyek', function (Blueprint $table) {
            $table->dropColumn('catatan');
        });

        Schema::table('pembangunan_kawasan', function (Blueprint $table) {
            $table->dropColumn('catatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add catatan column
        Schema::table('pembangunan_proyek', function (Blueprint $table) {
            $table->text('catatan')->nullable()->after('status_pembangunan');
        });

        Schema::table('pembangunan_kawasan', function (Blueprint $table) {
            $table->text('catatan')->nullable()->after('status_pembangunan');
        });

        // Re-add 'selesai dengan catatan' enum
        DB::statement("ALTER TABLE pembangunan_proyek MODIFY COLUMN status_pembangunan ENUM('pending', 'proses', 'selesai', 'selesai dengan catatan') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE pembangunan_kawasan MODIFY COLUMN status_pembangunan ENUM('pending', 'proses', 'selesai', 'selesai dengan catatan') NOT NULL DEFAULT 'pending'");
    }
};
