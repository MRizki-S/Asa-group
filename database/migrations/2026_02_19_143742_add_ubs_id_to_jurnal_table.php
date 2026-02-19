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
        Schema::table('jurnal', function (Blueprint $table) {
            $table->foreignId('ubs_id')
                ->nullable()
                ->after('nomor_jurnal')
                ->constrained('ubs')
                ->nullOnDelete();

            $table->index('ubs_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurnal', function (Blueprint $table) {
            $table->dropForeign(['ubs_id']);
            $table->dropColumn('ubs_id');
        });
    }
};
