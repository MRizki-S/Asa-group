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
        // 1. Create karyawan table
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('no_hp');
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null');
            $table->foreignId('perumahaan_id')->nullable()->constrained('ubs')->onDelete('set null');
            $table->timestamps();
        });

        // 2. Add karyawan_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('karyawan_id')->nullable()->constrained('karyawan')->onDelete('set null');
        });

        // 3. Migrate existing users of type 'karyawan' into karyawan table
        $karyawans = DB::table('users')
            ->where('type', 'karyawan')
            ->leftJoin('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                     ->where('model_has_roles.model_type', '=', 'App\Models\User');
            })
            ->select('users.id', 'users.nama_lengkap', 'users.no_hp', 'users.perumahaan_id', 'model_has_roles.role_id')
            ->get();

        foreach ($karyawans as $k) {
            DB::table('karyawan')->insert([
                'id' => $k->id, // keep ID same to make KPI migration seamless
                'nama' => $k->nama_lengkap ?? 'Karyawan ' . $k->id,
                'no_hp' => $k->no_hp ?? '',
                'role_id' => $k->role_id,
                'perumahaan_id' => $k->perumahaan_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')->where('id', $k->id)->update([
                'karyawan_id' => $k->id,
            ]);
        }

        // 4. Update kpi_user table: drop user_id foreign key, add karyawan_id foreign key
        Schema::table('kpi_user', function (Blueprint $table) {
            // Drop old foreign key constraint
            $table->dropForeign(['user_id']);
        });

        Schema::table('kpi_user', function (Blueprint $table) {
            // Rename column user_id to karyawan_id
            $table->renameColumn('user_id', 'karyawan_id');
        });

        Schema::table('kpi_user', function (Blueprint $table) {
            // Add foreign key constraint to karyawan table
            $table->foreign('karyawan_id')->references('id')->on('karyawan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpi_user', function (Blueprint $table) {
            $table->dropForeign(['karyawan_id']);
        });

        Schema::table('kpi_user', function (Blueprint $table) {
            $table->renameColumn('karyawan_id', 'user_id');
        });

        Schema::table('kpi_user', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['karyawan_id']);
            $table->dropColumn('karyawan_id');
        });

        Schema::dropIfExists('karyawan');
    }
};
