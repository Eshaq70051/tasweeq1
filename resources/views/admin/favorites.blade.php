@extends('admin.layout')

@section('title', 'مفضلة العملاء')
@section('page_title', 'مفضلة العملاء')

@section('content')
    <div class="panel">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; gap:15px; flex-wrap:wrap;">
            <div>
                <h3 style="margin:0 0 5px;">المنتجات المفضلة</h3>
                <span style="color:var(--text-muted); font-size:13px;">متابعة المنتجات التي حفظها العملاء في المفضلة</span>
            </div>
            <span class="status-badge status-success">{{ $favorites->count() }} حفظ</span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>العميل</th>
                        <th>البريد</th>
                        <th>المنتج</th>
                        <th>التصنيف</th>
                        <th>السعر</th>
                        <th>تاريخ الحفظ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($favorites as $favorite)
                        <tr>
                            <td>{{ $favorite->user->name ?? 'مستخدم محذوف' }}</td>
                            <td>{{ $favorite->user->email ?? '-' }}</td>
                            <td style="font-weight:700;">{{ $favorite->product->name ?? 'منتج محذوف' }}</td>
                            <td>{{ $favorite->product->category->name ?? 'عام' }}</td>
                            <td style="font-weight:800; color:var(--primary);">{{ number_format($favorite->product->price ?? 0, 2) }} ريال</td>
                            <td>{{ optional($favorite->created_at)->format('Y/m/d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center; padding:40px;">لا توجد منتجات مفضلة حاليًا</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
