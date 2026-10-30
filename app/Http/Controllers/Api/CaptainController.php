<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\CaptainPayment;
use App\Services\CaptainWalletService;
use App\Services\OrderNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CaptainController extends Controller
{
    /**
     * لوحة معلومات الكابتن
     */
    public function dashboard()
    {
        $captain = Auth::user();
        
        $assignedOrders = Order::with('items')
            ->where('assigned_captain_id', $captain->id)
            ->whereIn('status', ['processing', 'arrived_store', 'on_the_way'])
            ->latest()
            ->get();

        $completedOrdersCount = Order::where('assigned_captain_id', $captain->id)
            ->where('status', 'completed')
            ->count();

        $cashCollected = Order::where('assigned_captain_id', $captain->id)
            ->where('status', 'completed')
            ->where('payment_method', 'cash')
            ->sum('total_amount');

        $paidDebt = CaptainPayment::where('captain_id', $captain->id)
            ->where('status', 'approved')
            ->sum('amount');

        $pendingPayments = CaptainPayment::where('captain_id', $captain->id)
            ->where('status', 'pending')
            ->sum('amount');

        $outstandingDebt = max(0, (float) $cashCollected - (float) $paidDebt);

        $totalCommission = Order::where('assigned_captain_id', $captain->id)
            ->where('status', 'completed')
            ->sum('captain_commission');

        $captainRating = \App\Models\CaptainRating::where('captain_id', $captain->id)->avg('rating');

        return response()->json([
            'success' => true,
            'data' => [
                'is_available' => (bool) $captain->is_available,
                'assigned_orders' => $assignedOrders,
                'orders' => $assignedOrders,
                'completed_orders_count' => $completedOrdersCount,
                'cash_collected' => (float) $cashCollected,
                'total_commission' => (float) $totalCommission,
                'paid_debt' => (float) $paidDebt,
                'pending_payments' => (float) $pendingPayments,
                'outstanding_debt' => (float) $outstandingDebt,
                'stats' => [
                    'income' => (float) $totalCommission,
                    'outstanding_debt' => (float) $outstandingDebt,
                    'cash_collected' => (float) $cashCollected,
                    'paid_debt' => (float) $paidDebt,
                    'orders_count' => count($assignedOrders),
                    'completed_orders' => $completedOrdersCount,
                    'is_available' => (bool) $captain->is_available,
                    'rating' => $captainRating ? round((float) $captainRating, 1) : 5.0,
                ],
            ]
        ]);
    }

    /**
     * المحفظة المالية وسجل المعاملات للكابتن
     */
    public function wallet()
    {
        $captain = Auth::user();
        $summary = app(CaptainWalletService::class)->getCaptainFinancialSummary($captain);
        $ledger = $captain->captainLedgerEntries()->take(30)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'ledger' => $ledger,
            ]
        ]);
    }

    /**
     * تقرير كشف الحساب والمديونية للكابتن
     */
    public function report()
    {
        $captain = Auth::user();
        $summary = app(CaptainWalletService::class)->getCaptainFinancialSummary($captain);

        $payments = CaptainPayment::where('captain_id', $captain->id)
            ->latest()
            ->get();

        $completedOrders = Order::where('assigned_captain_id', $captain->id)
            ->where('status', 'completed')
            ->latest()
            ->get(['id', 'customer_name', 'total_amount', 'payment_method', 'captain_commission', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => array_merge($summary, [
                'payments' => $payments,
                'completed_orders' => $completedOrders,
            ])
        ]);
    }

    /**
     * تقديم طلب سداد من الكابتن
     */
    public function requestPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'reference' => 'nullable|string',
            'receipt_image' => 'nullable|image|max:3072',
            'notes' => 'nullable|string',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt_image')) {
            $receiptPath = $request->file('receipt_image')->store('captain_payments', 'public');
        }

        $payment = CaptainPayment::create([
            'captain_id' => Auth::id(),
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'reference' => $request->reference,
            'receipt_image' => $receiptPath,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم رفع طلب السداد وهو قيد المراجعة والاعتماد',
            'data' => $payment
        ], 201);
    }

    /**
     * تسجيل الوصول إلى المتجر
     */
    public function arriveAtStore($id)
    {
        $order = Order::where('id', $id)->where('assigned_captain_id', Auth::id())->first();
        
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'الطلب غير موجود أو غير مسند إليك'], 404);
        }

        if ($order->status !== 'processing') {
            return response()->json(['success' => false, 'message' => 'الطلب ليس في حالة التجهيز'], 400);
        }

        $order->status = 'arrived_store';
        $order->save();
        $this->recordStatus($order);

        // إشعار العميل بوصول الكابتن للمتجر
        app(OrderNotificationService::class)->notifyCustomerOrderStatus($order, 'arrived_store');

        return response()->json(['success' => true, 'message' => 'تم تسجيل الوصول إلى المتجر']);
    }

    /**
     * تغيير حالة التواجد (متصل/مغلق)
     */
    public function toggleStatus(Request $request)
    {
        $user = Auth::user();
        $request->validate(['is_available' => 'required|boolean']);

        $user->is_available = $request->is_available;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => $user->is_available ? 'أنت متصل الآن' : 'تم إغلاق استقبال الطلبات',
            'is_available' => (bool) $user->is_available
        ]);
    }

    /**
     * قبول استلام الطلب وبدء التوصيل
     */
    public function acceptOrder($id)
    {
        $order = Order::where('id', $id)->where('assigned_captain_id', Auth::id())->first();
        
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'الطلب غير موجود أو غير مسند إليك'], 404);
        }

        if ($order->status !== 'arrived_store') {
            return response()->json(['success' => false, 'message' => 'سجل الوصول إلى المتجر أولاً'], 400);
        }

        $order->status = 'on_the_way';
        $order->save();
        $this->recordStatus($order);

        // إشعار فوري للعميل بأن طلبه أصبح في الطريق
        app(OrderNotificationService::class)->notifyCustomerOrderStatus($order, 'on_the_way');

        return response()->json(['success' => true, 'message' => 'تم استلام الطلب بنجاح']);
    }

    /**
     * إتمام توصيل الطلب بنجاح
     */
    public function deliverOrder($id)
    {
        $order = Order::where('id', $id)->where('assigned_captain_id', Auth::id())->first();
        
        if (!$order || $order->status !== 'on_the_way') {
            return response()->json(['success' => false, 'message' => 'لا يمكن إتمام هذا الطلب حالياً'], 400);
        }

        $order->status = 'completed';
        if ($order->captain_commission <= 0) {
            $order->captain_commission = round(
                (float) $order->total_amount * ((float) env('CAPTAIN_COMMISSION_RATE', 10) / 100),
                2
            );
        }
        $order->save();
        $this->recordStatus($order);

        // تسجيل قيود دفتر الأستاذ المالي للكابتن
        app(CaptainWalletService::class)->recordOrderDelivered($order);

        // إشعار فوري للعميل باكتمال التوصيل بنجاح
        app(OrderNotificationService::class)->notifyCustomerOrderStatus($order, 'completed');

        return response()->json(['success' => true, 'message' => 'تم إيصال الطلب، شكراً لك!']);
    }

    private function recordStatus(Order $order): void
    {
        $order->statusHistory()->create([
            'status' => $order->status,
            'changed_by' => Auth::id(),
        ]);
    }
}
