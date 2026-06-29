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
        Schema::create('devisi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_devisi');
            $table->timestamps();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('devisi_id')->nullable()->after('guard_name')->constrained('devisi')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'devisi_id')) {
                $table->dropForeign(['devisi_id']);
                $table->dropColumn('devisi_id');
            }
        });

        Schema::dropIfExists('devisi');
    }
};
