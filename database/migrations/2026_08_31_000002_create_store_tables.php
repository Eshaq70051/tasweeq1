<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->timestamps();
            });
        } else {
            if (!Schema::hasColumn('categories', 'image')) {
                Schema::table('categories', function (Blueprint $table) {
                    $table->string('image')->nullable()->after('description');
                });
            }
        }

        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
                $table->string('name');
                $table->decimal('price', 10, 2);
                $table->string('currency', 10)->default('SAR');
                $table->text('details')->nullable();
                $table->string('image')->nullable();
                $table->decimal('old_price', 10, 2)->nullable();
                $table->integer('stock')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cart')) {
            Schema::create('cart', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->integer('quantity')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('customer_name');
                $table->string('customer_phone');
                $table->string('customer_email')->nullable();
                $table->text('customer_address');
                $table->decimal('total_amount', 10, 2)->default(0);
                $table->string('payment_method')->default('cash');
                $table->string('transfer_number')->nullable();
                $table->string('receipt_image')->nullable();
                $table->text('notes')->nullable();
                $table->string('status')->default('pending');
                $table->boolean('is_paid')->default(false);
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
                $table->string('product_name');
                $table->decimal('price', 10, 2);
                $table->integer('quantity');
                $table->decimal('subtotal', 10, 2);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('marketing_sliders')) {
            Schema::create('marketing_sliders', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->string('image_path');
                $table->string('link')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_sliders');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
