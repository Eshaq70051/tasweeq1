<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\CaptainPayment;

class OrderNotificationService
{
    protected FcmService $fcm;

    public function __construct(FcmService $fcm)
    {
        $this->fcm = $fcm;
    }

    /**
     * إرسال إشعار فوري للكابتن عند إسناد طلب جديد إليه
     */
    public function notifyCaptainOrderAssigned(Order $order, ?User $captain = null): void
    {
        $captain = $captain ?: $order->captain;
        if (!$captain) {
            return;
        }

        $title = "طلب توصيل جديد #{$order->id}";
        $body = "تم إسناد طلب جديد إليك بمبلغ " . number_format($order->total_amount, 2) . " ريال. العنوان: {$order->customer_address}";

        $this->fcm->sendToUser($captain, $title, $body, [
            'type' => 'order_assigned',
            'order_id' => (string) $order->id,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'total_amount' => (string) $order->total_amount,
        ], 'order_assigned');
    }

    /**
     * إرسال إشعار فوري للعميل عند تغيير حالة الطلب
     */
    public function notifyCustomerOrderStatus(Order $order, string $newStatus): void
    {
        $user = $order->user;
        if (!$user) {
            return;
        }

        $statusMessages = [
            'processing' => [
                'title' => 'طلبك قيد التجهيز الآن',
                'body' => "طلبك #{$order->id} قيد التجهيز حالياً في المتجر.",
            ],
            'arrived_store' => [
                'title' => 'الكابتن وصل إلى المتجر',
                'body' => "وصل الكابتن إلى المتجر لاستلام مشترياتك في الطلب #{$order->id}.",
            ],
            'on_the_way' => [
                'title' => 'طلبك في الطريق للتسليم',
                'body' => "الكابتن استلم طلبك #{$order->id} وهو الآن في طريقه إليك.",
            ],
            'completed' => [
                'title' => 'تم تسليم طلبك بنجاح',
                'body' => "تم تسليم الطلب #{$order->id} بنجاح. شكراً لتسوقك من متجر تسويق!",
            ],
            'cancelled' => [
                'title' => 'تم إلغاء الطلب',
                'body' => "تم إلغاء طلبك #{$order->id}. يمكنك مراجعة الدعم الفني لأي استفسار.",
            ],
        ];

        if (!isset($statusMessages[$newStatus])) {
            return;
        }

        $info = $statusMessages[$newStatus];

        $this->fcm->sendToUser($user, $info['title'], $info['body'], [
            'type' => 'order_status_update',
            'order_id' => (string) $order->id,
            'status' => $newStatus,
        ], 'order_status');
    }

    /**
     * إشعار العميل عند إنشاء الطلب لأول مرة
     */
    public function notifyCustomerOrderCreated(Order $order): void
    {
        $user = $order->user;
        if (!$user) {
            return;
        }

        $title = "تم استلام طلبك بنجاح";
        $body = "تم تسجيل طلبك رقم #{$order->id} بمبلغ " . number_format($order->total_amount, 2) . " ريال وجاري مراجعته.";

        $this->fcm->sendToUser($user, $title, $body, [
            'type' => 'order_created',
            'order_id' => (string) $order->id,
            'total_amount' => (string) $order->total_amount,
        ], 'order_created');
    }

    /**
     * إشعار الكابتن عند اعتماد سداد المديونية
     */
    public function notifyCaptainPaymentApproved(CaptainPayment $payment): void
    {
        $captain = $payment->captain;
        if (!$captain) {
            return;
        }

        $title = "تم اعتماد دفعة السداد";
        $body = "تمت الموافقة على سداد مبلغ " . number_format($payment->amount, 2) . " ريال بنجاح وخصمها من مديونيتك.";

        $this->fcm->sendToUser($captain, $title, $body, [
            'type' => 'payment_approved',
            'payment_id' => (string) $payment->id,
            'amount' => (string) $payment->amount,
        ], 'payment');
    }
}
