<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $merge = [];
        if ($this->has('coupon_code') && ($this->coupon_code === 'null' || trim((string)$this->coupon_code) === '')) {
            $merge['coupon_code'] = null;
        }
        if ($this->has('customer_email') && ($this->customer_email === 'null' || trim((string)$this->customer_email) === '')) {
            $merge['customer_email'] = null;
        }
        if ($this->has('latitude') && ($this->latitude === 'null' || $this->latitude === '')) {
            $merge['latitude'] = null;
        }
        if ($this->has('longitude') && ($this->longitude === 'null' || $this->longitude === '')) {
            $merge['longitude'] = null;
        }
        if ($this->has('transfer_number') && ($this->transfer_number === 'null' || trim((string)$this->transfer_number) === '')) {
            $merge['transfer_number'] = null;
        }
        if ($this->has('notes') && ($this->notes === 'null' || trim((string)$this->notes) === '')) {
            $merge['notes'] = null;
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|string|max:100',
            'customer_address' => 'required|string|max:500',
            'latitude' => 'nullable',
            'longitude' => 'nullable',
            'payment_method' => 'required|string|exists:payment_methods,code',
            'coupon_code' => 'nullable|string|max:50',
            'transfer_number' => 'nullable|string|max:100',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'notes' => 'nullable|string|max:1000',
            'items' => 'nullable',
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
            'receipt_image.max' => 'حجم صورة الإيصال لا يتجاوز 5 ميجابايت',
        ];
    }
}
