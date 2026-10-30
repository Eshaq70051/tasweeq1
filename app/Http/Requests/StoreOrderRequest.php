<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'customer_address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'payment_method' => 'required|string|exists:payment_methods,code',
            'coupon_code' => 'nullable|string|max:50',
            'transfer_number' => 'nullable|string|max:100',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'اسم العميل مطلوب',
            'customer_phone.required' => 'رقم الهاتف مطلوب',
            'customer_address.required' => 'عنوان التوصيل مطلوب',
            'payment_method.required' => 'طريقة الدفع مطلوبة',
            'payment_method.exists' => 'طريقة الدفع المحددة غير صالحة أو غير متوفرة',
            'receipt_image.image' => 'يجب أن يكون الإيصال صورة',
            'receipt_image.max' => 'حجم صورة الإيصال لا يتجاوز 3 ميجابايت',
        ];
    }
}
