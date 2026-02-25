<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Database\Seeders\master\MasterBankSeeder;
use Database\Seeders\Roles\StaffKprRoleSeeder;
use Database\Seeders\Master\AkunKeuanganSeeder;
use Database\Seeders\Roles\MarketingRoleSeeder;
use Database\Seeders\Roles\SuperadminRoleSeeder;
use Database\Seeders\master\MasterKprDokumenSeeder;
use Database\Seeders\Roles\ProjectManagerRoleSeeder;
use Database\Seeders\Roles\StaffAdminUmumRoleSeeder;
use Database\Seeders\Roles\StaffAkuntansiRoleSeeder;
use Database\Seeders\users\UsersKeuanganSystemSeeder;
use Database\Seeders\users\UsersMarketingSystemSeeder;
use Database\Seeders\Master\KategoriAkunKeuanganSeeder;
use Database\Seeders\Permissions\EtalasePermissionSeeder;
use Database\Seeders\Permissions\KeuanganPermissionPart1;
use Database\Seeders\Permissions\MarketingPermissionSeeder;
use Database\Seeders\Roles\ManagerDukunganLayananRoleSeeder;
use Database\Seeders\Permissions\SuperadminMenuPermissionSeeder;
use Database\Seeders\Master\PerumahaanSeeder as MasterPerumahaanSeeder;
use Database\Seeders\Master\UbsSeeder as MasterUbsSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // PERMISSION (MASTER)
        $this->call([
            // Marketing Sistem
            EtalasePermissionSeeder::class,
            MarketingPermissionSeeder::class,
            SuperadminMenuPermissionSeeder::class,

            // Keuangan Sistem
            KeuanganPermissionPart1::class
        ]);

        // Role >> Assign permission (Saat ini masih selesai di marketing sistem)
        $this->call([
            SuperadminRoleSeeder::class,
            // HUB
            ManagerDukunganLayananRoleSeeder::class,

            // Marketing Akun Role
            ProjectManagerRoleSeeder::class,
            StaffAdminUmumRoleSeeder::class,
            StaffKprRoleSeeder::class,
            MarketingRoleSeeder::class,

            // Keuangan
            StaffAkuntansiRoleSeeder::class
        ]);

        // 3. Master Data
        $this->call([
            MasterUbsSeeder::class,

            // Master Data Marketing
            MasterPerumahaanSeeder::class,
            MasterBankSeeder::class,
            MasterKprDokumenSeeder::class,

            // Master Data Keuangan
            KategoriAkunKeuanganSeeder::class,
            AkunKeuanganSeeder::class,
        ]);

        // 4. Users
        $this->call([
            // User Marketing Sistem
            UsersMarketingSystemSeeder::class,

            // User Keuangan Sistem 
            UsersKeuanganSystemSeeder::class
        ]);
    }
}
