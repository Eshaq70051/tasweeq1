<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingSlider extends Model
{
    protected $fillable = [
        'title',
        'image_path',
        'link',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
