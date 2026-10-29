<?php

use App\Http\Controllers\Admin\AdminPanelController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\PaymentMethodController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminPanelController::class, 'dashboard'])->name('admin.dashboard');
    
    // User & Captain Management
    Route::get('/users', [AdminPanelController::class, 'users'])->name('admin.users');
    Route::post('/users/store', [AdminPanelController::class, 'storeUser'])->name('admin.users.store');
    Route::post('/users/{user}/update', [AdminPanelController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/users/{user}/role', [AdminPanelController::class, 'updateUserRole'])->name('admin.users.updateRole');
    Route::post('/users/{user}/status', [AdminPanelController::class, 'toggleUserStatus'])->name('admin.users.toggleStatus');
    Route::post('/users/{user}/delete', [AdminPanelController::class, 'deleteUser'])->name('admin.users.delete');
    Route::get('/captains', [AdminPanelController::class, 'captains'])->name('admin.captains');
    Route::get('/captains/{captain}', [AdminPanelController::class, 'captainDetails'])->name('admin.captains.show');
    Route::post('/captain-payments/{captainPayment}/approve', [AdminPanelController::class, 'approveCaptainPayment'])->name('admin.captain-payments.approve');
    
    // Categories Management
    Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories');
    Route::post('/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::post('/categories/{category}/update', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::post('/categories/{category}/delete', [CategoryController::class, 'destroy'])->name('admin.categories.delete');

    // Products Management
    Route::get('/products', [AdminPanelController::class, 'products'])->name('admin.products');
    Route::post('/products', [ProductController::class, 'storeFromWeb'])->name('admin.products.store');
    Route::post('/products/{product}/update', [ProductController::class, 'updateFromWeb'])->name('admin.products.update');
    Route::post('/products/{product}/delete', [ProductController::class, 'deleteFromWeb'])->name('admin.products.delete');
    
    // Orders & Captain Assignment
    Route::get('/orders', [AdminPanelController::class, 'orders'])->name('admin.orders');
    Route::get('/carts', [AdminPanelController::class, 'carts'])->name('admin.carts');
    Route::get('/favorites', [AdminPanelController::class, 'favorites'])->name('admin.favorites');
    Route::get('/orders/{order}', [OrderController::class, 'showFromWeb'])->name('admin.orders.show');
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatusFromWeb'])->name('admin.orders.updateStatus');
    Route::post('/orders/{order}/paid', [OrderController::class, 'markPaidFromWeb'])->name('admin.orders.markPaid');
    Route::post('/orders/{order}/assign', [AdminPanelController::class, 'assignCaptain'])->name('admin.orders.assignCaptain');
    
    // Sliders
    Route::get('/sliders', [SliderController::class, 'index'])->name('admin.sliders');
    Route::post('/sliders', [SliderController::class, 'store'])->name('admin.sliders.store');
    Route::post('/sliders/{slider}/toggle', [SliderController::class, 'toggle'])->name('admin.sliders.toggle');
    Route::post('/sliders/{slider}/delete', [SliderController::class, 'destroy'])->name('admin.sliders.delete');

    // Settings
    Route::get('/settings', [AdminPanelController::class, 'settings'])->name('admin.settings');
    Route::post('/settings', [SettingsController::class, 'updateFromWeb'])->name('admin.settings.update');
    Route::post('/settings/notification-image', [SettingsController::class, 'updateNotificationImageFromWeb'])->name('admin.settings.notification_image');

    // Payment methods
    Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->name('admin.payment-methods');
    Route::post('/payment-methods', [PaymentMethodController::class, 'store'])->name('admin.payment-methods.store');
    Route::post('/payment-methods/{paymentMethod}/update', [PaymentMethodController::class, 'update'])->name('admin.payment-methods.update');
    Route::post('/payment-methods/{paymentMethod}/toggle', [PaymentMethodController::class, 'toggle'])->name('admin.payment-methods.toggle');
    Route::post('/payment-methods/{paymentMethod}/delete', [PaymentMethodController::class, 'destroy'])->name('admin.payment-methods.delete');
});
