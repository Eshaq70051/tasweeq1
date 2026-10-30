<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\SiteSetting;
use App\Models\CaptainPayment;
use App\Models\PaymentMethod;
use App\Services\OrderNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminPanelController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_orders' => Order::count(),
            'total_users' => User::count(),
            'total_products' => Product::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'low_stock_products' => Product::where('stock', '>', 0)->whereColumn('stock', '<=', 'low_stock_threshold')->count(),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
        ];

        $recent_orders = Order::with('user')->orderBy('created_at', 'desc')->limit(5)->get();
        $top_products = Product::withCount('orderItems')->orderBy('order_items_count', 'desc')->limit(4)->get();

        return view('admin.dashboard', compact('stats', 'recent_orders', 'top_products'));
    }

    public function products(Request $request)
    {
        $productsQuery = Product::with('category');

        // البحث بالنص (اسم المنتج أو الوصف)
        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $productsQuery->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('details', 'like', "%{$keyword}%");
            });
        }

        // تصفية حسب التصنيف
        $selectedCategory = null;
        if ($request->filled('category_id')) {
            $selectedCategory = Category::find($request->category_id);
            if ($selectedCategory) {
                $productsQuery->where('category_id', $selectedCategory->id);
            }
        }

        // تصفية حسب حالة المخزون
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'in_stock') {
                $productsQuery->where('stock', '>', 0);
            } elseif ($request->stock_status === 'out_of_stock') {
                $productsQuery->where('stock', '<=', 0);
            } elseif ($request->stock_status === 'low_stock') {
                $productsQuery->where('stock', '>', 0)->whereColumn('stock', '<=', 'low_stock_threshold');
            }
        }

        // الترتيب
        switch ($request->get('sort', 'latest')) {
            case 'price_asc':
                $productsQuery->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $productsQuery->orderBy('price', 'desc');
                break;
            case 'stock_asc':
                $productsQuery->orderBy('stock', 'asc');
                break;
            case 'stock_desc':
                $productsQuery->orderBy('stock', 'desc');
                break;
            case 'name_asc':
                $productsQuery->orderBy('name', 'asc');
                break;
            case 'latest':
            default:
                $productsQuery->latest();
                break;
        }

        $products = $productsQuery->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        $stats = [
            'total' => Product::count(),
            'in_stock' => Product::where('stock', '>', 0)->count(),
            'low_stock' => Product::where('stock', '>', 0)->whereColumn('stock', '<=', 'low_stock_threshold')->count(),
            'out_of_stock' => Product::where('stock', '<=', 0)->count(),
        ];

        return view('admin.products', compact('products', 'categories', 'selectedCategory', 'stats'));
    }

    public function orders(Request $request)
    {
        $ordersQuery = Order::with(['user', 'captain', 'items']);

        // البحث (رقم الطلب، اسم العميل، هاتف العميل)
        if ($request->filled('q')) {
            $search = trim($request->q);
            $cleanSearch = ltrim($search, '#');
            $ordersQuery->where(function ($q) use ($search, $cleanSearch) {
                if (is_numeric($cleanSearch)) {
                    $q->where('id', (int) $cleanSearch)
                      ->orWhere('customer_phone', 'like', "%{$search}%");
                } else {
                    $q->where('customer_name', 'like', "%{$search}%")
                      ->orWhere('customer_phone', 'like', "%{$search}%")
                      ->orWhere('customer_address', 'like', "%{$search}%")
                      ->orWhere('transfer_number', 'like', "%{$search}%");
                }
            });
        }

        // تصفية حسب الحالة
        if ($request->filled('status') && $request->status !== 'all') {
            $ordersQuery->where('status', $request->status);
        }

        // تصفية حسب الكابتن
        if ($request->filled('captain_id')) {
            $ordersQuery->where('assigned_captain_id', $request->captain_id);
        }

        // تصفية حسب طريقة الدفع
        if ($request->filled('payment_method')) {
            $ordersQuery->where('payment_method', $request->payment_method);
        }

        // تصفية حسب النطاق الزمني
        if ($request->filled('date_from')) {
            $ordersQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $ordersQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $ordersQuery->latest()->paginate(15)->withQueryString();

        // إحصائيات سريعة للطلبات لتغذية التبويبات بالأرقام
        $statusCounts = [
            'all' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'arrived_store' => Order::where('status', 'arrived_store')->count(),
            'on_the_way' => Order::where('status', 'on_the_way')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        $captains = User::where('role', 'captain')->where('status', 'active')->orderBy('name')->get();
        $paymentMethods = PaymentMethod::all();

        return view('admin.orders', compact('orders', 'captains', 'statusCounts', 'paymentMethods'));
    }

    public function carts(Request $request)
    {
        $query = Cart::with(['user', 'product']);
        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                  ->orWhereHas('product', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }
        $carts = $query->latest()->paginate(20)->withQueryString();
        return view('admin.carts', compact('carts'));
    }

    public function favorites(Request $request)
    {
        $query = Favorite::with(['user', 'product.category']);
        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                  ->orWhereHas('product', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }
        $favorites = $query->latest()->paginate(20)->withQueryString();
        return view('admin.favorites', compact('favorites'));
    }

    public function assignCaptain(Request $request, Order $order)
    {
        $request->validate([
            'captain_id' => 'required|exists:users,id,role,captain,status,active',
        ]);
        $captain = User::find($request->captain_id);
        $order->assigned_captain_id = $request->captain_id;
        if ($order->status == 'pending') {
            $order->status = 'processing';
        }
        $order->save();
        $order->statusHistory()->create([
            'status' => $order->status,
            'changed_by' => auth()->id(),
        ]);

        // إرسال إشعار فوري للكابتن والعميل عبر FCM
        app(OrderNotificationService::class)->notifyCaptainOrderAssigned($order, $captain);
        app(OrderNotificationService::class)->notifyCustomerOrderStatus($order, $order->status);

        return redirect()->back()->with('success', 'تم إسناد الطلب للكابتن بنجاح وإرسال إشعار فوري له');
    }

    // --- إدارة المستخدمين ---

    public function users(Request $request)
    {
        $query = User::where('role', '!=', 'captain');

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total' => User::where('role', '!=', 'captain')->count(),
            'active' => User::where('role', '!=', 'captain')->where('status', 'active')->count(),
            'inactive' => User::where('role', '!=', 'captain')->where('status', '!=', 'active')->count(),
        ];

        return view('admin.users', compact('users', 'stats'));
    }

    public function captains(Request $request)
    {
        $query = User::where('role', 'captain')->withCount([
            'captainOrders as completed_orders_count' => fn ($query) => $query->where('status', 'completed'),
        ])->withSum([
            'captainOrders as cash_collected' => fn ($query) => $query->where('status', 'completed')->where('payment_method', 'cash'),
        ], 'total_amount');

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_available')) {
            $query->where('is_available', (bool) $request->is_available);
        }

        $captains = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        foreach ($captains as $captain) {
            $captain->paid_debt = CaptainPayment::where('captain_id', $captain->id)->where('status', 'approved')->sum('amount');
            $captain->pending_payments = CaptainPayment::where('captain_id', $captain->id)->where('status', 'pending')->sum('amount');
            $captain->outstanding_debt = max(0, (float) ($captain->cash_collected ?? 0) - (float) $captain->paid_debt);
        }

        return view('admin.captains', compact('captains'));
    }

    public function approveCaptainPayment(CaptainPayment $captainPayment)
    {
        if ($captainPayment->status !== 'pending') {
            return redirect()->back()->with('error', 'تمت معالجة طلب السداد مسبقاً');
        }
        $captainPayment->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // توثيق العملية في دفتر الأستاذ المالي للكابتن
        app(\App\Services\CaptainWalletService::class)->recordPaymentApproved($captainPayment);

        app(OrderNotificationService::class)->notifyCaptainPaymentApproved($captainPayment);
        return redirect()->back()->with('success', 'تم اعتماد سداد الكابتن وتحديث رصيد المحفظة بنجاح');
    }

    public function captainDetails(User $captain)
    {
        abort_unless($captain->role === 'captain', 404);
        
        $summary = app(\App\Services\CaptainWalletService::class)->getCaptainFinancialSummary($captain);
        $orders = Order::with('statusHistory')->where('assigned_captain_id', $captain->id)->latest()->take(20)->get();
        $payments = CaptainPayment::where('captain_id', $captain->id)->latest()->take(20)->get();
        $ledger = $captain->captainLedgerEntries()->with(['order', 'payment'])->take(30)->get();

        $cashCollected = $summary['cash_collected'];
        $paidDebt = $summary['paid_debt'];
        $debt = $summary['outstanding_debt'];
        $earnedCommission = $summary['earned_commission'];

        return view('admin.captain-detail', compact('captain', 'orders', 'payments', 'ledger', 'cashCollected', 'paidDebt', 'debt', 'earnedCommission', 'summary'));
    }

    public function storeUser(StoreUserRequest $request)
    {
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_admin' => $validated['role'] === 'admin',
            'status' => 'active',
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()->back()->with('success', 'تم إضافة الحساب بنجاح');
    }

    public function updateUser(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->is_admin = $validated['role'] === 'admin';
        $user->phone = $validated['phone'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        return redirect()->back()->with('success', 'تم تحديث بيانات الحساب بنجاح');
    }

    public function updateUserRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,captain,admin',
        ]);

        $user->role = $request->role;
        $user->is_admin = $request->role === 'admin';
        $user->save();

        return redirect()->route('admin.users')->with('success', 'تم تحديث صلاحية المستخدم بنجاح');
    }

    public function toggleUserStatus(User $user)
    {
        $user->status = ($user->status === 'active') ? 'inactive' : 'active';
        $user->save();
        return redirect()->back()->with('success', 'تم تغيير حالة الحساب');
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'لا يمكن حذف حسابك الحالي');
        }
        $user->delete();
        return redirect()->back()->with('success', 'تم حذف الحساب نهائياً');
    }

    public function settings()
    {
        $settings = SiteSetting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings', compact('settings'));
    }
}
