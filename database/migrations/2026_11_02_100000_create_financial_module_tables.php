<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add financial fields to stores
        if (Schema::hasTable('stores')) {
            Schema::table('stores', function (Blueprint $table) {
                if (!Schema::hasColumn('stores', 'commission_rate')) {
                    $table->decimal('commission_rate', 5, 2)->default(10.00)->after('rating');
                }
                if (!Schema::hasColumn('stores', 'bank_name')) {
                    $table->string('bank_name')->nullable()->after('phone');
                }
                if (!Schema::hasColumn('stores', 'account_number')) {
                    $table->string('account_number')->nullable()->after('bank_name');
                }
                if (!Schema::hasColumn('stores', 'iban')) {
                    $table->string('iban')->nullable()->after('account_number');
                }
                if (!Schema::hasColumn('stores', 'tax_number')) {
                    $table->string('tax_number')->nullable()->after('iban');
                }
            });
        }

        // 2. Add store_id & financial breakdown fields to orders if missing
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'store_id')) {
                    $table->foreignId('store_id')->nullable()->after('user_id')->constrained('stores')->nullOnDelete();
                }
                if (!Schema::hasColumn('orders', 'store_amount')) {
                    $table->decimal('store_amount', 12, 2)->default(0)->after('total_amount');
                }
                if (!Schema::hasColumn('orders', 'app_commission')) {
                    $table->decimal('app_commission', 12, 2)->default(0)->after('store_amount');
                }
            });
        }

        // 3. Create employees table
        if (!Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('job_title')->default('موظف');
                $table->decimal('basic_salary', 12, 2)->default(0);
                $table->decimal('allowances', 12, 2)->default(0);
                $table->date('hire_date')->nullable();
                $table->string('national_id')->nullable();
                $table->string('status')->default('active'); // active, inactive, on_leave
                $table->text('notes')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 4. Create salaries table
        if (!Schema::hasTable('salaries')) {
            Schema::create('salaries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->string('month', 7); // YYYY-MM
                $table->decimal('basic_salary', 12, 2)->default(0);
                $table->decimal('allowances', 12, 2)->default(0);
                $table->decimal('bonus', 12, 2)->default(0);
                $table->decimal('deductions', 12, 2)->default(0);
                $table->decimal('net_salary', 12, 2)->default(0);
                $table->decimal('paid_amount', 12, 2)->default(0);
                $table->string('status')->default('pending'); // pending, partial, paid
                $table->date('payment_date')->nullable();
                $table->string('payment_method')->default('cash');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 5. Create store_invoices table
        if (!Schema::hasTable('store_invoices')) {
            Schema::create('store_invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->unique();
                $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
                $table->date('from_date');
                $table->date('to_date');
                $table->integer('total_orders_count')->default(0);
                $table->decimal('total_orders_amount', 12, 2)->default(0);
                $table->decimal('commission_rate', 5, 2)->default(10.00);
                $table->decimal('commission_amount', 12, 2)->default(0);
                $table->decimal('net_amount', 12, 2)->default(0);
                $table->decimal('paid_amount', 12, 2)->default(0);
                $table->string('status')->default('unpaid'); // unpaid, partial, paid
                $table->date('payment_date')->nullable();
                $table->string('payment_method')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 6. Create financial_transactions table
        if (!Schema::hasTable('financial_transactions')) {
            Schema::create('financial_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_number')->unique();
                $table->string('type'); // receipt (قبض), payment (صرف), adjustment (تسوية/قيد)
                $table->string('party_type'); // store, captain, employee, general
                $table->unsignedBigInteger('party_id')->nullable();
                $table->string('party_name')->nullable();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('direction'); // debit (مدين), credit (دائن)
                $table->string('category')->nullable(); // sales, commission, salary, advance, bonus, deduction, cash_settlement, expense, adjustment
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('payment_method')->default('cash'); // cash, bank_transfer, check, wallet
                $table->string('reference_number')->nullable();
                $table->string('attachment')->nullable();
                $table->date('transaction_date');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                
                $table->index(['party_type', 'party_id']);
                $table->index('transaction_date');
                $table->index('type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('store_invoices');
        Schema::dropIfExists('salaries');
        Schema::dropIfExists('employees');

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'store_id')) {
                    $table->dropForeign(['store_id']);
                    $table->dropColumn(['store_id', 'store_amount', 'app_commission']);
                }
            });
        }

        if (Schema::hasTable('stores')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->dropColumn(['commission_rate', 'bank_name', 'account_number', 'iban', 'tax_number']);
            });
        }
    }
};
