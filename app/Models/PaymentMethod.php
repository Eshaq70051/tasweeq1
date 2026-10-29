<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'code',
        'name',
        'account_number',
        'icon',
        'background_color',
        'text_color',
        'is_active',
        'requires_receipt',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_receipt' => 'boolean',
    ];
}
