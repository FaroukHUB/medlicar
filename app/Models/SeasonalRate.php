<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalRate extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price_per_day' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
