@extends('admin.layout')

@section('title', 'لوحة التحكم | تسويق')
@section('page_title', 'نظرة عامة على المتجر')

@section('content')
    @if(($stats['low_stock_products'] ?? 0) > 0)
        <div class="panel" style="background: rgba(242, 153, 74, 0.08); border: 1px solid rgba(242, 153, 74, 0.3); display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 12px; padding: 16px 20px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(242, 153, 74, 0.18); color: #B96818; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h4 style="margin: 0 0 2px; font-size: 14px; font-weight: 800; color: #B96818;">تنبيه مخزون منخفض</h4>
                    <span style="font-size: 12px; color: var(--text-muted);">يوجد <strong>{{ $stats['low_stock_products'] }}</strong> منتجات قاربت كميتها على النفاد في المخزون</span>
                </div>
            </div>
            <a href="{{ route('admin.products', ['stock_status' => 'low_stock']) }}" class="btn btn-outline" style="border-color: #F2994A; color: #B96818; font-size: 12px;">
                <span>معاينة المنتجات</span>
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </div>
    @endif

    <div class="stat-grid">
        <div class="stat-card">
            <div class="info">
                <span class="title">إجمالي الطلبات</span>
                <span class="value">{{ number_format($stats['total_orders'] ?? 0) }}</span>
            </div>
            <div class="icon-box">
                <i class="fa-solid fa-boxes-packing"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="info">
                <span class="title">إجمالي الإيرادات</span>
                <span class="value">{{ number_format($stats['total_revenue'] ?? 0, 0) }} <small style="font-size: 14px; font-weight: normal; color: var(--text-muted);">ريال</small></span>
            </div>
            <div class="icon-box" style="background: rgba(39, 174, 96, 0.1); color: #27AE60;">
                <i class="fa-solid fa-coins"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="info">
                <span class="title">إجمالي العملاء والمستخدمين</span>
                <span class="value">{{ number_format($stats['total_users'] ?? 0) }}</span>
            </div>
            <div class="icon-box" style="background: rgba(45, 156, 219, 0.1); color: #2D9CDB;">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="info">
                <span class="title">طلبات اليوم</span>
                <span class="value">{{ number_format($stats['today_orders'] ?? 0) }}</span>
            </div>
            <div class="icon-box" style="background: rgba(242, 153, 74, 0.12); color: #F2994A;">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
        <!-- آخر الطلبات -->
        <div class="panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin:0; font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-clock-rotate-left" style="color: var(--primary);"></i>
                    <span>آخر الطلبات المسجلة</span>
                </h3>
                <a href="{{ route('admin.orders') }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px;">
                    <span>عرض الكل</span>
                    <i class="fa-solid fa-arrow-left" style="font-size: 11px;"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>العميل</th>
                            <th>المبلغ</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent_orders as $order)
                            <tr>
                                <td>
                                    <div style="font-weight: 700;">{{ $order->customer_name }}</div>
                                    <small style="color: var(--text-muted); font-size: 11px;">#{{ $order->id }}</small>
                                </td>
                                <td style="font-weight: 800; color: var(--primary);">
                                    {{ number_format($order->total_amount, 2) }} <small style="font-size: 11px; font-weight: normal; color: var(--text-muted);">ريال</small>
                                </td>
                                <td>
                                    @php
                                        $statusConfig = [
                                            'pending' => ['label' => 'معلق', 'class' => 'status-pending', 'icon' => 'fa-clock'],
                                            'processing' => ['label' => 'قيد التجهيز', 'class' => 'status-info', 'icon' => 'fa-box-open'],
                                            'on_the_way' => ['label' => 'في الطريق', 'class' => 'status-info', 'icon' => 'fa-truck-fast'],
                                            'completed' => ['label' => 'مكتمل', 'class' => 'status-success', 'icon' => 'fa-circle-check'],
                                            'cancelled' => ['label' => 'ملغي', 'class' => 'status-danger', 'icon' => 'fa-circle-xmark'],
                                        ];
                                        $currentStatus = $statusConfig[$order->status] ?? ['label' => $order->status, 'class' => 'status-pending', 'icon' => 'fa-circle-info'];
                                    @endphp
                                    <span class="status-badge {{ $currentStatus['class'] }}">
                                        <i class="fa-solid {{ $currentStatus['icon'] }}" style="font-size: 10px;"></i>
                                        <span>{{ $currentStatus['label'] }}</span>
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                    <i class="fa-solid fa-inbox" style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                                    لا توجد طلبات حديثة
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- الأكثر مبيعاً -->
        <div class="panel">
            <h3 style="margin: 0 0 20px; font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-fire-flame-curved" style="color: #F2994A;"></i>
                <span>المنتجات الأكثر طلباً</span>
            </h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @forelse($top_products as $product)
                    <div style="display: flex; align-items: center; gap: 14px; padding: 12px 16px; background: #F8FBFB; border-radius: 14px; border: 1px solid var(--border);">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: white; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; color: var(--primary);">
                            <i class="fa-solid fa-box"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 14px;">{{ $product->name }}</div>
                            <small style="color: var(--text-muted); font-size: 11px;">
                                <i class="fa-solid fa-bag-shopping" style="margin-left: 4px;"></i>
                                {{ $product->order_items_count }} طلب
                            </small>
                        </div>
                        <div style="font-weight: 800; color: var(--primary); font-size: 14px;">
                            {{ number_format($product->price, 0) }} <small style="font-size: 10px; font-weight: normal; color: var(--text-muted);">ريال</small>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; color: var(--text-muted); padding: 30px;">
                        <i class="fa-solid fa-chart-line" style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                        لا توجد بيانات مبيعات حتى الآن
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
