<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Product;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;//طرق الدفع
use App\Models\CaptainRating;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
                       ->with(['items', 'statusHistory', 'captain', 'captainRating'])
                       ->orderBy('created_at', 'desc')
                       ->get();
        
        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function show($id)
    {
        $order = Order::where('id', $id)
                      ->where(function ($query) {
                          $query->where('user_id', Auth::id())
                                ->orWhere('assigned_captain_id', Auth::id());
                      })
                      ->with(['items', 'statusHistory', 'captain', 'captainRating'])
                      ->first();
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'customer_address' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'payment_method' => 'required|string|exists:payment_methods,code',
            'transfer_number' => 'nullable|string|max:100',
            'receipt_image' => 'nullable|image|max:2048',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $paymentMethod = PaymentMethod::where('code', $request->payment_method)
            ->where('is_active', true)
            ->first();

        if (!$paymentMethod) {
            return response()->json([
                'success' => false,
                'message' => 'وسيلة الدفع غير متاحة حاليًا'
            ], 422);
        }

        if ($request->payment_method !== 'cash' && !$request->hasFile('receipt_image')) {
            return response()->json([
                'success' => false,
                    'message' => 'يجب إرفاق صورة إيصال الدفع للوسائل غير النقدية'
            ], 422);
        }

        $cartItems = Cart::where('user_id', Auth::id())->get();
        
        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $totalAmount = 0;
            $orderItems = [];

            foreach ($cartItems as $cartItem) {
                $product = Product::find($cartItem->product_id);
                
                if ($product->stock < $cartItem->quantity) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Not enough stock for {$product->name}"
                    ], 400);
                }
                
                $subtotal = $product->price * $cartItem->quantity;
                $totalAmount += $subtotal;
                
                $orderItems[] = [
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $subtotal
                ];
                
                $product->stock -= $cartItem->quantity;
                $product->save();
            }

            $receiptPath = $request->hasFile('receipt_image')
                ? $request->file('receipt_image')->store('receipts', 'public')
                : null;

            $order = Order::create([
                'user_id' => Auth::id(),
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'customer_address' => $request->customer_address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'total_amount' => $totalAmount,
                'captain_commission' => 0,
                'payment_method' => $request->payment_method,
                'transfer_number' => $request->transfer_number,
                'receipt_image' => $receiptPath,
                'notes' => $request->notes,
                'status' => 'pending',
                'is_paid' => 0
            ]);

            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_name' => $item['product_name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal']
                ]);
            }

            $order->statusHistory()->create(['status' => 'pending', 'changed_by' => Auth::id()]);

            Cart::where('user_id', Auth::id())->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order->load('items')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
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
                'message' => 'Order not found'
            ], 404);
        }
        
        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be cancelled'
            ], 400);
        }

        $order->status = 'cancelled';
        $order->save();
        $order->statusHistory()->create(['status' => 'cancelled', 'changed_by' => Auth::id()]);

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully',
            'data' => $order
        ]);
    }

    public function rate($id, Request $request)
    {
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->whereIn('status', ['completed', 'delivered'])
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'لا يمكن تقييم هذا الطلب حالياً'], 422);
        }

        if (!$order->assigned_captain_id) {
            return response()->json(['success' => false, 'message' => 'لا يوجد كابتن مرتبط بهذا الطلب'], 422);
        }

        if (CaptainRating::where('order_id', $order->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'تم تقييم هذا الطلب مسبقاً'], 422);
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

        return response()->json(['success' => true, 'message' => 'تم إرسال تقييمك بنجاح', 'data' => $rating], 201);
    }
}
