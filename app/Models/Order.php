<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 
        'assigned_captain_id',
        'customer_name', 
        'customer_phone', 
        'customer_email',
        'customer_address', 
        'latitude',
        'longitude',
        'total_amount', 
        'captain_commission',
        'payment_method', 
        'transfer_number',
        'receipt_image', 
        'notes', 
        'status', 
        'is_paid', 
        'paid_at'
    ];
    
    protected $casts = [
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'total_amount' => 'float',
        'captain_commission' => 'float',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function captain()
    {
        return $this->belongsTo(User::class, 'assigned_captain_id');
    }

    public function captainRating()
    {
        return $this->hasOne(CaptainRating::class);
    }
    
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }
}
