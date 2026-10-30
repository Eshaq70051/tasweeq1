<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $couponId = $this->route('coupon') instanceof \App\Models\Coupon
            ? $this->route('coupon')->id
            : $this->route('coupon');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('coupons', 'code')->ignore($couponId),
            ],
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'كود الخصم مطلوب',
            'code.unique' => 'كود الخصم هذا موجود مسبقاً',
            'type.required' => 'نوع الخصم مطلوب (نسبة مئوية أو مبلغ ثابت)',
            'value.required' => 'قيمة الخصم مطلوبة',
            'value.min' => 'يجب أن تكون قيمة الخصم أكبر من صفر',
            'expires_at.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو مساوي لتاريخ البدء',
        ];
    }
}
