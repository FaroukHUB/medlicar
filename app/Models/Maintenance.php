<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'next_due_at' => 'date',
        'cost' => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
