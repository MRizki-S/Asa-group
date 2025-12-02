<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perumahaan', function (Blueprint $table) {
            // menambah company_id ke perumahaan
            $table->foreignId('company_id')->nullable()->after('slug')->constrained('companies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('perumahaan', function (Blueprint $table) {
            if (Schema::hasColumn('perumahaan', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
        });
    }
};
