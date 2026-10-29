<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaptainRating extends Model
{
    protected $fillable = [
        'order_id',
        'customer_id',
        'captain_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function captain()
    {
        return $this->belongsTo(User::class, 'captain_id');
    }
}
