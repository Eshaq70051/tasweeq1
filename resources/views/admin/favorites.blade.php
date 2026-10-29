@extends('admin.layout')

@section('title', 'مفضلة العملاء | تسويق')
@section('page_title', 'قائمة المنتجات المفضلة لدى العملاء')

@section('content')
    <div class="panel">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; gap:15px; flex-wrap:wrap;">
            <div>
                <h3 style="margin:0 0 5px; font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-heart" style="color: #FF4D4D;"></i>
                    <span>رغبات ومفضلة العملاء (Wishlists)</span>
                </h3>
                <span style="color:var(--text-muted); font-size:12px;">مؤشر اهتمام العملاء بالمنتجات لتحديد العروض الأكثر طلباً</span>
            </div>
            <div style="background: white; padding: 8px 18px; border-radius: 99px; border: 1px solid var(--border); font-weight: 700; color: #FF4D4D; font-size: 13px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-heart"></i>
                <span>{{ $favorites->count() }} عنصر في المفضلة</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>العميل</th>
                        <th>البريد الإلكتروني</th>
                        <th>المنتج المفضل</th>
                        <th>التصنيف</th>
                        <th>السعر الحالي</th>
                        <th>تاريخ الإضافة للمفضلة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($favorites as $favorite)
                        <tr>
                            <td>
                                <strong style="font-size: 14px;">{{ $favorite->user->name ?? 'مستخدم غير متوفر' }}</strong>
                            </td>
                            <td style="color:var(--text-muted); font-size: 12px;">{{ $favorite->user->email ?? '—' }}</td>
                            <td style="font-weight:700;">{{ $favorite->product->name ?? 'منتج محذوف' }}</td>
                            <td>
                                <span class="status-badge" style="background: var(--primary-soft); color: var(--primary); font-size: 11px;">
                                    {{ $favorite->product->category->name ?? 'عام' }}
                                </span>
                            </td>
                            <td style="font-weight:800; color:var(--primary);">{{ number_format($favorite->product->price ?? 0, 2) }} ريال</td>
                            <td style="color: var(--text-muted); font-size: 12px;">{{ optional($favorite->created_at)->format('Y/m/d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:40px; color: var(--text-muted);">
                                <i class="fa-solid fa-heart-crack" style="font-size: 32px; display: block; margin-bottom: 10px; color: #CCD2D6;"></i>
                                لا توجد منتجات مضافة للمفضلة حاليًا
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
