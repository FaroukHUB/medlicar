<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function expenseCategory()
    {
        return $this->belongsTo(ExpenseCategory::class);
    }
}
