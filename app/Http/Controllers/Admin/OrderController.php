<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items', 'captain']);
        if ($request->has('status')) $query->where('status', $request->status);
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function showFromWeb(Order $order)
    {
        $order->load(['user', 'items', 'captain']);
        return view('admin.order-detail', compact('order'));
    }

    public function updateStatusFromWeb(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,arrived_store,on_the_way,completed,cancelled',
        ]);

        $order->status = $validated['status'];
        if ($order->status === 'completed' && $order->captain_commission <= 0) {
            $order->captain_commission = round(
                (float) $order->total_amount * ((float) env('CAPTAIN_COMMISSION_RATE', 10) / 100),
                2
            );
        }
        $order->save();
        $order->statusHistory()->create([
            'status' => $order->status,
            'changed_by' => auth()->id(),
        ]);

        return redirect()->route('admin.orders')->with('success', 'تم تحديث حالة الطلب بنجاح');
    }

    public function markPaidFromWeb(Order $order)
    {
        if ($order->payment_method !== 'cash' && !$order->receipt_image) {
            return redirect()->back()->with('error', 'لا يمكن اعتماد الدفع قبل إرفاق صورة الإيصال');
        }

        $order->is_paid = true;
        $order->paid_at = now();
        $order->save();

        return redirect()->route('admin.orders')->with('success', 'تم تأكيد استلام المبلغ');
    }
}
