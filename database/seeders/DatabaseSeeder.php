<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'admin rozkya',
            'email' => 'admin@rozkya.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'pengguna rozkya',
            'email' => 'user@rozkya.com',
            'password' => bcrypt('password'),
            'role' => 'pengguna',
        ]);

        \App\Models\Car::create([
            'brand' => 'Honda',
            'model' => 'Civic hatchback',
            'year' => 2021,
            'price' => 385000000,
            'mileage' => 24000,
            'transmission' => 'otomatis',
            'fuel' => 'bensin',
            'engine' => '1500cc turbo',
            'color' => 'abu-abu metalik',
            'image' => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&q=80&w=800',
            'description' => 'kondisi sangat terawat, milik pribadi, service record dealer honda resmi, interior mulus wangi, pajak panjang.',
            'condition' => 'bekas',
            'status' => 'tersedia',
            'contact_phone' => '081234567890'
        ]);

        \App\Models\Car::create([
            'brand' => 'Toyota',
            'model' => 'Raize GR sport',
            'year' => 2022,
            'price' => 225000000,
            'mileage' => 15000,
            'transmission' => 'otomatis',
            'fuel' => 'bensin',
            'engine' => '1000cc turbo',
            'color' => 'merah-hitam',
            'image' => 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&q=80&w=800',
            'description' => 'raize gr sport tipe tertinggi, body mulus full original, asuransi all risk aktif, ban tebal.',
            'condition' => 'bekas',
            'status' => 'tersedia',
            'contact_phone' => '081234567890'
        ]);

        \App\Models\Car::create([
            'brand' => 'BMW',
            'model' => '320i sport LCI',
            'year' => 2018,
            'price' => 495000000,
            'mileage' => 42000,
            'transmission' => 'otomatis',
            'fuel' => 'bensin',
            'engine' => '2000cc twinpower turbo',
            'color' => 'hitam metalik',
            'image' => 'https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&q=80&w=800',
            'description' => 'nik 2018, bodi mulus tanpa cacat, kaki-kaki sunyi senyap, mesin kering, interior kulit hitam rapi, siap pakai luar kota.',
            'condition' => 'bekas',
            'status' => 'tersedia',
            'contact_phone' => '081234567890'
        ]);

        \App\Models\Car::create([
            'brand' => 'Mazda',
            'model' => 'CX-5 elite',
            'year' => 2019,
            'price' => 375000000,
            'mileage' => 38000,
            'transmission' => 'otomatis',
            'fuel' => 'bensin',
            'engine' => '2500cc skyactiv-g',
            'color' => 'merah kristal',
            'image' => 'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&q=80&w=800',
            'description' => 'mazda cx-5 seri elite teratas, fitur radar safety i-activsense aktif semua, bose audio system, jok kulit elektrik.',
            'condition' => 'bekas',
            'status' => 'tersedia',
            'contact_phone' => '081234567890'
        ]);

        \App\Models\Car::create([
            'brand' => 'Wuling',
            'model' => 'Air EV long range',
            'year' => 2023,
            'price' => 185000000,
            'mileage' => 8000,
            'transmission' => 'otomatis',
            'fuel' => 'listrik',
            'engine' => 'baterai 26.7 kWh',
            'color' => 'biru muda',
            'image' => 'https://images.unsplash.com/photo-1563720223185-11003d516935?auto=format&fit=crop&q=80&w=800',
            'description' => 'mobil listrik perkotaan yang super lincah, kilometer rendah sekali baru 8rb, garansi baterai wuling aktif panjang, charger original lengkap.',
            'condition' => 'bekas',
            'status' => 'tersedia',
            'contact_phone' => '081234567890'
        ]);

        \App\Models\Car::create([
            'brand' => 'Hyundai',
            'model' => 'Creta prime',
            'year' => 2022,
            'price' => 295000000,
            'mileage' => 19000,
            'transmission' => 'otomatis',
            'fuel' => 'bensin',
            'engine' => '1500cc smartstream',
            'color' => 'putih',
            'image' => 'https://images.unsplash.com/photo-1619767886558-efdc259cde1a?auto=format&fit=crop&q=80&w=800',
            'description' => 'creta prime panoramic sunroof, bose sound, blind spot monitoring, terawat mulus sekali tangan pertama dari baru.',
            'condition' => 'bekas',
            'status' => 'tersedia',
            'contact_phone' => '081234567890'
        ]);
    }
}
