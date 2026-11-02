<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'job_title',
        'basic_salary',
        'allowances',
        'hire_date',
        'national_id',
        'status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'basic_salary' => 'float',
        'allowances' => 'float',
        'hire_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class)->orderBy('month', 'desc');
    }

    public function transactions()
    {
        return $this->hasMany(FinancialTransaction::class, 'party_id')
            ->where('party_type', 'employee')
            ->orderBy('transaction_date', 'desc');
    }

    public function getTotalSalaryAttribute()
    {
        return (float) ($this->basic_salary + $this->allowances);
    }

    public function getTotalPaidSalariesAttribute()
    {
        return (float) $this->salaries()->where('status', 'paid')->sum('paid_amount');
    }

    public function getPendingSalariesCountAttribute()
    {
        return $this->salaries()->where('status', '!=', 'paid')->count();
    }
}
