<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CarController extends Controller
{
    public function index(Request $request)
    {
        $query = Car::query(); // Fetch all cars, frontend will filter catalog by status 'tersedia' or other fields

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->input('brand'));
        }

        if ($request->filled('transmission')) {
            $query->where('transmission', $request->input('transmission'));
        }

        if ($request->filled('fuel')) {
            $query->where('fuel', $request->input('fuel'));
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->input('price_max'));
        }

        $cars = $query->orderBy('created_at', 'desc')->get();
        
        $brands = \App\Models\Brand::orderBy('name', 'asc')->get();
        
        $bookings = Booking::with('car')->orderBy('meeting_date', 'asc')->orderBy('meeting_time', 'asc')->get();
        
        $settings = \App\Models\Setting::pluck('value', 'key');
        
        $testimonials = \App\Models\Testimonial::where('is_active', true)->orderBy('created_at', 'desc')->get();

        return Inertia::render('Welcome', [
            'cars' => $cars,
            'brands' => $brands,
            'bookings' => $bookings,
            'settings' => $settings,
            'testimonials' => $testimonials,
            'partners' => \App\Models\Partner::orderBy('name', 'asc')->get(),
            'filters' => $request->only(['search', 'brand', 'transmission', 'fuel', 'price_max']),
        ]);
    }

    public function show(Car $car)
    {
        $otherCars = Car::where('id', '!=', $car->id)
            ->where('status', 'tersedia')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        $comments = \App\Models\Comment::where('car_id', $car->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Cars/Show', [
            'car' => $car,
            'otherCars' => $otherCars,
            'comments' => $comments,
        ]);
    }

    public function storeComment(Request $request, Car $car)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $validated['car_id'] = $car->id;

        \App\Models\Comment::create($validated);

        return redirect()->back()->with('success', 'Komentar berhasil dikirim.');
    }

    public function replyComment(Request $request, \App\Models\Comment $comment)
    {
        $validated = $request->validate([
            'reply' => 'required|string',
        ]);

        $comment->update($validated);

        return redirect()->back()->with('success', 'Balasan berhasil dikirim.');
    }

    public function storeBooking(Request $request)
    {
        $validated = $request->validate([
            'car_id' => 'nullable|exists:cars,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'meeting_date' => 'required|date|after_or_equal:today',
            'meeting_time' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $validated['type'] = 'pembelian';

        Booking::create($validated);

        return redirect()->back()->with('success', 'jadwal pertemuan berhasil dibuat.');
    }

    public function storeSellCar(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'car_brand' => 'required|string|max:255',
            'car_model' => 'required|string|max:255',
            'car_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'car_price' => 'required|numeric|min:0',
            'meeting_date' => 'required|date|after_or_equal:today',
            'meeting_time' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $validated['type'] = 'penjualan';

        Booking::create($validated);

        return redirect()->back()->with('success', 'pengajuan jual mobil dan jadwal temu berhasil dibuat.');
    }

    public function storeCar(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'price' => 'required|numeric|min:0',
            'mileage' => 'required|integer|min:0',
            'transmission' => 'required|string|in:manual,otomatis',
            'fuel' => 'required|string|in:bensin,listrik',
            'engine' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'images' => 'required|array|min:4',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'description' => 'nullable|string',
            'condition' => 'required|string|in:baru,bekas',
            'status' => 'required|string|in:tersedia,terjual,perbaikan',
            'contact_phone' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
            'features' => 'nullable|array',
            'seating_capacity' => 'nullable|integer|min:1',
            'car_type' => 'nullable|string|max:255',
            'is_terlaris' => 'nullable|boolean',
            'is_unggulan' => 'nullable|boolean',
        ], [
            'images.min' => 'Anda harus mengunggah minimal 4 gambar mobil.',
            'images.*.image' => 'File harus berupa gambar.',
            'images.*.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp.',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('cars', 'public');
                $imagePaths[] = '/storage/' . $path;
            }
        }
        $validated['image'] = json_encode($imagePaths);

        Car::create($validated);

        return redirect()->back()->with('success', 'Mobil berhasil ditambahkan.');
    }

    public function createCar()
    {
        return Inertia::render('Cars/Create', [
            'brands' => \App\Models\Brand::pluck('name')
        ]);
    }

    public function editCar(Car $car)
    {
        return Inertia::render('Cars/Edit', [
            'car' => $car,
            'brands' => \App\Models\Brand::pluck('name')
        ]);
    }

    public function updateCar(Request $request, Car $car)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'price' => 'required|numeric|min:0',
            'mileage' => 'required|integer|min:0',
            'transmission' => 'required|string|in:manual,otomatis',
            'fuel' => 'required|string|in:bensin,listrik',
            'engine' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'description' => 'nullable|string',
            'condition' => 'required|string|in:baru,bekas',
            'status' => 'required|string|in:tersedia,terjual,perbaikan',
            'contact_phone' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
            'features' => 'nullable|array',
            'seating_capacity' => 'nullable|integer|min:1',
            'car_type' => 'nullable|string|max:255',
            'is_terlaris' => 'nullable|boolean',
            'is_unggulan' => 'nullable|boolean',
        ]);

        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('cars', 'public');
                $imagePaths[] = '/storage/' . $path;
            }
            $validated['image'] = json_encode($imagePaths);
        } else {
            $validated['image'] = $car->image;
        }

        $car->update($validated);

        return redirect()->back()->with('success', 'Mobil berhasil diperbarui.');
    }

    public function destroyCar(Car $car)
    {
        $car->delete();
        return redirect()->back()->with('success', 'Mobil berhasil dihapus.');
    }

    public function updateBookingStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:menunggu,disetujui,selesai,dibatalkan',
        ]);

        $booking->update($validated);

        return redirect()->back()->with('success', 'Status jadwal berhasil diperbarui.');
    }

    public function updateBooking(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'meeting_date' => 'required|date',
            'meeting_time' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $booking->update($validated);

        return redirect()->back()->with('success', 'Jadwal temu berhasil diperbarui.');
    }
}
