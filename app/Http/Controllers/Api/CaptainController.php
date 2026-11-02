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
        
        // الطلبات النشطة المسندة للكابتن
        $activeOrders = Order::with('items')
            ->where('assigned_captain_id', $captain->id)
            ->whereIn('status', ['pending', 'confirmed', 'processing', 'arrived_store', 'on_the_way'])
            ->latest()
            ->get();

        // لا تظهر الطلبات غير المسندة إلا للكابتن المتصل.
        $availableOrders = $captain->is_available
            ? Order::with('items')
                ->whereNull('assigned_captain_id')
                ->whereIn('status', ['pending', 'processing', 'confirmed'])
                ->latest()
                ->get()
            : collect();

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
                'available_orders' => $availableOrders,
                'active_orders' => $activeOrders,
                'assigned_orders' => $activeOrders,
                'orders' => $activeOrders,
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
                    'orders_count' => count($activeOrders),
                    'available_orders_count' => count($availableOrders),
                    'completed_orders' => $completedOrdersCount,
                    'is_available' => (bool) $captain->is_available,
                    'rating' => $captainRating ? round((float) $captainRating, 1) : 4.8,
                ],
            ]
        ]);
    }

    /**
     * تحديث موقع الكابتن الحالي
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $captain = Auth::user();
        $captain->latitude = $request->latitude;
        $captain->longitude = $request->longitude;
        $captain->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث موقعك الحالي بنجاح',
            'data' => [
                'latitude' => (float) $captain->latitude,
                'longitude' => (float) $captain->longitude,
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
     * تقرير كشف الحساب والإحصائيات الشاملة للكابتن
     */
    public function report()
    {
        $captain = Auth::user();
        $summary = app(CaptainWalletService::class)->getCaptainFinancialSummary($captain);

        $payments = CaptainPayment::where('captain_id', $captain->id)
            ->latest()
            ->get();

        $completedOrders = Order::with('items')
            ->where('assigned_captain_id', $captain->id)
            ->where('status', 'completed')
            ->latest()
            ->get();

        $captainRating = \App\Models\CaptainRating::where('captain_id', $captain->id)->avg('rating');
        $ratingVal = $captainRating ? round((float) $captainRating, 1) : 4.8;

        // إحصائيات اليوم
        $today = now()->startOfDay();
        $dailyOrders = Order::where('assigned_captain_id', $captain->id)
            ->where('status', 'completed')
            ->where('created_at', '>=', $today)
            ->get();

        $dailyDelivery = (float) $dailyOrders->sum('captain_commission');
        $dailyTotal = (float) $dailyOrders->sum('total_amount');
        $dailyOrdersCount = $dailyOrders->count();

        $outstandingDebt = $summary['outstanding_debt'] ?? 0;
        $insuranceAmount = 50000.0;
        $insuranceRemaining = max(0, $insuranceAmount - $outstandingDebt);

        $completedOrdersCount = $summary['completed_orders_count'] ?? 0;

        return response()->json([
            'success' => true,
            'data' => array_merge($summary, [
                'rating' => $ratingVal,
                'is_exceeded' => $outstandingDebt >= $insuranceAmount,
                'standing_label' => ($outstandingDebt < $insuranceAmount) ? 'غير متجاوز' : 'متجاوز الحد',
                'total_balance' => (float) ($summary['earned_commission'] ?? 0),
                'daily' => [
                    'delivery' => $dailyDelivery,
                    'total' => $dailyTotal,
                    'orders' => $dailyOrdersCount,
                ],
                'insurance' => [
                    'amount' => $insuranceAmount,
                    'remaining' => $insuranceRemaining,
                ],
                'invoices' => [
                    'remaining' => max(0, 50 - $completedOrdersCount),
                    'used' => $completedOrdersCount,
                    'damaged' => 0,
                ],
                'payments' => $payments,
                'completed_orders' => $completedOrders,
                'orders' => $completedOrders,
            ])
        ]);
    }

    /**
     * استلام أو قبول الطلب
     */
    public function acceptOrder(Request $request, $id)
    {
        $captain = Auth::user();
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'الطلب غير موجود'], 404);
        }

        // إذا كان الطلب متاحاً وغير مسند
        if (!$order->assigned_captain_id) {
            if (!$captain->is_available) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب فتح حالة الاتصال قبل استلام الطلب',
                ], 403);
            }

            $order->assigned_captain_id = $captain->id;
            $order->status = 'processing';
            $order->save();
            $this->recordStatus($order);
            app(OrderNotificationService::class)->notifyCustomerOrderStatus($order, 'processing');
            return response()->json(['success' => true, 'message' => 'تم استلام وتأكيد الطلب بنجاح']);
        }

        if ($order->assigned_captain_id != $captain->id) {
            return response()->json(['success' => false, 'message' => 'الطلب مسند لكابتن آخر'], 403);
        }

        if ($order->status === 'arrived_store') {
            $order->status = 'on_the_way';
            $order->save();
            $this->recordStatus($order);
            app(OrderNotificationService::class)->notifyCustomerOrderStatus($order, 'on_the_way');
            return response()->json(['success' => true, 'message' => 'أنت الآن في طريقك لتسليم الطلب للعميل']);
        }

        return response()->json(['success' => true, 'message' => 'الطلب قيد المتابعة']);
    }

    /**
     * رفض الطلب
     */
    public function rejectOrder($id)
    {
        $captain = Auth::user();
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'الطلب غير موجود'], 404);
        }

        if ($order->assigned_captain_id == $captain->id) {
            $order->assigned_captain_id = null;
            $order->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم رفض الطلب بنجاح',
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

        $isAvailable = $request->boolean('is_available');
        if (!$isAvailable && $user->is_available) {
            $hasActiveOrders = Order::where('assigned_captain_id', $user->id)
                ->whereIn('status', [
                    'pending',
                    'confirmed',
                    'processing',
                    'arrived_store',
                    'on_the_way',
                ])
                ->exists();

            if ($hasActiveOrders) {
                return response()->json([
                    'success' => false,
                    'message' => 'أكمل توصيل الطلبات النشطة قبل إغلاق حالة الاتصال',
                    'is_available' => true,
                ], 409);
            }
        }

        $user->is_available = $isAvailable;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => $user->is_available ? 'أنت متصل الآن' : 'تم إغلاق استقبال الطلبات',
            'is_available' => (bool) $user->is_available
        ]);
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

    /**
     * تقييمات الكابتن
     */
    public function ratings()
    {
        $captain = Auth::user();
        $ratings = \App\Models\CaptainRating::with('order')
            ->where('captain_id', $captain->id)
            ->latest()
            ->get();
        $avg = \App\Models\CaptainRating::where('captain_id', $captain->id)->avg('rating');

        return response()->json([
            'success' => true,
            'data' => [
                'average_rating' => $avg ? round((float) $avg, 1) : 4.8,
                'ratings' => $ratings,
            ]
        ]);
    }

    private function recordStatus(Order $order): void
    {
        $order->statusHistory()->create([
            'status' => $order->status,
            'changed_by' => Auth::id(),
        ]);
    }
}
