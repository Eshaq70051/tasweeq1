<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. جدول الكوبونات وقسائم الخصم
        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->enum('type', ['percentage', 'fixed'])->default('percentage'); // نسبة مئوية أو مبلغ ثابت
                $table->decimal('value', 10, 2); // قيمة الخصم (مثلاً 10% أو 500 ريال)
                $table->decimal('min_order_amount', 10, 2)->nullable()->default(0); // الحد الأدنى للطلب لتفعيل الكوبون
                $table->decimal('max_discount_amount', 10, 2)->nullable(); // سقف الخصم في حال النسبة المئوية
                $table->integer('usage_limit')->nullable(); // إجمالي مرات الاستخدام المسموحة للكوبون
                $table->integer('usage_limit_per_user')->default(1); // الحد الأقصى لكل عميل
                $table->integer('used_count')->default(0); // عدد مرات الاستخدام الفعلي
                $table->timestamp('starts_at')->nullable(); // تاريخ بدء الصلاحية
                $table->timestamp('expires_at')->nullable(); // تاريخ انتهاء الصلاحية
                $table->boolean('is_active')->default(true); // حالة التفعيل
                $table->string('description')->nullable(); // وصف ترويجي
                $table->timestamps();

                $table->index(['code', 'is_active']);
            });
        }

        // 2. تحديث جدول الطلبات لإضافة بيانات الكوبون والخصم
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'coupon_id')) {
                $table->foreignId('coupon_id')->nullable()->after('user_id')->constrained('coupons')->onDelete('set null');
            }
            if (!Schema::hasColumn('orders', 'coupon_code')) {
                $table->string('coupon_code')->nullable()->after('coupon_id');
            }
            if (!Schema::hasColumn('orders', 'subtotal_amount')) {
                $table->decimal('subtotal_amount', 10, 2)->default(0)->after('longitude');
            }
            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('subtotal_amount');
            }
        });

        // 3. جدول سجل استخدام الكوبونات
        if (!Schema::hasTable('coupon_usages')) {
            Schema::create('coupon_usages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained('coupons')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
                $table->decimal('discount_amount', 10, 2);
                $table->timestamp('created_at')->useCurrent();

                $table->index(['coupon_id', 'user_id']);
            });
        }

        // 4. تحديث جدول المنتجات لإضافة حد تنبيه المخزون المنخفض
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'low_stock_threshold')) {
                $table->integer('low_stock_threshold')->default(5)->after('stock');
            }
        });

        // 5. جدول دفتر أستاذ الحسابات والمحفظة المالية للكباتن (Captain Ledger)
        if (!Schema::hasTable('captain_ledger_entries')) {
            Schema::create('captain_ledger_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('captain_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
                $table->foreignId('captain_payment_id')->nullable()->constrained('captain_payments')->onDelete('set null');
                $table->enum('type', ['cash_collected', 'commission_earned', 'payment_settlement', 'adjustment']);
                $table->enum('direction', ['debit', 'credit']); // debit: على الكابتن، credit: لصالح الكابتن
                $table->decimal('amount', 10, 2);
                $table->decimal('balance_after', 10, 2)->default(0); // صافي رصيد الذمة بعد العملية
                $table->string('description');
                $table->timestamps();

                $table->index(['captain_id', 'type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('captain_ledger_entries');
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'low_stock_threshold')) {
                $table->dropColumn('low_stock_threshold');
            }
        });
        Schema::dropIfExists('coupon_usages');
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'coupon_id')) {
                $table->dropForeign(['coupon_id']);
                $table->dropColumn(['coupon_id', 'coupon_code', 'subtotal_amount', 'discount_amount']);
            }
        });
        Schema::dropIfExists('coupons');
    }
};
