<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\SiteSetting;
use App\Models\CaptainPayment;
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
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
        ];

        $recent_orders = Order::with('user')->orderBy('created_at', 'desc')->limit(5)->get();
        $top_products = Product::withCount('orderItems')->orderBy('order_items_count', 'desc')->limit(4)->get();

        return view('admin.dashboard', compact('stats', 'recent_orders', 'top_products'));
    }

    public function products(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        $productsQuery = Product::with('category')->latest();
        $selectedCategory = null;

        if ($request->filled('category_id')) {
            $selectedCategory = Category::find($request->category_id);
            $productsQuery->where('category_id', $selectedCategory->id);
        }

        $products = $productsQuery->get();
        $categories = Category::orderBy('name')->get();
        return view('admin.products', compact('products', 'categories', 'selectedCategory'));
    }

    public function orders()
    {
        $orders = Order::with(['user', 'captain'])->latest()->get();
        $captains = User::where('role', 'captain')->where('status', 'active')->get();
        return view('admin.orders', compact('orders', 'captains'));
    }

    public function carts()
    {
        $carts = Cart::with(['user', 'product'])->latest()->get();
        return view('admin.carts', compact('carts'));
    }

    public function favorites()
    {
        $favorites = Favorite::with(['user', 'product.category'])->latest()->get();
        return view('admin.favorites', compact('favorites'));
    }

    public function assignCaptain(Request $request, Order $order)
    {
        $request->validate([
            'captain_id' => 'required|exists:users,id,role,captain,status,active',
        ]);
        $order->assigned_captain_id = $request->captain_id;
        if ($order->status == 'pending') $order->status = 'processing';
        $order->save();
        $order->statusHistory()->create([
            'status' => $order->status,
            'changed_by' => auth()->id(),
        ]);
        return redirect()->back()->with('success', 'تم إسناد الطلب للكابتن بنجاح');
    }

    // --- إدارة المستخدمين ---

    public function users()
    {
        $users = User::where('role', '!=', 'captain')->orderBy('created_at', 'desc')->get();
        return view('admin.users', compact('users'));
    }

    public function captains()
    {
        $captains = User::where('role', 'captain')->withCount([
            'captainOrders as completed_orders_count' => fn ($query) => $query->where('status', 'completed'),
        ])->withSum([
            'captainOrders as cash_collected' => fn ($query) => $query->where('status', 'completed')->where('payment_method', 'cash'),
        ], 'total_amount')->orderBy('created_at', 'desc')->get();
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
        return redirect()->back()->with('success', 'تم اعتماد سداد الكابتن');
    }

    public function captainDetails(User $captain)
    {
        abort_unless($captain->role === 'captain', 404);
        $orders = Order::with('statusHistory')->where('assigned_captain_id', $captain->id)->latest()->get();
        $payments = CaptainPayment::where('captain_id', $captain->id)->latest()->get();
        $cashCollected = $orders->where('status', 'completed')->where('payment_method', 'cash')->sum('total_amount');
        $paidDebt = $payments->where('status', 'approved')->sum('amount');
        $debt = max(0, (float) $cashCollected - (float) $paidDebt);
        return view('admin.captain-detail', compact('captain', 'orders', 'payments', 'cashCollected', 'paidDebt', 'debt'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string',
            'role' => 'required|in:user,captain,admin',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_admin' => $request->role === 'admin',
            'status' => 'active',
            'phone' => $request->phone,
        ]);

        return redirect()->back()->with('success', 'تم إضافة الحساب بنجاح');
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:user,captain,admin',
        ]);

        $user->update($request->only(['name', 'email', 'role', 'phone']));
        $user->is_admin = $request->role === 'admin';
        
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
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
        if ($user->id === auth()->id()) return redirect()->back()->with('error', 'لا يمكن حذف حسابك الحالي');
        $user->delete();
        return redirect()->back()->with('success', 'تم حذف الحساب نهائياً');
    }

    public function settings()
    {
        $settings = SiteSetting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings', compact('settings'));
    }
}
