<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
        'description',
    ];

    protected $casts = [
        'value' => 'float',
        'min_order_amount' => 'float',
        'max_discount_amount' => 'float',
        'usage_limit' => 'integer',
        'usage_limit_per_user' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * التحقق من صلاحية الكوبون مع مستخدم وقيمة طلب محددة
     */
    public function validateForUser(?User $user, float $subtotal): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'هذا الكوبون غير مفعّل حالياً'];
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return ['valid' => false, 'message' => 'هذا الكوبون لم يبدأ العمل به بعد'];
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return ['valid' => false, 'message' => 'انتهت صلاحية هذا الكوبون'];
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'وصل هذا الكوبون للحد الأقصى من مرات الاستخدام'];
        }

        if ($this->min_order_amount && $subtotal < $this->min_order_amount) {
            return [
                'valid' => false,
                'message' => 'الحد الأدنى للطلب لتطبيق هذا الكوبون هو ' . number_format($this->min_order_amount, 2) . ' ريال',
            ];
        }

        if ($user && $this->usage_limit_per_user) {
            $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
            if ($userUsageCount >= $this->usage_limit_per_user) {
                return ['valid' => false, 'message' => 'لقد استنفدت الحد المسموح لك لاستخدام هذا الكوبون'];
            }
        }

        return ['valid' => true, 'message' => 'الكوبون صالح للاستخدام'];
    }

    /**
     * احتساب قيمة الخصم بناء على المجموع الفرعي
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'fixed') {
            return min($this->value, $subtotal);
        }

        // نسبة مئوية
        $discount = round(($subtotal * ($this->value / 100)), 2);

        if ($this->max_discount_amount !== null && $this->max_discount_amount > 0) {
            $discount = min($discount, $this->max_discount_amount);
        }

        return min($discount, $subtotal);
    }
}
