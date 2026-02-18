<?php

namespace Database\Seeders\permissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KeuanganPermissionPart1 extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        // Reset cache permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [

            // PERIODE
            'keuangan.periode.read',
            'keuangan.periode.create',
            'keuangan.periode.update',
            'keuangan.periode.delete',

            // KATEGORI AKUN
            'keuangan.kategori-akun.read',

            // AKUN KEUANGAN
            'keuangan.akun-keuangan.read',
            'keuangan.akun-keuangan.create',
            'keuangan.akun-keuangan.update',
            'keuangan.akun-keuangan.delete',

            // TRANSAKSI JURNAL
            'keuangan.transaksi-jurnal.read',
            'keuangan.transaksi-jurnal.create',

            // LAPORAN Jurnal
            'keuangan.laporan-jurnal.read',
            // Lapoaran Buku Besar
            'keuangan.buku-besar.read',
            // Laporan Neraca Saldo
            'keuangan.neraca-saldo.read',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
