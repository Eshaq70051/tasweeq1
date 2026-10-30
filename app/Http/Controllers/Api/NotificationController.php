<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * حفظ أو تحديث رمز جهاز المستخدم (FCM Token)
     */
    public function saveToken(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'device_type' => 'nullable|string|in:android,ios,web',
        ]);

        $user = Auth::user();

        // تحديث في جدول المستخدمين
        $user->fcm_token = $validated['token'];
        $user->save();

        // وتحديث في جدول الأجهزة المتعددة
        DeviceToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'token' => $validated['token'],
            ],
            [
                'device_type' => $validated['device_type'] ?? 'android',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ رمز الجهاز بنجاح',
        ]);
    }

    /**
     * حذف رمز الجهاز عند تسجيل الخروج
     */
    public function removeToken(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        $user = Auth::user();

        DeviceToken::where('user_id', $user->id)
            ->where('token', $validated['token'])
            ->delete();

        if ($user->fcm_token === $validated['token']) {
            $user->fcm_token = null;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء تسجيل رمز الجهاز بنجاح',
        ]);
    }

    /**
     * جلب قائمة إشعارات المستخدم الحالية
     */
    public function index(Request $request)
    {
        $notifications = UserNotification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * عدد الإشعارات غير المقروءة
     */
    public function unreadCount()
    {
        $count = UserNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count,
        ]);
    }

    /**
     * تعيين إشعار كمقروء
     */
    public function markAsRead($id)
    {
        $notification = UserNotification::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'الإشعار غير موجود'], 404);
        }

        $notification->is_read = true;
        $notification->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تعيين الإشعار كمقروء',
        ]);
    }

    /**
     * تعيين كافة الإشعارات كمقروءة
     */
    public function markAllAsRead()
    {
        UserNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'تم تعيين كافة الإشعارات كمقروءة',
        ]);
    }
}
