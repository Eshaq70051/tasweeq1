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
        'store_id',
        'store_menu_section_id',
        'stock',
        'is_available',
        'low_stock_threshold',
        'old_price',
    ];

    protected $casts = [
        'price' => 'float',
        'old_price' => 'float',
        'stock' => 'integer',
        'is_available' => 'boolean',
        'low_stock_threshold' => 'integer',
    ];

    protected $appends = [
        'image_url',
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function menuSection()
    {
        return $this->belongsTo(StoreMenuSection::class, 'store_menu_section_id');
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
        if (!$this->image) {
            return null;
        }
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }
        return asset('storage/' . $this->image);
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
