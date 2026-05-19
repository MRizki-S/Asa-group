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
        Schema::table('customer_booking', function (Blueprint $table) {
            $table->enum('source', ['internal', 'agent'])
                ->default('internal')
                ->after('sales_id');

            $table->foreignId('agent_id')
                ->nullable()
                ->after('source')
                ->constrained('master_agent')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer_booking', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropColumn(['source', 'agent_id']);
        });
    }
};
