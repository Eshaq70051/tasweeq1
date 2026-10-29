<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendOrderConfirmation(Order $order)
    {
        try {
            $user = $order->user;
            
            // Send email
            Mail::send('emails.order-confirmation', [
                'order' => $order,
                'user' => $user
            ], function ($message) use ($user, $order) {
                $message->to($user->email)
                       ->subject('تأكيد الطلب #' . $order->id);
            });
            
            // Send SMS (if configured)
            // $this->sendSMS($order->customer_phone, 'Order confirmed');
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Notification Error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendOrderStatusUpdate(Order $order)
    {
        try {
            // Send email notification for status update
            Mail::send('emails.order-status', [
                'order' => $order
            ], function ($message) use ($order) {
                $message->to($order->customer_email ?? $order->user->email)
                       ->subject('تحديث حالة الطلب #' . $order->id);
            });
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Status Update Notification Error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendWelcomeEmail(User $user)
    {
        try {
            Mail::send('emails.welcome', [
                'user' => $user
            ], function ($message) use ($user) {
                $message->to($user->email)
                       ->subject('مرحباً بك في تسويق');
            });
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Welcome Email Error: ' . $e->getMessage());
            return false;
        }
    }
}
