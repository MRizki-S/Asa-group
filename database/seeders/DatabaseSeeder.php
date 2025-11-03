<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\PerumahaanSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PerumahaanSeeder::class,
            RoleSeeder::class,
            AkunKaryawanSeeder::class,

            // seeder master bank & dokumen kpr
            MasterBankSeeder::class,
            MasterKprDokumenSeeder::class,
        ]);
    }
}
