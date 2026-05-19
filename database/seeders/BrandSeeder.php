<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = \App\Models\Car::distinct()->pluck('brand');
        foreach ($brands as $brand) {
            if ($brand) {
                \App\Models\Brand::firstOrCreate(['name' => $brand]);
            }
        }
    }
}
