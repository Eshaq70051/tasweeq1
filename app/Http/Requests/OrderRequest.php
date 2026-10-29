<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'customer_address' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'payment_method' => 'required|in:jaib,cash,transfer',
            'transfer_number' => 'nullable|string|max:100',
            'receipt_image' => 'nullable|image|max:2048',
            'notes' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'customer_name.required' => 'اسم العميل مطلوب',
            'customer_phone.required' => 'رقم الهاتف مطلوب',
            'customer_address.required' => 'العنوان مطلوب',
            'payment_method.required' => 'طريقة الدفع مطلوبة',
            'payment_method.in' => 'طريقة الدفع غير صالحة',
        ];
    }
} 
