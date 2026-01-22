<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Database\Seeders\master\MasterBankSeeder;
use Database\Seeders\Roles\StaffKprRoleSeeder;
use Database\Seeders\Roles\MarketingRoleSeeder;
use Database\Seeders\Roles\SuperadminRoleSeeder;
use Database\Seeders\master\MasterKprDokumenSeeder;
use Database\Seeders\Roles\ProjectManagerRoleSeeder;
use Database\Seeders\Roles\StaffAdminUmumRoleSeeder;
use Database\Seeders\users\UsersMarketingSystemSeeder;
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

            ManagerDukunganLayananRoleSeeder::class,
            ProjectManagerRoleSeeder::class,
            StaffAdminUmumRoleSeeder::class,
            StaffKprRoleSeeder::class,
            MarketingRoleSeeder::class,
        ]);

        // 3. Master Data
        $this->call([
            MasterPerumahaanSeeder::class,
            MasterBankSeeder::class,
            MasterKprDokumenSeeder::class,
        ]);

        // 4. Users
        $this->call([
            UsersMarketingSystemSeeder::class,
        ]);
    }
}
