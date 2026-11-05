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
        Schema::table('pembangunan_lain_barang_return_fifo', function (Blueprint $table) {
            $table->dropForeign('fk_pl_ret_fifo_usage');
            $table->unsignedBigInteger('fifo_usage_id')->nullable()->change();
            $table->foreign('fifo_usage_id', 'fk_pl_ret_fifo_usage')
                ->references('id')
                ->on('pembangunan_lain_barang_fifo_usage')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembangunan_lain_barang_return_fifo', function (Blueprint $table) {
            $table->dropForeign('fk_pl_ret_fifo_usage');
            $table->unsignedBigInteger('fifo_usage_id')->nullable(false)->change();
            $table->foreign('fifo_usage_id', 'fk_pl_ret_fifo_usage')
                ->references('id')
                ->on('pembangunan_lain_barang_fifo_usage')
                ->cascadeOnDelete();
        });
    }
};

