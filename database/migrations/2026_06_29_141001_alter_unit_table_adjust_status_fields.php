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
        // Add status_pembangunan to unit table
        Schema::table('unit', function (Blueprint $table) {
            $table->enum('status_pembangunan', ['belum dibangun', 'diajukan', 'dalam pembangunan', 'selesai dibangun'])
                  ->default('belum dibangun')
                  ->after('status_unit');
        });

        // Set initial status_pembangunan based on status_unit
        DB::table('unit')
            ->where('status_unit', 'under_construction')
            ->update(['status_pembangunan' => 'dalam pembangunan']);

        // Set status_pembangunan to 'diajukan' for units that have pending requests
        DB::table('unit')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('pembangunan_unit')
                    ->join('pengajuan_pembangunan_unit', 'pengajuan_pembangunan_unit.pembangunan_unit_id', '=', 'pembangunan_unit.id')
                    ->whereColumn('pembangunan_unit.unit_id', 'unit.id')
                    ->where('pengajuan_pembangunan_unit.status_pengajuan', 'pending');
            })
            ->update(['status_pembangunan' => 'diajukan']);

        // Set status_pembangunan to 'dalam pembangunan' for units that are being built
        DB::table('unit')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('pembangunan_unit')
                    ->join('pengajuan_pembangunan_unit', 'pengajuan_pembangunan_unit.pembangunan_unit_id', '=', 'pembangunan_unit.id')
                    ->whereColumn('pembangunan_unit.unit_id', 'unit.id')
                    ->where('pengajuan_pembangunan_unit.status_pengajuan', 'dibangun');
            })
            ->update(['status_pembangunan' => 'dalam pembangunan']);

        // Reset status_unit from under_construction to available
        DB::table('unit')
            ->where('status_unit', 'under_construction')
            ->update(['status_unit' => 'available']);

        // Alter status_unit enum to remove under_construction
        DB::statement("ALTER TABLE unit MODIFY COLUMN status_unit ENUM('available', 'booked', 'sold') NOT NULL DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add under_construction to status_unit enum
        DB::statement("ALTER TABLE unit MODIFY COLUMN status_unit ENUM('available', 'booked', 'sold', 'under_construction') NOT NULL DEFAULT 'available'");

        // Revert status_unit of units that are in construction back to under_construction
        DB::table('unit')
            ->whereIn('status_pembangunan', ['diajukan', 'dalam pembangunan'])
            ->update(['status_unit' => 'under_construction']);

        // Drop status_pembangunan column
        Schema::table('unit', function (Blueprint $table) {
            $table->dropColumn('status_pembangunan');
        });
    }
};

