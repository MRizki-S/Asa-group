<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $karyawans = DB::table('users')
            ->where('type', 'karyawan')
            ->leftJoin('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                     ->where('model_has_roles.model_type', '=', 'App\Models\User');
            })
            ->select('users.id', 'users.nama_lengkap', 'users.no_hp', 'users.perumahaan_id', 'model_has_roles.role_id')
            ->get();

        foreach ($karyawans as $k) {
            DB::table('karyawan')->updateOrInsert(
                ['id' => $k->id],
                [
                    'nama' => $k->nama_lengkap ?? 'Karyawan ' . $k->id,
                    'no_hp' => $k->no_hp ?? '',
                    'role_id' => $k->role_id,
                    'ubs_id' => $k->perumahaan_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('users')->where('id', $k->id)->update([
                'karyawan_id' => $k->id,
            ]);
        }
    }
}
