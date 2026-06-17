<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleTemplate extends Model
{
    protected $guarded = [];

    protected $casts = ['has_ac' => 'boolean', 'is_active' => 'boolean'];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
