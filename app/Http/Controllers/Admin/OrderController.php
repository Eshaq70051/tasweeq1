<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderNotificationService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items', 'captain']);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($request->integer('per_page', 20));

        return OrderResource::collection($orders)->additional([
            'success' => true,
        ]);
    }

    public function showFromWeb(Order $order)
    {
        $order->load(['user', 'items.product', 'captain', 'statusHistory']);
        return view('admin.order-detail', compact('order'));
    }

    public function updateStatusFromWeb(UpdateOrderStatusRequest $request, Order $order)
    {
        $validated = $request->validated();

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

        // إرسال إشعار فوري للعميل بتحديث الحالة
        app(OrderNotificationService::class)->notifyCustomerOrderStatus($order, $order->status);

        // تسجيل قيود الدفتر المالي للكابتن عند اكتمال الطلب
        if ($order->status === 'completed') {
            app(\App\Services\CaptainWalletService::class)->recordOrderDelivered($order);
        }

        return redirect()->route('admin.orders')->with('success', 'تم تحديث حالة الطلب وإرسال إشعار للعميل');
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
