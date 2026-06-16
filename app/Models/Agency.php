<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * L'agence unique (single-tenant). Une seule ligne en base.
 */
class Agency extends Model
{
    protected $table = 'agency';

    protected $guarded = [];

    protected $casts = [
        'payment_methods' => 'array',
        'vat_rate' => 'decimal:2',
        'default_advance_percent' => 'decimal:2',
        'delivery_enabled' => 'boolean',
        'transfer_enabled' => 'boolean',
    ];

    /** Récupère (et met en cache) l'agence unique. */
    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], ['name' => 'Medlicar']);
    }
}
