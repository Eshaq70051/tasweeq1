<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'basic_salary',
        'allowances',
        'bonus',
        'deductions',
        'net_salary',
        'paid_amount',
        'status',
        'payment_date',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'basic_salary' => 'float',
        'allowances' => 'float',
        'bonus' => 'float',
        'deductions' => 'float',
        'net_salary' => 'float',
        'paid_amount' => 'float',
        'payment_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->net_salary - (float) $this->paid_amount);
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($salary) {
            $salary->net_salary = max(0, (float) $salary->basic_salary + (float) $salary->allowances + (float) $salary->bonus - (float) $salary->deductions);
            if ($salary->paid_amount >= $salary->net_salary && $salary->net_salary > 0) {
                $salary->status = 'paid';
            } elseif ($salary->paid_amount > 0) {
                $salary->status = 'partial';
            } else {
                $salary->status = 'pending';
            }
        });
    }
}
