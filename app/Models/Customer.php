<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'birth_date' => 'date',
        'license_expiry' => 'date',
        'documents_verified' => 'boolean',
        'is_blacklisted' => 'boolean',
        'rating' => 'decimal:2',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
