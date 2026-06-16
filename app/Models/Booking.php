<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'advance_paid_at' => 'datetime',
        'advance_expires_at' => 'datetime',
        'contract_signed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'selected_options' => 'array',
        'photos_before' => 'array',
        'photos_after' => 'array',
    ];

    public const STATUSES = [
        'pending', 'expired', 'confirmed', 'active',
        'returning', 'completed', 'cancelled', 'dispute',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    /** Génère une référence unique du type MED-2026-AB12CD. */
    public static function generateReference(): string
    {
        return 'MED-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }
}
