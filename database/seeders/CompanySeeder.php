<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    public function run()
    {
        $companies = [
            [
                'nama'      => 'PT Alvin Bhakti Mandiri',
                'slug'      => Str::slug('PT Alvin Bhakti Mandiri'),
                'tipe'      => 'pusat',
                'is_global' => true,
            ],
            [
                'nama'      => 'Mangoon Internal',
                'slug'      => Str::slug('Mangoon Internal'),
                'tipe'      => 'kontraktor',
                'is_global' => false,
            ],
            [
                'nama'      => 'Mangoon Eksternal',
                'slug'      => Str::slug('Mangoon Eksternal'),
                'tipe'      => 'kontraktor',
                'is_global' => false,
            ],
            [
                'nama'      => 'Asa Dreamland',
                'slug'      => Str::slug('Asa Dreamland'),
                'tipe'      => 'perumahaan',
                'is_global' => false,
            ],
            [
                'nama'      => 'Lembah Hijau Residence',
                'slug'      => Str::slug('Lembah Hijau Residence'),
                'tipe'      => 'perumahaan',
                'is_global' => false,
            ],
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }
    }
}
