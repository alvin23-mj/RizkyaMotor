<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'car_id',
        'name',
        'phone',
        'email',
        'meeting_date',
        'meeting_time',
        'type',
        'car_brand',
        'car_model',
        'car_year',
        'car_price',
        'notes',
        'status'
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
