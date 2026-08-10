<?php

namespace Database\Seeders\Roles;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Exception;

class StaffAkuntansiRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // Create / ambil role
            $role = Role::firstOrCreate([
                'name' => 'Staff Akuntansi',
                'guard_name' => 'web',
            ]);

            // Permission yang WAJIB ADA
            $permissionNames = [

                // =====================
                // KEUANGAN
                // =====================

                // Periode
                'keuangan.periode.read',

                // Kategori Akun
                'keuangan.kategori-akun.read',

                // Akun Keuangan
                'keuangan.akun-keuangan.read',

                // Transaksi Jurnal
                'keuangan.transaksi-jurnal.read',
                'keuangan.transaksi-jurnal.create',

                // Laporan
                'keuangan.laporan-jurnal.read',
                'keuangan.buku-besar.read',
                'keuangan.neraca-saldo.read',
            ];

            // Ambil permission dari DB
            $permissions = Permission::whereIn('name', $permissionNames)->get();

            // VALIDASI KETAT
            $missingPermissions = collect($permissionNames)
                ->diff($permissions->pluck('name'));

            if ($missingPermissions->isNotEmpty()) {
                throw new Exception(
                    'Seeder Staff Akuntansi GAGAL. Permission belum terdaftar: ' .
                    $missingPermissions->implode(', ')
                );
            }

            // Assign permission ke role
            $role->syncPermissions($permissions);
        });
    }
}
