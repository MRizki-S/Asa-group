<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        // Sales 1
        $sales1 = User::updateOrCreate(
            ['username' => 'sales1'],
            [
                'username'       => 'sales1',
                'no_hp'          => '6285238617670',
                'password'       => Hash::make('12345678'),
                'slug'           => Str::slug('sales1'),
                'type'           => 'karyawan',
                'perumahaan_id'  => null,
                'is_global'      => true,
            ]
        );
        $sales1->syncRoles(['sales']); // langsung sambungkan ke role sales

        // Sales 2
        $sales2 = User::updateOrCreate(
            ['username' => 'sales2'],
            [
                'username'       => 'sales2',
                'no_hp'          => '6289515806753',
                'password'       => Hash::make('12345678'),
                'slug'           => Str::slug('sales2'),
                'type'           => 'karyawan',
                'perumahaan_id'  => null,
                'is_global'      => true,
            ]
        );
        $sales2->syncRoles(['sales']);
    }
}
