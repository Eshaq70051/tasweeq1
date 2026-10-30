<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Product;
use App\Models\PaymentMethod;
use App\Models\CaptainRating;
use App\Services\OrderNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::where('user_id', Auth::id())
                       ->with(['items.product', 'statusHistory', 'captain', 'captainRating'])
                       ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('per_page')) {
            $perPage = min(100, max(1, $request->integer('per_page', 15)));
            $orders = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => OrderResource::collection($orders)->response()->getData(true),
            ]);
        }

        $orders = $query->get();

        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders),
        ]);
    }

    public function show($id)
    {
        $order = Order::where('id', $id)
                      ->where(function ($query) {
                          $query->where('user_id', Auth::id())
                                ->orWhere('assigned_captain_id', Auth::id());
                      })
                      ->with(['items.product', 'statusHistory', 'captain', 'captainRating'])
                      ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'الطلب غير موجود',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
        ]);
    }

    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();

        $paymentMethod = PaymentMethod::where('code', $validated['payment_method'])
            ->where('is_active', true)
            ->first();

        if (!$paymentMethod) {
            return response()->json([
                'success' => false,
                'message' => 'وسيلة الدفع غير متاحة حاليًا',
            ], 422);
        }

        if ($validated['payment_method'] !== 'cash' && !$request->hasFile('receipt_image')) {
            return response()->json([
                'success' => false,
                'message' => 'يجب إرفاق صورة إيصال الدفع للوسائل غير النقدية',
            ], 422);
        }

        $cartItems = Cart::where('user_id', Auth::id())->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'سلة المشتريات فارغة',
            ], 400);
        }

        DB::beginTransaction();

        try {
            $totalAmount = 0;
            $orderItems = [];

            foreach ($cartItems as $cartItem) {
                $product = Product::find($cartItem->product_id);

                if (!$product || $product->stock < $cartItem->quantity) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "الكمية المطلوبة غير متوفرة في المخزون للمنتج: " . ($product->name ?? 'غير معروف'),
                    ], 400);
                }

                $subtotal = $product->price * $cartItem->quantity;
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $subtotal,
                ];

                $product->stock -= $cartItem->quantity;
                $product->save();
            }

            $subtotalAmount = $totalAmount;
            $discountAmount = 0;
            $coupon = null;

            if (!empty($validated['coupon_code'])) {
                $couponValidation = app(\App\Services\CouponService::class)->validateCoupon(
                    $validated['coupon_code'],
                    Auth::user(),
                    $subtotalAmount
                );

                if ($couponValidation['valid']) {
                    $coupon = \App\Models\Coupon::where('code', $couponValidation['code'])->first();
                    $discountAmount = $couponValidation['discount_amount'];
                    $totalAmount = $couponValidation['final_amount'];
                }
            }

            $receiptPath = $request->hasFile('receipt_image')
                ? $request->file('receipt_image')->store('receipts', 'public')
                : null;

            $order = Order::create([
                'user_id' => Auth::id(),
                'assigned_captain_id' => null,
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_address' => $validated['customer_address'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'subtotal_amount' => $subtotalAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'captain_commission' => 0,
                'payment_method' => $validated['payment_method'],
                'transfer_number' => $validated['transfer_number'] ?? null,
                'receipt_image' => $receiptPath,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'is_paid' => 0,
            ]);

            if ($coupon && $discountAmount > 0) {
                app(\App\Services\CouponService::class)->applyCouponToOrder($coupon, $order, Auth::user(), $discountAmount);
            }

            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            $order->statusHistory()->create([
                'status' => 'pending',
                'changed_by' => Auth::id(),
            ]);

            Cart::where('user_id', Auth::id())->delete();

            DB::commit();

            // إرسال إشعار فوري للعميل بتأكيد إنشاء واستلام طلبه
            app(OrderNotificationService::class)->notifyCustomerOrderCreated($order);

            $order->load(['items.product', 'statusHistory']);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الطلب بنجاح',
                'data' => new OrderResource($order),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'فشل إنشاء الطلب: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function cancel($id)
    {
        $order = Order::where('id', $id)
                      ->where('user_id', Auth::id())
                      ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'الطلب غير موجود',
            ], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن إلغاء الطلب بعد دخوله مرحلة التجهيز أو التوصيل',
            ], 400);
        }

        $order->status = 'cancelled';
        $order->save();
        $order->statusHistory()->create([
            'status' => 'cancelled',
            'changed_by' => Auth::id(),
        ]);

        app(OrderNotificationService::class)->notifyCustomerOrderStatus($order, 'cancelled');

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء الطلب بنجاح',
            'data' => new OrderResource($order->load(['items', 'statusHistory'])),
        ]);
    }

    public function rate($id, Request $request)
    {
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->whereIn('status', ['completed', 'delivered'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تقييم هذا الطلب حالياً',
            ], 422);
        }

        if (!$order->assigned_captain_id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد كابتن مرتبط بهذا الطلب',
            ], 422);
        }

        if (CaptainRating::where('order_id', $order->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'تم تقييم هذا الطلب مسبقاً',
            ], 422);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $rating = CaptainRating::create([
            'order_id' => $order->id,
            'customer_id' => Auth::id(),
            'captain_id' => $order->assigned_captain_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال تقييمك بنجاح',
            'data' => $rating,
        ], 201);
    }
}
