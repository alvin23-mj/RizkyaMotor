<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = [
        'brand',
        'model',
        'year',
        'price',
        'mileage',
        'transmission',
        'fuel',
        'engine',
        'color',
        'image',
        'description',
        'condition',
        'status',
        'contact_phone',
        'is_active',
        'features',
        'seating_capacity',
        'car_type',
        'is_terlaris',
        'is_unggulan'
    ];

    protected $casts = [
        'features' => 'array',
        'is_terlaris' => 'boolean',
        'is_unggulan' => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
