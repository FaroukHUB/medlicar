<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
