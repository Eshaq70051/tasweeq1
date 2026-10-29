<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('account_number')->nullable();
            $table->string('icon')->nullable();
            $table->string('background_color', 7)->default('#FFFFFF');
            $table->string('text_color', 7)->default('#1A1C1E');
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_receipt')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
