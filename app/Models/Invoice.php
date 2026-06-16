<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'issued_at' => 'date',
        'due_at' => 'date',
        'subtotal' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
