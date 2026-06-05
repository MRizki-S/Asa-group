
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
        Schema::create('stock_gudang', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barang_id')
                ->constrained('master_barang')
                ->cascadeOnDelete();

            $table->enum('stock_type', ['HUB', 'UBS']);

            $table->foreignId('ubs_id')
                ->nullable()
                ->constrained('ubs')
                ->nullOnDelete();

            $table->decimal('jumlah_stock', 18, 2)->default(0);

            $table->decimal('minimal_stock', 18, 2)->nullable();

            $table->timestamps();

            $table->unique(['barang_id', 'stock_type', 'ubs_id']);

            $table->index('barang_id');
            $table->index('ubs_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_gudang');
    }
};
