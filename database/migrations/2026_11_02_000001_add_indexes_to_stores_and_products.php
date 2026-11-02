<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->index('category_id');
            $table->index(['category_id', 'is_open', 'is_featured']);
        });

        Schema::table('store_menu_sections', function (Blueprint $table) {
            $table->index('store_id');
            $table->index(['store_id', 'sort_order']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('store_id');
            $table->index('store_menu_section_id');
            $table->index(['store_id', 'is_available']);
            $table->index(['store_menu_section_id', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['store_id']);
            $table->dropIndex(['store_menu_section_id']);
            $table->dropIndex(['store_id', 'is_available']);
            $table->dropIndex(['store_menu_section_id', 'is_available']);
        });

        Schema::table('store_menu_sections', function (Blueprint $table) {
            $table->dropIndex(['store_id']);
            $table->dropIndex(['store_id', 'sort_order']);
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['category_id', 'is_open', 'is_featured']);
        });
    }
};
