<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaptainLedgerEntry extends Model
{
    protected $fillable = [
        'captain_id',
        'order_id',
        'captain_payment_id',
        'type',
        'direction',
        'amount',
        'balance_after',
        'description',
    ];

    protected $casts = [
        'amount' => 'float',
        'balance_after' => 'float',
    ];

    public function captain(): BelongsTo
    {
        return $this->belongsTo(User::class, 'captain_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(CaptainPayment::class, 'captain_payment_id');
    }
}
