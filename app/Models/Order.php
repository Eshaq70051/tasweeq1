<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 
        'store_id',
        'assigned_captain_id',
        'coupon_id',
        'coupon_code',
        'customer_name', 
        'customer_phone', 
        'customer_email',
        'customer_address', 
        'latitude',
        'longitude',
        'subtotal_amount',
        'discount_amount',
        'total_amount', 
        'store_amount',
        'app_commission',
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
        'subtotal_amount' => 'float',
        'discount_amount' => 'float',
        'total_amount' => 'float',
        'store_amount' => 'float',
        'app_commission' => 'float',
        'captain_commission' => 'float',
    ];

    protected $appends = ['store'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function getStoreAttribute()
    {
        if ($this->relationLoaded('store') && $this->getRelation('store')) {
            return $this->getRelation('store');
        }
        if ($this->store_id) {
            $store = Store::find($this->store_id);
            if ($store) return $store;
        }
        $firstItem = $this->items()->with('product.store')->first();
        if ($firstItem && $firstItem->product && $firstItem->product->store) {
            return $firstItem->product->store;
        }
        return \App\Models\Store::first();
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function captain()
    {
        return $this->belongsTo(User::class, 'assigned_captain_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
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

    public function ledgerEntries()
    {
        return $this->hasMany(CaptainLedgerEntry::class);
    }
}
