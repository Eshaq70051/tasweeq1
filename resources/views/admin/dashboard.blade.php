@extends('admin.layout')

@section('title', 'لوحة التحكم')
@section('page_title', 'نظرة عامة على المتجر')

@section('content')
    <div class="stat-grid">
        <div class="stat-card">
            <div style="font-size: 13px; color: var(--text-muted); font-weight: bold; margin-bottom: 5px;">📦 إجمالي الطلبات</div>
            <div class="value">{{ $stats['total_orders'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div style="font-size: 13px; color: var(--text-muted); font-weight: bold; margin-bottom: 5px;">💰 الإيرادات</div>
            <div class="value">{{ number_format($stats['total_revenue'] ?? 0, 0) }} ريال</div>
        </div>
        <div class="stat-card">
            <div style="font-size: 13px; color: var(--text-muted); font-weight: bold; margin-bottom: 5px;">👥 المستخدمين</div>
            <div class="value">{{ $stats['total_users'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div style="font-size: 13px; color: var(--text-muted); font-weight: bold; margin-bottom: 5px;">🛒 طلبات اليوم</div>
            <div class="value">{{ $stats['today_orders'] ?? 0 }}</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px;">
        <!-- آخر الطلبات -->
        <div class="panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin:0;">آخر الطلبات</h3>
                <a href="{{ route('admin.orders') }}" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;">عرض الكل</a>
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
                        @foreach($recent_orders as $order)
                            <tr>
                                <td>{{ $order->customer_name }}</td>
                                <td style="font-weight: 800; color: var(--primary);">{{ number_format($order->total_amount, 2) }}</td>
                                <td><span class="status-badge status-pending">{{ $order->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- الأكثر مبيعاً -->
        <div class="panel">
            <h3 style="margin: 0 0 20px;">الأكثر مبيعاً</h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($top_products as $product)
                    <div style="display: flex; align-items: center; gap: 15px; padding: 12px; background: #F9FAFB; border-radius: 15px; border: 1px solid var(--border);">
                        <div style="flex: 1;">
                            <div style="font-weight: 800; font-size: 14px;">{{ $product->name }}</div>
                            <small style="color: var(--text-muted);">{{ $product->order_items_count }} مبيعة</small>
                        </div>
                        <div style="font-weight: 900; color: var(--primary);">{{ number_format($product->price, 0) }} ريال</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
