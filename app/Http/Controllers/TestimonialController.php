<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Testimonial;
use Inertia\Inertia;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::orderBy('created_at', 'desc')->get();
        return Inertia::render('Testimonials/Index', [
            'testimonials' => $testimonials
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'rating' => 'required|integer|min:1|max:5',
            'message' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('avatar')) {
            $imagePath = $request->file('avatar')->store('testimonials', 'public');
            $validated['avatar'] = $imagePath;
        }

        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        Testimonial::create($validated);

        return redirect()->back()->with('success', 'Testimoni berhasil ditambahkan.');
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'rating' => 'required|integer|min:1|max:5',
            'message' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('avatar')) {
            $imagePath = $request->file('avatar')->store('testimonials', 'public');
            $validated['avatar'] = $imagePath;
        }

        if (isset($validated['is_active'])) {
            $validated['is_active'] = (bool) $validated['is_active'];
        }

        $testimonial->update($validated);

        return redirect()->back()->with('success', 'Testimoni berhasil diperbarui.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();
        return redirect()->back()->with('success', 'Testimoni berhasil dihapus.');
    }
}
