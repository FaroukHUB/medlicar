<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'gallery' => 'array',
        'features' => 'array',
        'has_ac' => 'boolean',
        'is_active' => 'boolean',
        'price_per_day' => 'decimal:2',
        'price_per_week' => 'decimal:2',
        'price_per_month' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    public function documents()
    {
        return $this->hasMany(VehicleDocument::class);
    }

    /**
     * Disponible sur une période ? (anti double-réservation)
     * Vrai si aucune réservation active ni blocage ne chevauche [start, end].
     */
    public function isAvailableBetween($start, $end): bool
    {
        $overlapBooking = $this->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'active', 'returning'])
            ->where('start_date', '<', $end)
            ->where('end_date', '>', $start)
            ->exists();

        $overlapBlock = $this->availabilities()
            ->where('start_date', '<', $end)
            ->where('end_date', '>', $start)
            ->exists();

        return ! $overlapBooking && ! $overlapBlock;
    }
}
