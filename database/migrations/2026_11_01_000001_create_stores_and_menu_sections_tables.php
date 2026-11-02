<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stores')) {
            Schema::create('stores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');
                $table->string('name');
                $table->string('name_en')->nullable();
                $table->string('logo')->nullable();
                $table->string('banner')->nullable();
                $table->decimal('rating', 3, 2)->default(4.5);
                $table->string('distance')->default('1.5 كم');
                $table->string('delivery_time')->default('30 - 45 دقيقة');
                $table->boolean('is_open')->default(true);
                $table->string('address')->nullable();
                $table->string('phone')->nullable();
                $table->boolean('is_featured')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('store_menu_sections')) {
            Schema::create('store_menu_sections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
                $table->string('name');
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'store_id')) {
                    $table->foreignId('store_id')->nullable()->after('category_id')->constrained('stores')->onDelete('cascade');
                }
                if (!Schema::hasColumn('products', 'store_menu_section_id')) {
                    $table->foreignId('store_menu_section_id')->nullable()->after('store_id')->constrained('store_menu_sections')->onDelete('set null');
                }
                if (!Schema::hasColumn('products', 'is_available')) {
                    $table->boolean('is_available')->default(true)->after('stock');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'store_menu_section_id')) {
                    $table->dropForeign(['store_menu_section_id']);
                    $table->dropColumn('store_menu_section_id');
                }
                if (Schema::hasColumn('products', 'store_id')) {
                    $table->dropForeign(['store_id']);
                    $table->dropColumn('store_id');
                }
                if (Schema::hasColumn('products', 'is_available')) {
                    $table->dropColumn('is_available');
                }
            });
        }
        Schema::dropIfExists('store_menu_sections');
        Schema::dropIfExists('stores');
    }
};
