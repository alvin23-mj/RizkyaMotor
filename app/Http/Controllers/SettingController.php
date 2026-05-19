<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key');
        return Inertia::render('Settings/Index', [
            'settings' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'facebook_link' => 'nullable|string',
            'instagram_link' => 'nullable|string',
            'tiktok_link' => 'nullable|string',
            'whatsapp_number' => 'nullable|string',
            'coordinates' => 'nullable|string',
            'footer_about' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        foreach ($request->except('logo', '_method') as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        if ($request->hasFile('logo')) {
            $imagePath = $request->file('logo')->store('brands', 'public');
            Setting::updateOrCreate(['key' => 'logo_path'], ['value' => $imagePath]);
        }

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
