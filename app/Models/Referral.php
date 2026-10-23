<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $guarded = [];

    protected $casts = ['reward_amount' => 'decimal:2'];

    public function referrer()
    {
        return $this->belongsTo(Customer::class, 'referrer_id');
    }

    public function referred()
    {
        return $this->belongsTo(Customer::class, 'referred_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
