<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\FinancialTransaction;
use App\Models\Order;
use App\Models\Salary;
use App\Models\Store;
use App\Models\StoreInvoice;
use App\Models\User;
use App\Services\CaptainWalletService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FinanceController extends Controller
{
    /**
     * 1. الواجهة المالية الرئيسية (Financial Overview)
     */
    public function overview(Request $request)
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->format('Y-m');

        // إجمالي مبيعات الطلبات المكتملة
        $completedOrders = Order::where('status', 'completed');
        $totalSales = (float) (clone $completedOrders)->sum('total_amount');
        $thisMonthSales = (float) (clone $completedOrders)->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)->sum('total_amount');

        // عمولات التطبيق التقديرية (10% افتراضي أو من تفاصيل الطلب)
        $totalAppCommission = (float) (clone $completedOrders)->sum('app_commission');
        if ($totalAppCommission <= 0 && $totalSales > 0) {
            $totalAppCommission = round($totalSales * 0.10, 2);
        }

        // عمولات الكباتن المكتسبة
        $totalCaptainCommissions = (float) (clone $completedOrders)->sum('captain_commission');

        // الكاش المحصل لدى الكباتن
        $totalCashCollected = (float) (clone $completedOrders)->where('payment_method', 'cash')->sum('total_amount');

        // مديونيات الكباتن الإجمالية
        $captains = User::where('role', 'captain')->get();
        $totalCaptainsDebt = 0;
        $walletService = app(CaptainWalletService::class);
        foreach ($captains as $captain) {
            $summary = $walletService->getCaptainFinancialSummary($captain);
            $totalCaptainsDebt += $summary['outstanding_debt'];
        }

        // إجمالي المستحقات للمطاعم
        $stores = Store::all();
        $totalStoresNetDue = 0;
        $totalStoresDebit = 0;
        $totalStoresCredit = 0;
        foreach ($stores as $store) {
            $summary = $store->getFinancialSummary();
            $totalStoresDebit += $summary['total_debit'];
            $totalStoresCredit += $summary['total_credit'];
            if ($summary['balance'] > 0) {
                $totalStoresNetDue += $summary['balance'];
            }
        }

        // إجمالي الرواتب المصروفة هذا الشهر
        $paidSalariesThisMonth = (float) Salary::where('month', $thisMonth)->where('status', 'paid')->sum('paid_amount');
        $pendingSalariesThisMonth = (float) Salary::where('month', $thisMonth)->where('status', '!=', 'paid')->sum('net_salary');

        // السندات وحركات الصندوق
        $totalReceipts = (float) FinancialTransaction::where('type', 'receipt')->sum('amount');
        $totalPayments = (float) FinancialTransaction::where('type', 'payment')->sum('amount');
        $netCashFlow = $totalReceipts - $totalPayments;

        // آخر 10 عمليات محاسبية
        $latestTransactions = FinancialTransaction::with(['creator'])->latest('transaction_date')->latest('id')->take(10)->get();

        // إحصائيات المطاعم والكباتن الأكثر نشاطاً
        $topStores = Store::withCount(['orders' => fn($q) => $q->where('status', 'completed')])
            ->orderBy('orders_count', 'desc')
            ->take(5)
            ->get();

        $stats = [
            'total_sales' => $totalSales,
            'this_month_sales' => $thisMonthSales,
            'app_commission' => $totalAppCommission,
            'captain_commissions' => $totalCaptainCommissions,
            'captains_cash_collected' => $totalCashCollected,
            'captains_outstanding_debt' => $totalCaptainsDebt,
            'stores_net_due' => $totalStoresNetDue,
            'stores_credit' => $totalStoresCredit,
            'stores_debit' => $totalStoresDebit,
            'paid_salaries' => $paidSalariesThisMonth,
            'pending_salaries' => $pendingSalariesThisMonth,
            'total_receipts' => $totalReceipts,
            'total_payments' => $totalPayments,
            'net_cash_flow' => $netCashFlow,
            'active_employees' => Employee::where('status', 'active')->count(),
            'active_captains' => $captains->where('status', 'active')->count(),
            'total_stores' => $stores->count(),
        ];

        return view('admin.finance.overview', compact('stats', 'latestTransactions', 'topStores'));
    }

    /**
     * 2. حسابات المطاعم والمتاجر (Restaurants Financial Accounts)
     */
    public function stores(Request $request)
    {
        $query = Store::query();

        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%")
                  ->orWhere('address', 'like', "%{$keyword}%");
            });
        }

        $allStores = $query->orderBy('name')->paginate(20)->withQueryString();

        // تجميع الحساب المالي لكل مطعم
        $storesFinancials = [];
        $totals = [
            'sales' => 0,
            'commission' => 0,
            'debit' => 0,
            'credit' => 0,
            'balance' => 0,
        ];

        foreach ($allStores as $store) {
            $summary = $store->getFinancialSummary();
            $storesFinancials[$store->id] = $summary;

            $totals['sales'] += $summary['total_sales'];
            $totals['commission'] += $summary['app_commission'];
            $totals['debit'] += $summary['total_debit'];
            $totals['credit'] += $summary['total_credit'];
            $totals['balance'] += $summary['balance'];
        }

        return view('admin.finance.stores', compact('allStores', 'storesFinancials', 'totals'));
    }

    /**
     * كشف حساب تفصيلي لمطعم معين
     */
    public function storeStatement(Store $store)
    {
        $summary = $store->getFinancialSummary();

        // الطلبات المكتملة
        $orders = Order::where(function ($q) use ($store) {
            $q->where('store_id', $store->id)
              ->orWhereHas('items.product', function ($itemQ) use ($store) {
                  $itemQ->where('store_id', $store->id);
              });
        })->latest()->paginate(25);

        // الحركات المالية والسندات المسجلة
        $transactions = FinancialTransaction::where('party_type', 'store')
            ->where('party_id', $store->id)
            ->latest('transaction_date')
            ->latest('id')
            ->get();

        // الفواتير والتسويات المسجلة
        $invoices = StoreInvoice::where('store_id', $store->id)->latest()->get();

        return view('admin.finance.store-statement', compact('store', 'summary', 'orders', 'transactions', 'invoices'));
    }

    /**
     * تحديث بيانات الحساب والعمولة للمطعم
     */
    public function updateStoreFinancials(Request $request, Store $store)
    {
        $validated = $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:100',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'iban' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:100',
        ]);

        $store->update($validated);

        return redirect()->back()->with('success', "تم تحديث البيانات المالية للمتجر '{$store->name}' بنجاح");
    }

    /**
     * قائمة فواتير وتسويات المطاعم
     */
    public function storeInvoices(Request $request)
    {
        $query = StoreInvoice::with('store');

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('from_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('to_date', '<=', $request->date_to);
        }

        $invoices = $query->latest('id')->paginate(20)->withQueryString();
        $stores = Store::orderBy('name')->get();

        $stats = [
            'total_invoices' => StoreInvoice::count(),
            'total_amount' => (float) StoreInvoice::sum('net_amount'),
            'paid_amount' => (float) StoreInvoice::sum('paid_amount'),
            'unpaid_count' => StoreInvoice::where('status', 'unpaid')->count(),
        ];

        return view('admin.finance.invoices', compact('invoices', 'stores', 'stats'));
    }

    /**
     * إنشاء فاتورة تسوية جديدة للمطعم
     */
    public function generateStoreInvoice(Request $request, Store $store)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'notes' => 'nullable|string',
        ]);

        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();

        // جلب الطلبات المكتملة في هذا النطاق
        $orders = Order::where(function ($q) use ($store) {
            $q->where('store_id', $store->id)
              ->orWhereHas('items.product', function ($itemQ) use ($store) {
                  $itemQ->where('store_id', $store->id);
              });
        })->where('status', 'completed')
          ->whereBetween('created_at', [$fromDate, $toDate])
          ->get();

        $ordersCount = $orders->count();
        $totalSales = (float) $orders->sum('total_amount');
        $rate = $store->commission_rate > 0 ? $store->commission_rate : 10.0;
        $commissionAmount = round(($totalSales * $rate) / 100, 2);
        $netAmount = max(0, $totalSales - $commissionAmount);

        $invoice = StoreInvoice::create([
            'invoice_number' => StoreInvoice::generateInvoiceNumber(),
            'store_id' => $store->id,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'total_orders_count' => $ordersCount,
            'total_orders_amount' => $totalSales,
            'commission_rate' => $rate,
            'commission_amount' => $commissionAmount,
            'net_amount' => $netAmount,
            'paid_amount' => 0,
            'status' => 'unpaid',
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', "تم إنشاء الفاتورة رقم {$invoice->invoice_number} بنجاح بإجمالي مستحق {$netAmount} ر.ي");
    }

    /**
     * تحديث حالة سداد الفاتورة
     */
    public function updateInvoiceStatus(Request $request, StoreInvoice $invoice)
    {
        $validated = $request->validate([
            'status' => 'required|in:unpaid,partial,paid',
            'paid_amount' => 'required|numeric|min:0',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
            'record_transaction' => 'nullable|boolean',
        ]);

        $prevPaid = $invoice->paid_amount;
        $invoice->update([
            'status' => $validated['status'],
            'paid_amount' => $validated['paid_amount'],
            'payment_date' => $validated['payment_date'] ?? now()->toDateString(),
            'payment_method' => $validated['payment_method'] ?? 'cash',
            'notes' => $validated['notes'] ?? $invoice->notes,
        ]);

        // إذا رغب المدير في تسجيل سند صرف فوري للمطعم
        $diffPaid = $validated['paid_amount'] - $prevPaid;
        if (!empty($request->record_transaction) && $diffPaid > 0) {
            FinancialTransaction::create([
                'transaction_number' => FinancialTransaction::generateTransactionNumber(),
                'type' => 'payment',
                'party_type' => 'store',
                'party_id' => $invoice->store_id,
                'party_name' => $invoice->store?->name,
                'amount' => $diffPaid,
                'direction' => 'debit',
                'category' => 'sales_settlement',
                'title' => "سداد فاتورة تسوية رقم {$invoice->invoice_number}",
                'description' => "دفعة مسددة للمتجر '{$invoice->store?->name}' للفاتورة {$invoice->invoice_number}",
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'transaction_date' => $validated['payment_date'] ?? now()->toDateString(),
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()->back()->with('success', "تم تحديث حالة الفاتورة {$invoice->invoice_number} بنجاح");
    }

    /**
     * طباعة فاتورة المطعم
     */
    public function printStoreInvoice(StoreInvoice $invoice)
    {
        $invoice->load('store');
        return view('admin.finance.invoice-print', compact('invoice'));
    }

    /**
     * 3. حسابات الكباتن والذمم المالية (Captains Accounts & Debt)
     */
    public function captains(Request $request)
    {
        $query = User::where('role', 'captain');

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $captains = $query->orderBy('name')->paginate(20)->withQueryString();

        $walletService = app(CaptainWalletService::class);
        $captainsFinancials = [];
        $totals = [
            'cash_collected' => 0,
            'earned_commission' => 0,
            'paid_debt' => 0,
            'outstanding_debt' => 0,
            'completed_orders' => 0,
        ];

        foreach ($captains as $captain) {
            $summary = $walletService->getCaptainFinancialSummary($captain);
            $captainsFinancials[$captain->id] = $summary;

            $totals['cash_collected'] += $summary['cash_collected'];
            $totals['earned_commission'] += $summary['earned_commission'];
            $totals['paid_debt'] += $summary['paid_debt'];
            $totals['outstanding_debt'] += $summary['outstanding_debt'];
            $totals['completed_orders'] += $summary['completed_orders_count'];
        }

        return view('admin.finance.captains', compact('captains', 'captainsFinancials', 'totals'));
    }

    /**
     * كشف حساب مالي تفصيلي للكابتن
     */
    public function captainStatement(User $captain)
    {
        abort_unless($captain->role === 'captain', 404);

        $walletService = app(CaptainWalletService::class);
        $summary = $walletService->getCaptainFinancialSummary($captain);

        // قيود دفتر الأستاذ الرسمية
        $ledgerEntries = $captain->captainLedgerEntries()->with(['order', 'payment'])->take(50)->get();

        // الحركات المالية العامة المرتبطة بالكابتن
        $transactions = FinancialTransaction::where('party_type', 'captain')
            ->where('party_id', $captain->id)
            ->latest('transaction_date')
            ->latest('id')
            ->get();

        // طلبات التوصيل المكتملة
        $orders = Order::where('assigned_captain_id', $captain->id)
            ->latest()
            ->take(30)
            ->get();

        // سجل مدفوعات وسدادات الكابتن
        $payments = \App\Models\CaptainPayment::where('captain_id', $captain->id)->latest()->get();

        return view('admin.finance.captain-statement', compact('captain', 'summary', 'ledgerEntries', 'transactions', 'orders', 'payments'));
    }

    /**
     * 4. حسابات الموظفين والرواتب (Employees & Payroll)
     */
    public function employees(Request $request)
    {
        $query = Employee::query();

        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%")
                  ->orWhere('job_title', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query->orderBy('name')->paginate(15)->withQueryString();

        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('status', 'active')->count(),
            'total_payroll' => (float) Employee::where('status', 'active')->sum(DB::raw('basic_salary + allowances')),
        ];

        return view('admin.finance.employees', compact('employees', 'stats'));
    }

    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'job_title' => 'required|string|max:100',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'hire_date' => 'nullable|date',
            'national_id' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive,on_leave',
            'notes' => 'nullable|string',
        ]);

        Employee::create($validated);

        return redirect()->back()->with('success', 'تمت إضافة الموظف بنجاح');
    }

    public function updateEmployee(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'job_title' => 'required|string|max:100',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'hire_date' => 'nullable|date',
            'national_id' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive,on_leave',
            'notes' => 'nullable|string',
        ]);

        $employee->update($validated);

        return redirect()->back()->with('success', 'تم تحديث بيانات الموظف بنجاح');
    }

    public function deleteEmployee(Employee $employee)
    {
        $employee->delete();
        return redirect()->back()->with('success', 'تم حذف الموظف بنجاح');
    }

    /**
     * مسير الرواتب الشهرية (Payroll)
     */
    public function payroll(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));

        $salaries = Salary::with('employee')
            ->where('month', $month)
            ->get();

        $stats = [
            'month' => $month,
            'total_due' => (float) $salaries->sum('net_salary'),
            'total_paid' => (float) $salaries->sum('paid_amount'),
            'remaining' => (float) $salaries->sum(fn($s) => $s->remaining_amount),
            'count' => $salaries->count(),
            'paid_count' => $salaries->where('status', 'paid')->count(),
            'pending_count' => $salaries->where('status', '!=', 'paid')->count(),
        ];

        return view('admin.finance.payroll', compact('salaries', 'month', 'stats'));
    }

    /**
     * توليد مسير رواتب الشهر تلقائياً
     */
    public function generatePayroll(Request $request)
    {
        $request->validate([
            'month' => 'required|string|regex:/^\d{4}-\d{2}$/',
        ]);

        $month = $request->month;
        $activeEmployees = Employee::where('status', 'active')->get();

        $createdCount = 0;
        foreach ($activeEmployees as $employee) {
            $exists = Salary::where('employee_id', $employee->id)->where('month', $month)->exists();
            if (!$exists) {
                Salary::create([
                    'employee_id' => $employee->id,
                    'month' => $month,
                    'basic_salary' => $employee->basic_salary,
                    'allowances' => $employee->allowances,
                    'bonus' => 0,
                    'deductions' => 0,
                    'paid_amount' => 0,
                    'status' => 'pending',
                ]);
                $createdCount++;
            }
        }

        return redirect()->back()->with('success', "تم توليد مسير رواتب شهر ({$month}) لعدد ({$createdCount}) موظف بنجاح");
    }

    /**
     * صرف الراتب لموظف
     */
    public function paySalary(Request $request, Salary $salary)
    {
        $validated = $request->validate([
            'paid_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'bonus' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $salary->bonus = $validated['bonus'] ?? $salary->bonus;
        $salary->deductions = $validated['deductions'] ?? $salary->deductions;
        $salary->paid_amount += (float) $validated['paid_amount'];
        $salary->payment_date = $validated['payment_date'];
        $salary->payment_method = $validated['payment_method'];
        $salary->notes = $validated['notes'];
        $salary->save();

        // توثيق عملية الصرف في جدول العمليات المحاسبية
        FinancialTransaction::create([
            'transaction_number' => FinancialTransaction::generateTransactionNumber(),
            'type' => 'payment',
            'party_type' => 'employee',
            'party_id' => $salary->employee_id,
            'party_name' => $salary->employee?->name,
            'amount' => $validated['paid_amount'],
            'direction' => 'debit',
            'category' => 'salary',
            'title' => "صرف راتب شهر {$salary->month} للموظف {$salary->employee?->name}",
            'description' => "صرف راتب / دفعة بمبلغ {$validated['paid_amount']} عن شهر {$salary->month}",
            'payment_method' => $validated['payment_method'],
            'transaction_date' => $validated['payment_date'],
            'created_by' => auth()->id(),
            'notes' => $validated['notes'],
        ]);

        return redirect()->back()->with('success', "تم تسجيل صرف الراتب للموظف '{$salary->employee?->name}' بنجاح");
    }

    /**
     * كشف حساب مالي للموظف
     */
    public function employeeStatement(Employee $employee)
    {
        $salaries = $employee->salaries()->get();
        $transactions = FinancialTransaction::where('party_type', 'employee')
            ->where('party_id', $employee->id)
            ->latest('transaction_date')
            ->latest('id')
            ->get();

        $stats = [
            'total_earned' => (float) $salaries->sum('net_salary'),
            'total_paid' => (float) $salaries->sum('paid_amount'),
            'remaining' => (float) $salaries->sum(fn($s) => $s->remaining_amount),
        ];

        return view('admin.finance.employee-statement', compact('employee', 'salaries', 'transactions', 'stats'));
    }

    /**
     * 5. العمليات والسندات المحاسبية الشاملة (Accounting Transactions)
     * "وقدر اعدل ونشاء اي عمليه حسابيه"
     */
    public function transactions(Request $request)
    {
        $query = FinancialTransaction::with(['creator', 'store', 'captain', 'employee']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('party_type')) {
            $query->where('party_type', $request->party_type);
        }

        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('party_name', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $transactions = $query->latest('transaction_date')->latest('id')->paginate(20)->withQueryString();

        // بيانات منسدلة لإنشاء السندات
        $stores = Store::orderBy('name')->get(['id', 'name']);
        $captains = User::where('role', 'captain')->orderBy('name')->get(['id', 'name', 'phone']);
        $employees = Employee::where('status', 'active')->orderBy('name')->get(['id', 'name', 'job_title']);

        $stats = [
            'total_count' => FinancialTransaction::count(),
            'total_receipts' => (float) FinancialTransaction::where('type', 'receipt')->sum('amount'),
            'total_payments' => (float) FinancialTransaction::where('type', 'payment')->sum('amount'),
            'total_adjustments' => (float) FinancialTransaction::where('type', 'adjustment')->sum('amount'),
        ];

        return view('admin.finance.transactions', compact('transactions', 'stores', 'captains', 'employees', 'stats'));
    }

    /**
     * إنشاء عملية / سند محاسبي جديد
     */
    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:receipt,payment,adjustment',
            'party_type' => 'required|in:store,captain,employee,general',
            'party_id' => 'nullable|integer',
            'party_name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'direction' => 'required|in:debit,credit',
            'category' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'payment_method' => 'required|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'transaction_date' => 'required|date',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('finance/attachments', 'public');
        }

        // اسم الطرف إن لم يتم توفيره
        $partyName = $validated['party_name'] ?? null;
        if ($validated['party_type'] === 'store' && !empty($validated['party_id'])) {
            $partyName = Store::find($validated['party_id'])?->name ?? $partyName;
        } elseif ($validated['party_type'] === 'captain' && !empty($validated['party_id'])) {
            $partyName = User::find($validated['party_id'])?->name ?? $partyName;
        } elseif ($validated['party_type'] === 'employee' && !empty($validated['party_id'])) {
            $partyName = Employee::find($validated['party_id'])?->name ?? $partyName;
        }

        $transaction = FinancialTransaction::create([
            'transaction_number' => FinancialTransaction::generateTransactionNumber(),
            'type' => $validated['type'],
            'party_type' => $validated['party_type'],
            'party_id' => $validated['party_id'] ?? null,
            'party_name' => $partyName,
            'amount' => $validated['amount'],
            'direction' => $validated['direction'],
            'category' => $validated['category'] ?? 'general',
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'attachment' => $attachmentPath,
            'transaction_date' => $validated['transaction_date'],
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', "تم إنشاء العملية المحاسبية رقم {$transaction->transaction_number} بنجاح");
    }

    /**
     * تعديل عملية / سند محاسبي قائم
     */
    public function updateTransaction(Request $request, FinancialTransaction $transaction)
    {
        $validated = $request->validate([
            'type' => 'required|in:receipt,payment,adjustment',
            'party_type' => 'required|in:store,captain,employee,general',
            'party_id' => 'nullable|integer',
            'party_name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'direction' => 'required|in:debit,credit',
            'category' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'payment_method' => 'required|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'transaction_date' => 'required|date',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            if ($transaction->attachment) {
                Storage::disk('public')->delete($transaction->attachment);
            }
            $validated['attachment'] = $request->file('attachment')->store('finance/attachments', 'public');
        }

        $partyName = $validated['party_name'] ?? $transaction->party_name;
        if ($validated['party_type'] === 'store' && !empty($validated['party_id'])) {
            $partyName = Store::find($validated['party_id'])?->name ?? $partyName;
        } elseif ($validated['party_type'] === 'captain' && !empty($validated['party_id'])) {
            $partyName = User::find($validated['party_id'])?->name ?? $partyName;
        } elseif ($validated['party_type'] === 'employee' && !empty($validated['party_id'])) {
            $partyName = Employee::find($validated['party_id'])?->name ?? $partyName;
        }

        $validated['party_name'] = $partyName;

        $transaction->update($validated);

        return redirect()->back()->with('success', "تم تحديث العملية المحاسبية رقم {$transaction->transaction_number} بنجاح");
    }

    /**
     * حذف عملية محاسبية
     */
    public function deleteTransaction(FinancialTransaction $transaction)
    {
        if ($transaction->attachment) {
            Storage::disk('public')->delete($transaction->attachment);
        }

        $number = $transaction->transaction_number;
        $transaction->delete();

        return redirect()->back()->with('success', "تم حذف العملية المحاسبية {$number} بنجاح");
    }

    /**
     * طباعة سند مالي (قبض أو صرف)
     */
    public function printTransaction(FinancialTransaction $transaction)
    {
        $transaction->load(['creator', 'store', 'captain', 'employee']);
        return view('admin.finance.transaction-print', compact('transaction'));
    }

    /**
     * 6. التقارير المالية الشاملة (Financial Reports)
     */
    public function reports(Request $request)
    {
        $period = $request->input('period', 'this_month');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // حساب نطاق التاريخ
        $now = Carbon::now();
        switch ($period) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'this_week':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                break;
            case 'last_month':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'this_year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                break;
            case 'custom':
                $start = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : $now->copy()->startOfMonth();
                $end = $dateTo ? Carbon::parse($dateTo)->endOfDay() : $now->copy()->endOfDay();
                break;
            case 'this_month':
            default:
                $period = 'this_month';
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
        }

        // 1. مبيعات الطلبات وعمولات المنصة في هذا النطاق
        $orders = Order::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $totalSales = (float) $orders->sum('total_amount');
        $appCommission = (float) $orders->sum('app_commission');
        if ($appCommission <= 0 && $totalSales > 0) {
            $appCommission = round($totalSales * 0.10, 2);
        }
        $captainCommissions = (float) $orders->sum('captain_commission');

        // 2. العمليات المحاسبية في النطاق
        $transactions = FinancialTransaction::whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $totalReceipts = (float) $transactions->where('type', 'receipt')->sum('amount');
        $totalPayments = (float) $transactions->where('type', 'payment')->sum('amount');

        // رواتب ومصروفات
        $salariesPaid = (float) $transactions->where('type', 'payment')->where('party_type', 'employee')->sum('amount');
        $storePayouts = (float) $transactions->where('type', 'payment')->where('party_type', 'store')->sum('amount');
        $otherExpenses = (float) $transactions->where('type', 'payment')->where('party_type', 'general')->sum('amount');

        // كشف أرصدة المطاعم الحالي
        $stores = Store::all();
        $storeReports = [];
        foreach ($stores as $s) {
            $summary = $s->getFinancialSummary();
            if ($summary['orders_count'] > 0 || $summary['total_debit'] > 0 || $summary['total_credit'] > 0) {
                $storeReports[] = [
                    'store' => $s,
                    'summary' => $summary,
                ];
            }
        }

        // كشف أرصدة الكباتن الحالي
        $captains = User::where('role', 'captain')->get();
        $captainReports = [];
        $walletService = app(CaptainWalletService::class);
        foreach ($captains as $c) {
            $summary = $walletService->getCaptainFinancialSummary($c);
            if ($summary['completed_orders_count'] > 0 || $summary['paid_debt'] > 0 || $summary['cash_collected'] > 0) {
                $captainReports[] = [
                    'captain' => $c,
                    'summary' => $summary,
                ];
            }
        }

        // صافي ربح المنصة التقديري
        $netEstimatedProfit = $appCommission - $otherExpenses;

        $reportData = [
            'period' => $period,
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'orders_count' => $orders->count(),
            'total_sales' => $totalSales,
            'app_commission' => $appCommission,
            'captain_commissions' => $captainCommissions,
            'total_receipts' => $totalReceipts,
            'total_payments' => $totalPayments,
            'salaries_paid' => $salariesPaid,
            'store_payouts' => $storePayouts,
            'other_expenses' => $otherExpenses,
            'net_profit' => $netEstimatedProfit,
            'store_reports' => $storeReports,
            'captain_reports' => $captainReports,
        ];

        return view('admin.finance.reports', compact('reportData'));
    }
}
