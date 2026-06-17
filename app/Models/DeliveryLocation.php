<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryLocation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_free' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    /** Frais effectifs (0 si gratuit). */
    public function fee(): float
    {
        return $this->is_free ? 0.0 : (float) $this->price;
    }
}
