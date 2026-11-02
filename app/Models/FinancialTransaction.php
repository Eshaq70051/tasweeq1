<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'type', // receipt, payment, adjustment
        'party_type', // store, captain, employee, general
        'party_id',
        'party_name',
        'order_id',
        'amount',
        'direction', // debit, credit
        'category',
        'title',
        'description',
        'payment_method',
        'reference_number',
        'attachment',
        'transaction_date',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'transaction_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'party_id');
    }

    public function captain()
    {
        return $this->belongsTo(User::class, 'party_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'party_id');
    }

    public function getResolvedPartyNameAttribute(): string
    {
        if ($this->party_type === 'store') {
            return $this->store?->name ?? $this->party_name ?? 'متجر';
        }
        if ($this->party_type === 'captain') {
            return $this->captain?->name ?? $this->party_name ?? 'كابتن';
        }
        if ($this->party_type === 'employee') {
            return $this->employee?->name ?? $this->party_name ?? 'موظف';
        }
        return $this->party_name ?: 'حساب عام / منصة';
    }

    public function getTypeNameAttribute(): string
    {
        return match ($this->type) {
            'receipt' => 'سند قبض',
            'payment' => 'سند صرف',
            'adjustment' => 'قيد تسوية',
            default => $this->type,
        };
    }

    public function getDirectionNameAttribute(): string
    {
        return match ($this->direction) {
            'debit' => 'مدين (عليه)',
            'credit' => 'دائن (له)',
            default => $this->direction,
        };
    }

    public static function generateTransactionNumber(): string
    {
        $yearMonth = date('Ym');
        $last = static::where('transaction_number', 'like', "TXN-{$yearMonth}-%")->latest('id')->first();
        $next = 1;
        if ($last) {
            $parts = explode('-', $last->transaction_number);
            $next = intval(end($parts)) + 1;
        }
        return sprintf('TXN-%s-%04d', $yearMonth, $next);
    }
}
