<?php

use App\Http\Controllers\Admin\AdminPanelController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\CouponController;
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
    Route::post('/captains/{captain}/update', [AdminPanelController::class, 'updateCaptain'])->name('admin.captains.update');
    Route::post('/captain-payments/{captainPayment}/approve', [AdminPanelController::class, 'approveCaptainPayment'])->name('admin.captain-payments.approve');
    
    // Categories Management
    Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories');
    Route::post('/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::post('/categories/{category}/update', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::post('/categories/{category}/delete', [CategoryController::class, 'destroy'])->name('admin.categories.delete');

    // Stores & Restaurants Management
    Route::get('/stores', [StoreController::class, 'index'])->name('admin.stores');
    Route::post('/stores', [StoreController::class, 'store'])->name('admin.stores.store');
    Route::get('/stores/{store}', [StoreController::class, 'show'])->name('admin.stores.show');
    Route::post('/stores/{store}/update', [StoreController::class, 'update'])->name('admin.stores.update');
    Route::post('/stores/{store}/delete', [StoreController::class, 'destroy'])->name('admin.stores.delete');
    Route::post('/stores/{store}/toggle-open', [StoreController::class, 'toggleOpen'])->name('admin.stores.toggle-open');
    Route::post('/stores/{store}/toggle-featured', [StoreController::class, 'toggleFeatured'])->name('admin.stores.toggle-featured');

    // Store Menu Sections Management
    Route::post('/stores/{store}/sections', [StoreController::class, 'storeSection'])->name('admin.stores.sections.store');
    Route::post('/store-sections/{section}/update', [StoreController::class, 'updateSection'])->name('admin.stores.sections.update');
    Route::post('/store-sections/{section}/delete', [StoreController::class, 'deleteSection'])->name('admin.stores.sections.delete');

    // Store Meals & Products Management
    Route::post('/stores/{store}/products', [StoreController::class, 'storeProduct'])->name('admin.stores.products.store');
    Route::post('/store-products/{product}/update', [StoreController::class, 'updateProduct'])->name('admin.stores.products.update');
    Route::post('/store-products/{product}/delete', [StoreController::class, 'deleteProduct'])->name('admin.stores.products.delete');

    // Legacy Products Redirect -> Redirect to Stores
    Route::get('/products', function () {
        return redirect()->route('admin.stores')->with('info', 'إدارة الوجبات والمنتجات أصبحت تابعة لكل متجر/مطعم؛ يرجى اختيار المتجر لإدارة قائمته ووجباته.');
    })->name('admin.products');
    
    // Orders & Captain Assignment
    Route::get('/orders', [AdminPanelController::class, 'orders'])->name('admin.orders');
    Route::get('/carts', [AdminPanelController::class, 'carts'])->name('admin.carts');
    Route::get('/favorites', [AdminPanelController::class, 'favorites'])->name('admin.favorites');
    Route::get('/orders/{order}', [OrderController::class, 'showFromWeb'])->name('admin.orders.show');
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatusFromWeb'])->name('admin.orders.updateStatus');
    Route::post('/orders/{order}/paid', [OrderController::class, 'markPaidFromWeb'])->name('admin.orders.markPaid');
    Route::post('/orders/{order}/assign', [AdminPanelController::class, 'assignCaptain'])->name('admin.orders.assignCaptain');
    
    // Coupons & Promo Codes
    Route::get('/coupons', [CouponController::class, 'index'])->name('admin.coupons');
    Route::post('/coupons', [CouponController::class, 'store'])->name('admin.coupons.store');
    Route::post('/coupons/{coupon}/update', [CouponController::class, 'update'])->name('admin.coupons.update');
    Route::post('/coupons/{coupon}/toggle', [CouponController::class, 'toggle'])->name('admin.coupons.toggle');
    Route::post('/coupons/{coupon}/delete', [CouponController::class, 'destroy'])->name('admin.coupons.delete');

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

    // ==========================================
    // Financial & Accounting Management System
    // ==========================================
    Route::prefix('finance')->group(function () {
        // Financial Overview
        Route::get('/', [FinanceController::class, 'overview'])->name('admin.finance.overview');
        
        // Restaurants & Stores Financials
        Route::get('/stores', [FinanceController::class, 'stores'])->name('admin.finance.stores');
        Route::get('/stores/{store}/statement', [FinanceController::class, 'storeStatement'])->name('admin.finance.stores.statement');
        Route::post('/stores/{store}/financials', [FinanceController::class, 'updateStoreFinancials'])->name('admin.finance.stores.updateFinancials');
        Route::post('/stores/{store}/invoices/generate', [FinanceController::class, 'generateStoreInvoice'])->name('admin.finance.stores.generateInvoice');
        
        // Store Invoices & Settlements
        Route::get('/invoices', [FinanceController::class, 'storeInvoices'])->name('admin.finance.invoices');
        Route::post('/invoices/{invoice}/status', [FinanceController::class, 'updateInvoiceStatus'])->name('admin.finance.invoices.updateStatus');
        Route::get('/invoices/{invoice}/print', [FinanceController::class, 'printStoreInvoice'])->name('admin.finance.invoices.print');

        // Captains Financial Accounts & Debt
        Route::get('/captains', [FinanceController::class, 'captains'])->name('admin.finance.captains');
        Route::get('/captains/{captain}/statement', [FinanceController::class, 'captainStatement'])->name('admin.finance.captains.statement');

        // Employees & Payroll
        Route::get('/employees', [FinanceController::class, 'employees'])->name('admin.finance.employees');
        Route::post('/employees', [FinanceController::class, 'storeEmployee'])->name('admin.finance.employees.store');
        Route::post('/employees/{employee}/update', [FinanceController::class, 'updateEmployee'])->name('admin.finance.employees.update');
        Route::post('/employees/{employee}/delete', [FinanceController::class, 'deleteEmployee'])->name('admin.finance.employees.delete');
        Route::get('/employees/{employee}/statement', [FinanceController::class, 'employeeStatement'])->name('admin.finance.employees.statement');

        // Monthly Payroll
        Route::get('/payroll', [FinanceController::class, 'payroll'])->name('admin.finance.payroll');
        Route::post('/payroll/generate', [FinanceController::class, 'generatePayroll'])->name('admin.finance.payroll.generate');
        Route::post('/payroll/{salary}/pay', [FinanceController::class, 'paySalary'])->name('admin.finance.payroll.pay');

        // Comprehensive Accounting Transactions (Create, Edit, Delete, Print)
        Route::get('/transactions', [FinanceController::class, 'transactions'])->name('admin.finance.transactions');
        Route::post('/transactions', [FinanceController::class, 'storeTransaction'])->name('admin.finance.transactions.store');
        Route::post('/transactions/{transaction}/update', [FinanceController::class, 'updateTransaction'])->name('admin.finance.transactions.update');
        Route::post('/transactions/{transaction}/delete', [FinanceController::class, 'deleteTransaction'])->name('admin.finance.transactions.delete');
        Route::get('/transactions/{transaction}/print', [FinanceController::class, 'printTransaction'])->name('admin.finance.transactions.print');

        // Financial Reports
        Route::get('/reports', [FinanceController::class, 'reports'])->name('admin.finance.reports');
    });
});
