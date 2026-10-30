<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'currency',
        'details',
        'image', 
        'category_id',
        'stock',
        'low_stock_threshold',
        'old_price',
    ];

    protected $casts = [
        'price' => 'float',
        'old_price' => 'float',
        'stock' => 'integer',
        'low_stock_threshold' => 'integer',
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }
    
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock > 0 && $this->stock <= ($this->low_stock_threshold ?? 5);
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->stock <= 0;
    }
}
