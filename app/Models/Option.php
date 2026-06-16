<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
