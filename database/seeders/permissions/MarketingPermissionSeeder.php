<?php

namespace Database\Seeders\Permissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class MarketingPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            // Customer
            'marketing.customer.read',
            'marketing.customer.create',
            'marketing.customer.update',
            'marketing.customer.delete',

            // Pemesanan Unit
            'marketing.pemesanan-unit.read',
            'marketing.pemesanan-unit.create',

            // Kelola Pemesanan
            'marketing.kelola-pemesanan.read',
            'marketing.kelola-pemesanan.tagihan.read',
            'marketing.kelola-pemesanan.pengajuan-pembatalan',
            'marketing.kelola-pemesanan.print-ppjb',
            'marketing.kelola-pemesanan.lihat-berkas',
            'marketing.kelola-pemesanan.update-berkas',
            'marketing.kelola-pemesanan.pengajuan-adendum', // masih belum ada fiturnya

            // Pengajuan
            'marketing.pengajuan-pemesanan.read',
            'marketing.pengajuan-pemesanan.detail',
            'marketing.pengajuan-pemesanan.action',

            'marketing.pengajuan-pembatalan.read',
            'marketing.pengajuan-pembatalan.action',

            // Adendum
            'marketing.adendum.pengajuan-adendum.read',
            'marketing.adendum.pengajuan-adendum.action', // fitur belum selesai
            'marketing.adendum.create', // fitur belum selesai (hanya adendum cara bayar selesai)

            'marketing.adendum.list-adendum.read', // fitur belum selesai
            'marketing.adendum.list-adendum.detail', // fitur belum selesai
            'marketing.adendum.list-adendum.delete', // fiur belum selesai

            // Setting PPJB
            'marketing.setting-ppjb.read',
            'marketing.setting-ppjb.kelola',
            'marketing.setting-ppjb.kelola.pengajuan-perubahaan',
            'marketing.setting-ppjb.kelola.action',
            'marketing.setting-ppjb.kelola.cancel',
            'marketing.setting-ppjb.kelola.nonaktif',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
