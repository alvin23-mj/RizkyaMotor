<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\CarController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\PartnerController;

Route::get('/', [CarController::class, 'index'])->name('cars.index');
Route::post('/bookings', [CarController::class, 'storeBooking'])->name('bookings.store');
Route::post('/sell-car', [CarController::class, 'storeSellCar'])->name('sell-car.store');

// Admin actions
Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/cars', [CarController::class, 'storeCar'])->name('cars.store');
    Route::get('/cars/create', [CarController::class, 'createCar'])->name('cars.create');
    Route::get('/cars/{car}/edit', [CarController::class, 'editCar'])->name('cars.edit');
    Route::put('/cars/{car}', [CarController::class, 'updateCar'])->name('cars.update');
    Route::delete('/cars/{car}', [CarController::class, 'destroyCar'])->name('cars.destroy');
    Route::patch('/bookings/{booking}/status', [CarController::class, 'updateBookingStatus'])->name('bookings.updateStatus');
    Route::put('/bookings/{booking}', [CarController::class, 'updateBooking'])->name('bookings.update');
    Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
    Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
    Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/testimonials', [TestimonialController::class, 'index'])->name('testimonials.index');
    Route::post('/testimonials', [TestimonialController::class, 'store'])->name('testimonials.store');
    Route::put('/testimonials/{testimonial}', [TestimonialController::class, 'update'])->name('testimonials.update');
    Route::delete('/testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->name('testimonials.destroy');
    Route::get('/partners', [PartnerController::class, 'index'])->name('partners.index');
    Route::post('/partners', [PartnerController::class, 'store'])->name('partners.store');
    Route::put('/partners/{partner}', [PartnerController::class, 'update'])->name('partners.update');
    Route::delete('/partners/{partner}', [PartnerController::class, 'destroy'])->name('partners.destroy');
    Route::patch('/comments/{comment}/reply', [CarController::class, 'replyComment'])->name('comments.reply');
});

Route::get('/cars/{car}', [CarController::class, 'show'])->name('cars.show');
Route::post('/cars/{car}/comments', [CarController::class, 'storeComment'])->name('cars.comments.store');

Route::get('/dashboard', function () {
    return redirect('/');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
