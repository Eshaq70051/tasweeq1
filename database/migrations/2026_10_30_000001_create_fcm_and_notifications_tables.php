<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. إضافة عمود fcm_token لجدول المستخدمين للوصول السريع
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'fcm_token')) {
                $table->text('fcm_token')->nullable()->after('avatar');
            }
        });

        // 2. جدول الرموز التعريفية للأجهزة (يدعم تسجيل الدخول من أكثر من جهاز)
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('token');
            $table->string('device_type', 20)->default('android'); // android, ios, web
            $table->timestamps();

            $table->index(['user_id', 'device_type']);
        });

        // 3. جدول حفظ سجل الإشعارات للمستخدم والكابتن
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('type', 50)->default('general'); // order_assigned, order_status, payment, general
            $table->json('data')->nullable(); // e.g. {"order_id": 123}
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
        Schema::dropIfExists('device_tokens');
        
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'fcm_token')) {
                $table->dropColumn('fcm_token');
            }
        });
    }
};
