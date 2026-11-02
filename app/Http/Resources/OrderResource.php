<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $receiptImageUrl = null;
        if ($this->receipt_image) {
            $receiptImageUrl = str_starts_with($this->receipt_image, 'http')
                ? $this->receipt_image
                : asset('storage/' . $this->receipt_image);
        }

        $statusLabels = [
            'pending' => 'قيد الانتظار',
            'processing' => 'قيد التجهيز',
            'arrived_store' => 'وصل المتجر',
            'on_the_way' => 'في الطريق',
            'completed' => 'مكتمل',
            'delivered' => 'تم التوصيل',
            'cancelled' => 'ملغي',
        ];

        $subtotal = (float) ($this->subtotal_amount ?? $this->total_amount);
        $total = (float) $this->total_amount;
        $deliveryFee = max(0, $total - $subtotal);
        $store = $this->store;
        $storeName = $store ? ($store->name . ($store->name_en ? ' - ' . $store->name_en : '')) : 'بسباس ورز - PILIPILI MCHELE';

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'store_id' => $this->store_id,
            'assigned_captain_id' => $this->assigned_captain_id,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_email' => $this->customer_email,
            'customer_address' => $this->customer_address,
            'latitude' => $this->latitude ? (float) $this->latitude : null,
            'longitude' => $this->longitude ? (float) $this->longitude : null,
            'subtotal_amount' => $subtotal,
            'formatted_subtotal' => number_format($subtotal, 2),
            'discount_amount' => (float) ($this->discount_amount ?? 0),
            'formatted_discount' => number_format((float) ($this->discount_amount ?? 0), 2),
            'delivery_fee' => $deliveryFee,
            'formatted_delivery_fee' => number_format($deliveryFee, 2),
            'coupon_id' => $this->coupon_id,
            'coupon_code' => $this->coupon_code,
            'total_amount' => $total,
            'formatted_total' => number_format($total, 2),
            'captain_commission' => (float) ($this->captain_commission ?? 0),
            'payment_method' => $this->payment_method,
            'transfer_number' => $this->transfer_number,
            'receipt_image' => $this->receipt_image,
            'receipt_image_url' => $receiptImageUrl,
            'notes' => $this->notes,
            'store' => $store ? [
                'id' => $store->id,
                'name' => $store->name,
                'name_en' => $store->name_en,
                'phone' => $store->phone,
                'address' => $store->address,
            ] : null,
            'store_name' => $storeName,
            'status' => $this->status,
            'status_label' => $statusLabels[$this->status] ?? $this->status,
            'is_paid' => (bool) $this->is_paid,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'user' => new UserResource($this->whenLoaded('user')),
            'captain' => new UserResource($this->whenLoaded('captain')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'status_history' => OrderStatusHistoryResource::collection($this->whenLoaded('statusHistory')),
            'captain_rating' => $this->whenLoaded('captainRating'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
