<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'facebook_link', 'value' => 'https://facebook.com'],
            ['key' => 'instagram_link', 'value' => 'https://instagram.com'],
            ['key' => 'tiktok_link', 'value' => 'https://tiktok.com'],
            ['key' => 'youtube_link', 'value' => 'https://youtube.com'],
            ['key' => 'whatsapp_number', 'value' => '081234567890'],
            ['key' => 'coordinates', 'value' => '-6.200000, 106.816666'],
            ['key' => 'footer_about', 'value' => 'Rizkya Motor adalah dealer mobil bekas terpercaya.'],
            ['key' => 'logo_path', 'value' => 'brands/default_logo.png'],
            ['key' => 'address', 'value' => 'Jl. Otomotif Raya No. 123 Jakarta Selatan, DKI Jakarta 12345'],
            ['key' => 'email', 'value' => 'info@rizkya-motor.com'],
            ['key' => 'phone', 'value' => '+62 812-3456-7890'],
            ['key' => 'opening_hours_weekday', 'value' => '08:00 - 17:00'],
            ['key' => 'opening_hours_saturday', 'value' => '09:00 - 15:00'],
            ['key' => 'opening_hours_sunday', 'value' => 'Tutup'],
            ['key' => 'instagram_active', 'value' => '1'],
            ['key' => 'facebook_active', 'value' => '1'],
            ['key' => 'tiktok_active', 'value' => '1'],
            ['key' => 'youtube_active', 'value' => '1'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], ['value' => $setting['value']]);
        }
    }
}
