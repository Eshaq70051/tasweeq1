<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'name_en',
        'logo',
        'banner',
        'rating',
        'commission_rate',
        'distance',
        'delivery_time',
        'is_open',
        'address',
        'phone',
        'bank_name',
        'account_number',
        'iban',
        'tax_number',
        'is_featured',
    ];

    protected $casts = [
        'rating' => 'float',
        'commission_rate' => 'float',
        'is_open' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected $appends = [
        'logo_url',
        'banner_url',
    ];

    public function getLogoUrlAttribute()
    {
        if (!$this->logo) {
            return null;
        }
        if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
            return $this->logo;
        }
        return asset('storage/' . $this->logo);
    }

    public function getBannerUrlAttribute()
    {
        if (!$this->banner) {
            return null;
        }
        if (str_starts_with($this->banner, 'http://') || str_starts_with($this->banner, 'https://')) {
            return $this->banner;
        }
        return asset('storage/' . $this->banner);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function menuSections()
    {
        return $this->hasMany(StoreMenuSection::class)->orderBy('sort_order');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function invoices()
    {
        return $this->hasMany(StoreInvoice::class)->latest();
    }

    public function financialTransactions()
    {
        return $this->hasMany(FinancialTransaction::class, 'party_id')
            ->where('party_type', 'store')
            ->orderBy('transaction_date', 'desc');
    }

    /**
     * احتساب الملخص المالي والذمة للمطعم / المتجر
     */
    public function getFinancialSummary(): array
    {
        return Cache::remember("store.fin_summary.{$this->id}", 60, function () {
            // 1. مبيعات الطلبات المكتملة
            $completedOrders = Order::where(function ($q) {
                $q->where('store_id', $this->id)
                  ->orWhereHas('items.product', function ($itemQ) {
                      $itemQ->where('store_id', $this->id);
                  });
            })->where('status', 'completed');

            $totalOrdersCount = (clone $completedOrders)->count();
            $totalSales = (float) (clone $completedOrders)->sum('total_amount');

            // عمولة التطبيق (نسبة المتجر الافتراضية)
            $rate = $this->commission_rate > 0 ? $this->commission_rate : 10.0;
            $appCommission = round(($totalSales * $rate) / 100, 2);

            // صافي المبيعات المستحق للمتجر
            $netSalesDue = max(0, $totalSales - $appCommission);

            // 2. الحركات المالية المسجلة للمتجر
            $transactions = FinancialTransaction::where('party_type', 'store')
                ->where('party_id', $this->id)
                ->get();

            // المدين: المبالغ المصروفة للمتجر + العمولات والخصومات
            $paidToStore = (float) $transactions->where('type', 'payment')->sum('amount');
            $manualDebit = (float) $transactions->where('direction', 'debit')->where('type', '!=', 'payment')->sum('amount');
            $totalDebit = $paidToStore + $manualDebit;

            // الدائن: مستحقات المبيعات + أي تسويات إضافية لصالحه
            $manualCredit = (float) $transactions->where('direction', 'credit')->sum('amount');
            $totalCredit = $netSalesDue + $manualCredit;

            // صافي الرصيد = الدائن (له) - المدين (المصروف له)
            $balance = $totalCredit - $totalDebit;

            return [
                'orders_count' => $totalOrdersCount,
                'total_sales' => $totalSales,
                'commission_rate' => $rate,
                'app_commission' => $appCommission,
                'net_sales_due' => $netSalesDue,
                'total_paid' => $paidToStore,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'balance' => $balance,
                'status' => $balance > 0 ? 'creditor' : ($balance < 0 ? 'debtor' : 'settled'),
            ];
        });
    }
}
