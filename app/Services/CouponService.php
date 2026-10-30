<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;

class CouponService
{
    /**
     * فحص صلاحية الكوبون واحتساب قيمة الخصم والمجموع النهائي
     */
    public function validateCoupon(string $code, ?User $user, float $subtotal): array
    {
        $code = strtoupper(trim($code));

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return [
                'valid' => false,
                'message' => 'كود الخصم غير صحيح أو غير موجود',
            ];
        }

        $check = $coupon->validateForUser($user, $subtotal);

        if (!$check['valid']) {
            return [
                'valid' => false,
                'message' => $check['message'],
            ];
        }

        $discount = $coupon->calculateDiscount($subtotal);
        $finalAmount = max(0, round($subtotal - $discount, 2));

        return [
            'valid' => true,
            'message' => 'تم تطبيق كود الخصم بنجاح',
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'discount_amount' => $discount,
            'subtotal' => $subtotal,
            'final_amount' => $finalAmount,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'description' => $coupon->description,
            ],
        ];
    }

    /**
     * تطبيق الكوبون وتوثيق استخدامه في قاعدة البيانات بعد نجاح الطلب
     */
    public function applyCouponToOrder(Coupon $coupon, Order $order, ?User $user, float $discount): ?CouponUsage
    {
        if (!$user) {
            return null;
        }

        $coupon->increment('used_count');

        return CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'discount_amount' => $discount,
            'created_at' => now(),
        ]);
    }
}
