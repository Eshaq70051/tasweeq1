<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'store_id',
        'from_date',
        'to_date',
        'total_orders_count',
        'total_orders_amount',
        'commission_rate',
        'commission_amount',
        'net_amount',
        'paid_amount',
        'status',
        'payment_date',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'payment_date' => 'date',
        'total_orders_amount' => 'float',
        'commission_rate' => 'float',
        'commission_amount' => 'float',
        'net_amount' => 'float',
        'paid_amount' => 'float',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->net_amount - (float) $this->paid_amount);
    }

    public static function generateInvoiceNumber(): string
    {
        $yearMonth = date('Ym');
        $last = static::where('invoice_number', 'like', "INV-{$yearMonth}-%")->latest('id')->first();
        $next = 1;
        if ($last) {
            $parts = explode('-', $last->invoice_number);
            $next = intval(end($parts)) + 1;
        }
        return sprintf('INV-%s-%04d', $yearMonth, $next);
    }
}
