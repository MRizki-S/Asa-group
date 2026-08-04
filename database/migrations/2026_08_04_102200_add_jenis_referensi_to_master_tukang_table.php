<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('master_tukang') && !Schema::hasColumn('master_tukang', 'jenis_referensi')) {
            Schema::table('master_tukang', function (Blueprint $table) {
                $table->enum('jenis_referensi', [
                    'perumahan',
                    'mangoon',
                ])->after('nama_tukang')->default('perumahan');
                $table->index('jenis_referensi');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('master_tukang') && Schema::hasColumn('master_tukang', 'jenis_referensi')) {
            Schema::table('master_tukang', function (Blueprint $table) {
                $table->dropIndex(['jenis_referensi']);
                $table->dropColumn('jenis_referensi');
            });
        }
    }
};
