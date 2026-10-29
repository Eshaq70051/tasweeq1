<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\CaptainPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CaptainController extends Controller
{
    /**
     * لوحة معلومات الكابتن: الإحصائيات والطلبات المسندة
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        $captainOrders = Order::where('assigned_captain_id', $user->id);
        $cashCollected = (clone $captainOrders)->where('status', 'completed')->where('payment_method', 'cash')->sum('total_amount');
        $paidDebt = CaptainPayment::where('captain_id', $user->id)->where('status', 'approved')->sum('amount');

        $stats = [
            'income' => (float) (clone $captainOrders)->where('status', 'completed')->sum('captain_commission'),
            'captain_commission' => (float) (clone $captainOrders)->where('status', 'completed')->sum('captain_commission'),
            'orders_count' => (clone $captainOrders)->where('status', 'completed')->whereDate('updated_at', today())->count(),
            'completed_orders' => (clone $captainOrders)->where('status', 'completed')->count(),
            'active_orders' => (clone $captainOrders)->whereIn('status', ['processing', 'arrived_store', 'on_the_way'])->count(),
            'cash_collected' => (float) $cashCollected,
            'paid_debt' => (float) $paidDebt,
            'outstanding_debt' => max(0, (float) $cashCollected - (float) $paidDebt),
            'is_available' => (bool) $user->is_available
        ];

        $assignedOrders = Order::with('statusHistory')
                               ->where('assigned_captain_id', $user->id)
                               ->whereIn('status', ['processing', 'arrived_store', 'on_the_way'])
                               ->orderBy('updated_at', 'desc')
                               ->limit(50)
                               ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'orders' => $assignedOrders
            ]
        ]);
    }

    public function report(Request $request)
    {
        $user = Auth::user();
        $orders = Order::where('assigned_captain_id', $user->id);
        if ($request->filled('from')) $orders->whereDate('created_at', '>=', $request->date('from'));
        if ($request->filled('to')) $orders->whereDate('created_at', '<=', $request->date('to'));

        $cashCollected = (clone $orders)->where('status', 'completed')->where('payment_method', 'cash')->sum('total_amount');
        $paidDebt = CaptainPayment::where('captain_id', $user->id)->where('status', 'approved')->sum('amount');

        return response()->json(['success' => true, 'data' => [
            'orders_count' => (clone $orders)->count(),
            'completed_orders' => (clone $orders)->where('status', 'completed')->count(),
            'active_orders' => (clone $orders)->whereIn('status', ['processing', 'arrived_store', 'on_the_way'])->count(),
            'cancelled_orders' => (clone $orders)->where('status', 'cancelled')->count(),
            'income' => (float) (clone $orders)->where('status', 'completed')->sum('captain_commission'),
            'captain_commission' => (float) (clone $orders)->where('status', 'completed')->sum('captain_commission'),
            'cash_collected' => (float) $cashCollected,
            'paid_debt' => (float) $paidDebt,
            'outstanding_debt' => max(0, (float) $cashCollected - (float) $paidDebt),
            'payments' => CaptainPayment::where('captain_id', $user->id)->latest()->get(),
            'orders' => (clone $orders)->with('statusHistory')->latest()->limit(100)->get(),
        ]]);
    }

    public function requestPayment(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:jaib,jawali,one_cash',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'receipt_image' => 'required|image|max:2048',
        ]);

        $cashCollected = Order::where('assigned_captain_id', $user->id)->where('status', 'completed')->where('payment_method', 'cash')->sum('total_amount');
        $approved = CaptainPayment::where('captain_id', $user->id)->where('status', 'approved')->sum('amount');
        $pending = CaptainPayment::where('captain_id', $user->id)->where('status', 'pending')->sum('amount');
        $available = max(0, (float) $cashCollected - (float) $approved - (float) $pending);
        if ((float) $validated['amount'] > $available) {
            return response()->json(['success' => false, 'message' => 'المبلغ أكبر من المديونية المتاحة للسداد'], 422);
        }

        $validated['receipt_image'] = $request->file('receipt_image')->store('captain-payments', 'public');
        $payment = CaptainPayment::create(array_merge($validated, ['captain_id' => $user->id]));
        return response()->json(['success' => true, 'message' => 'تم إرسال طلب السداد للمراجعة', 'data' => $payment], 201);
    }

    /** تسجيل وصول الكابتن إلى المتجر قبل استلام الطلب. */
    public function arriveAtStore($id)
    {
        $order = Order::where('id', $id)->where('assigned_captain_id', Auth::id())->first();

        if (!$order || $order->status !== 'processing') {
            return response()->json(['success' => false, 'message' => 'لا يمكن تسجيل الوصول لهذا الطلب حالياً'], 400);
        }

        $order->status = 'arrived_store';
        $order->save();
        $this->recordStatus($order);

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
        $order->captain_commission = round(
            (float) $order->total_amount * ((float) env('CAPTAIN_COMMISSION_RATE', 10) / 100),
            2
        );
        $order->save();
        $this->recordStatus($order);

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
