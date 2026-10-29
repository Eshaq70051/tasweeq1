@extends('admin.layout')

@section('title', 'سلال العملاء')
@section('page_title', 'سلال العملاء الحالية')

@section('content')
    <div class="panel">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; gap:15px; flex-wrap:wrap;">
            <div>
                <h3 style="margin:0 0 5px;">السلال المحفوظة</h3>
                <span style="color:var(--text-muted); font-size:13px;">المنتجات التي أضافها العملاء ولم يتم تحويلها إلى طلبات بعد</span>
            </div>
            <span class="status-badge status-success">{{ $carts->count() }} عنصر</span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>العميل</th>
                        <th>المنتج</th>
                        <th>السعر</th>
                        <th>الكمية</th>
                        <th>قيمة العنصر</th>
                        <th>آخر تحديث</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($carts as $cart)
                        <tr>
                            <td>
                                <strong>{{ $cart->user->name ?? 'مستخدم محذوف' }}</strong>
                                <small style="display:block; color:var(--text-muted);">{{ $cart->user->email ?? '' }}</small>
                            </td>
                            <td>{{ $cart->product->name ?? 'منتج محذوف' }}</td>
                            <td>{{ number_format($cart->product->price ?? 0, 2) }} ريال</td>
                            <td><span class="status-badge status-pending">{{ $cart->quantity }}</span></td>
                            <td style="font-weight:800; color:var(--primary);">{{ number_format(($cart->product->price ?? 0) * $cart->quantity, 2) }} ريال</td>
                            <td>{{ optional($cart->updated_at)->format('Y/m/d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center; padding:40px;">لا توجد سلال محفوظة حاليًا</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
