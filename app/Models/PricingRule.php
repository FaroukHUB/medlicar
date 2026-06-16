<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingRule extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'modifier_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
