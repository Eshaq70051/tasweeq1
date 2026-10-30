<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'details' => 'nullable|string|max:5000',
            'category_id' => 'required|integer|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:3072',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المنتج مطلوب',
            'name.max' => 'اسم المنتج يجب ألا يتجاوز 255 حرفًا',
            'price.required' => 'سعر المنتج مطلوب',
            'price.numeric' => 'يجب أن يكون السعر رقمًا صحيحًا أو عشريًا',
            'price.min' => 'لا يمكن أن يكون السعر أقل من صفر',
            'currency.required' => 'عملة المنتج مطلوبة',
            'category_id.required' => 'يرجى اختيار تصنيف المنتج',
            'category_id.exists' => 'التصنيف المختار غير موجود',
            'stock.required' => 'الكمية في المخزون مطلوبة',
            'stock.integer' => 'الكمية يجب أن تكون رقمًا صحيحًا',
            'stock.min' => 'الكمية لا يمكن أن تقل عن صفر',
            'image.image' => 'الملف المرفوع يجب أن يكون صورة',
            'image.mimes' => 'صيغ الصور المدعومة هي jpeg, png, jpg, webp, gif',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 3 ميجابايت',
        ];
    }
}
