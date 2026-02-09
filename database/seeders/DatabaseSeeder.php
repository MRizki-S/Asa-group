<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Database\Seeders\Master\AkunKeuanganSeeder;
use Illuminate\Database\Seeder;
use Database\Seeders\master\MasterBankSeeder;
use Database\Seeders\Roles\StaffKprRoleSeeder;
use Database\Seeders\Roles\MarketingRoleSeeder;
use Database\Seeders\Roles\SuperadminRoleSeeder;
use Database\Seeders\master\MasterKprDokumenSeeder;
use Database\Seeders\Roles\ProjectManagerRoleSeeder;
use Database\Seeders\Roles\StaffAdminUmumRoleSeeder;
use Database\Seeders\users\UsersMarketingSystemSeeder;
use Database\Seeders\Master\KategoriAkunKeuanganSeeder;
use Database\Seeders\Permissions\EtalasePermissionSeeder;
use Database\Seeders\Permissions\MarketingPermissionSeeder;
use Database\Seeders\Roles\ManagerDukunganLayananRoleSeeder;
use Database\Seeders\Permissions\SuperadminMenuPermissionSeeder;
use Database\Seeders\Master\PerumahaanSeeder as MasterPerumahaanSeeder;

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
        ]);

        // Role >> Assign permission (Saat ini masih selesai di marketing sistem)
        $this->call([
            SuperadminRoleSeeder::class,

            // Marketing Akun Role
            ManagerDukunganLayananRoleSeeder::class,
            ProjectManagerRoleSeeder::class,
            StaffAdminUmumRoleSeeder::class,
            StaffKprRoleSeeder::class,
            MarketingRoleSeeder::class,
        ]);

        // 3. Master Data
        $this->call([
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
        ]);
    }
}
