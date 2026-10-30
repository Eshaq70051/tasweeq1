<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\CaptainController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\CouponController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/auth/google', [AuthController::class, 'googleLogin']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}/products', [CategoryController::class, 'products']);
Route::get('/sliders', [ProductController::class, 'getSliders']);
Route::get('/settings', [SettingsController::class, 'index']);
Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'index']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'changePassword']);
    
    // Cart & Favorites
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);
    
    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::post('/orders/{id}/rating', [OrderController::class, 'rate']);
    
    // FCM & Push Notifications
    Route::post('/fcm/token', [NotificationController::class, 'saveToken']);
    Route::delete('/fcm/token', [NotificationController::class, 'removeToken']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Captain API
    Route::middleware('captain')->group(function () {
        Route::get('/captain/dashboard', [CaptainController::class, 'dashboard']);
        Route::get('/captain/wallet', [CaptainController::class, 'wallet']);
        Route::get('/captain/report', [CaptainController::class, 'report']);
        Route::post('/captain/payments', [CaptainController::class, 'requestPayment']);
        Route::post('/captain/toggle-status', [CaptainController::class, 'toggleStatus']);
        Route::post('/captain/orders/{id}/arrive-store', [CaptainController::class, 'arriveAtStore']);
        Route::post('/captain/orders/{id}/accept', [CaptainController::class, 'acceptOrder']);
        Route::post('/captain/orders/{id}/deliver', [CaptainController::class, 'deliverOrder']);
    });
});
