<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $paymentMethods = PaymentMethod::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.payment-methods', compact('paymentMethods'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store('payment-methods', 'public');
        }
        PaymentMethod::create($data);
        return redirect()->route('admin.payment-methods')->with('success', 'تمت إضافة وسيلة الدفع');
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $data = $this->validated($request, $paymentMethod->id);
        if ($request->hasFile('icon')) {
            if ($paymentMethod->icon) Storage::disk('public')->delete($paymentMethod->icon);
            $data['icon'] = $request->file('icon')->store('payment-methods', 'public');
        }
        $paymentMethod->update($data);
        return redirect()->route('admin.payment-methods')->with('success', 'تم تحديث وسيلة الدفع');
    }

    public function toggle(PaymentMethod $paymentMethod)
    {
        $paymentMethod->update(['is_active' => !$paymentMethod->is_active]);
        return back()->with('success', 'تم تغيير حالة وسيلة الدفع');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->code === 'cash') {
            return back()->with('error', 'لا يمكن حذف الدفع عند الاستلام');
        }
        if ($paymentMethod->icon) Storage::disk('public')->delete($paymentMethod->icon);
        $paymentMethod->delete();
        return back()->with('success', 'تم حذف وسيلة الدفع');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => 'required|string|max:40|alpha_dash|unique:payment_methods,code,' . ($id ?? 'NULL'),
            'name' => 'required|string|max:100',
            'account_number' => 'nullable|string|max:80',
            'background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active' => 'nullable|boolean',
            'requires_receipt' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0|max:999',
            'icon' => 'nullable|image|max:2048',
        ]);
    }
}
