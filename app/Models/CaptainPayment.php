<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaptainPayment extends Model
{
    protected $fillable = [
        'captain_id', 'amount', 'payment_method', 'reference',
        'receipt_image', 'status', 'approved_by', 'approved_at', 'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'approved_at' => 'datetime',
    ];

    public function captain()
    {
        return $this->belongsTo(User::class, 'captain_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}