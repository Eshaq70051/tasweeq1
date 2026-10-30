@extends('admin.layout')

@section('title', 'سلال العملاء | تسويق')
@section('page_title', 'سلال العملاء الحالية النشطة')

@section('content')
    <!-- شريط البحث السريع للسلال -->
    <div class="filter-card">
        <form action="{{ route('admin.carts') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px;">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="ابحث باسم العميل أو البريد أو اسم المنتج..." style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); width: 100%;">
            </div>
            <button type="submit" class="btn btn-primary" style="height: 42px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>بحث</span>
            </button>
            @if(request()->filled('q'))
                <a href="{{ route('admin.carts') }}" class="btn btn-outline" style="height: 42px;" title="إعادة ضبط">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </form>
    </div>

    <div class="panel">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; gap:15px; flex-wrap:wrap;">
            <div>
                <h3 style="margin:0 0 5px; font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-basket-shopping" style="color: var(--primary);"></i>
                    <span>المنتجات في سلال التسوق</span>
                </h3>
                <span style="color:var(--text-muted); font-size:12px;">متابعة المنتجات التي أضافها العملاء إلى سلاتهم ولم يتم تحويلها لطلبات بعد</span>
            </div>
            <div style="background: white; padding: 8px 18px; border-radius: 99px; border: 1px solid var(--border); font-weight: 700; color: var(--primary); font-size: 13px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-layer-group"></i>
                <span>إجمالي العناصر: {{ $carts->total() }}</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>العميل</th>
                        <th>المنتج</th>
                        <th>سعر الحبة</th>
                        <th style="text-align: center;">الكمية</th>
                        <th>القيمة الإجمالية</th>
                        <th>آخر تحديث</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($carts as $cart)
                        <tr>
                            <td>
                                <strong style="font-size: 14px;">{{ $cart->user->name ?? 'مستخدم غير متوفر' }}</strong>
                                <small style="display:block; color:var(--text-muted); font-size: 11px;">{{ $cart->user->email ?? '' }}</small>
                            </td>
                            <td style="font-weight: 700;">{{ $cart->product->name ?? 'منتج محذوف' }}</td>
                            <td>{{ number_format($cart->product->price ?? 0, 2) }} ريال</td>
                            <td style="text-align: center;">
                                <span class="status-badge status-pending" style="font-size: 12px; padding: 4px 10px;">
                                    {{ $cart->quantity }}
                                </span>
                            </td>
                            <td style="font-weight:800; color:var(--primary);">{{ number_format(($cart->product->price ?? 0) * $cart->quantity, 2) }} ريال</td>
                            <td style="color: var(--text-muted); font-size: 12px;">{{ optional($cart->updated_at)->format('Y/m/d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:40px; color: var(--text-muted);">
                                <i class="fa-solid fa-basket-shopping" style="font-size: 32px; display: block; margin-bottom: 10px; color: #CCD2D6;"></i>
                                لا توجد سلال مطابقة حاليًا
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- الترقيم والتنقل بين الصفحات -->
    <div class="pagination-container">
        <div class="info-text">
            عرض <strong>{{ $carts->firstItem() ?? 0 }}</strong> إلى <strong>{{ $carts->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $carts->total() }}</strong> عنصر
        </div>
        <div>
            {{ $carts->links() }}
        </div>
    </div>
@endsection
