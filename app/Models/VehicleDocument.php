<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleDocument extends Model
{
    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
