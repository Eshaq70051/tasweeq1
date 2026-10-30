@extends('admin.layout')

@section('title', 'إدارة الطلبات | تسويق')
@section('page_title', 'قائمة الطلبات والمبيعات')

@section('content')
    <!-- تبويبات الحالة السريعة بالأرقام -->
    <div class="filter-tabs">
        @php
            $currentStatus = request('status', 'all');
        @endphp
        <a href="{{ route('admin.orders', array_merge(request()->except(['status', 'page']), ['status' => 'all'])) }}" class="filter-pill {{ $currentStatus === 'all' ? 'active' : '' }}">
            <i class="fa-solid fa-layer-group"></i>
            <span>الكل</span>
            <span class="badge-count">{{ $statusCounts['all'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.orders', array_merge(request()->except(['status', 'page']), ['status' => 'pending'])) }}" class="filter-pill {{ $currentStatus === 'pending' ? 'active' : '' }}">
            <i class="fa-solid fa-clock"></i>
            <span>قيد الانتظار</span>
            <span class="badge-count">{{ $statusCounts['pending'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.orders', array_merge(request()->except(['status', 'page']), ['status' => 'processing'])) }}" class="filter-pill {{ $currentStatus === 'processing' ? 'active' : '' }}">
            <i class="fa-solid fa-spinner"></i>
            <span>قيد التجهيز</span>
            <span class="badge-count">{{ $statusCounts['processing'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.orders', array_merge(request()->except(['status', 'page']), ['status' => 'arrived_store'])) }}" class="filter-pill {{ $currentStatus === 'arrived_store' ? 'active' : '' }}">
            <i class="fa-solid fa-shop"></i>
            <span>وصل المتجر</span>
            <span class="badge-count">{{ $statusCounts['arrived_store'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.orders', array_merge(request()->except(['status', 'page']), ['status' => 'on_the_way'])) }}" class="filter-pill {{ $currentStatus === 'on_the_way' ? 'active' : '' }}">
            <i class="fa-solid fa-motorcycle"></i>
            <span>في الطريق</span>
            <span class="badge-count">{{ $statusCounts['on_the_way'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.orders', array_merge(request()->except(['status', 'page']), ['status' => 'completed'])) }}" class="filter-pill {{ $currentStatus === 'completed' ? 'active' : '' }}">
            <i class="fa-solid fa-circle-check"></i>
            <span>مكتمل</span>
            <span class="badge-count">{{ $statusCounts['completed'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.orders', array_merge(request()->except(['status', 'page']), ['status' => 'cancelled'])) }}" class="filter-pill {{ $currentStatus === 'cancelled' ? 'active' : '' }}">
            <i class="fa-solid fa-circle-xmark"></i>
            <span>ملغي</span>
            <span class="badge-count">{{ $statusCounts['cancelled'] ?? 0 }}</span>
        </a>
    </div>

    <!-- شريط الفلاتر المتقدمة والبحث -->
    <div class="filter-card">
        <form action="{{ route('admin.orders') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; align-items: end;">
            @if(request('status') && request('status') !== 'all')
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                    <i class="fa-solid fa-magnifying-glass" style="margin-left: 4px;"></i>
                    البحث
                </label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="رقم الطلب، اسم العميل، الهاتف..." style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); width: 100%;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                    <i class="fa-solid fa-motorcycle" style="margin-left: 4px;"></i>
                    الكابتن
                </label>
                <select name="captain_id" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); width: 100%; background: white;">
                    <option value="">-- كل الكباتن --</option>
                    @foreach($captains as $captain)
                        <option value="{{ $captain->id }}" {{ request('captain_id') == $captain->id ? 'selected' : '' }}>
                            {{ $captain->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                    <i class="fa-solid fa-credit-card" style="margin-left: 4px;"></i>
                    طريقة الدفع
                </label>
                <select name="payment_method" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); width: 100%; background: white;">
                    <option value="">-- كل طرق الدفع --</option>
                    @foreach($paymentMethods as $pm)
                        <option value="{{ $pm->code }}" {{ request('payment_method') == $pm->code ? 'selected' : '' }}>
                            {{ $pm->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                    <i class="fa-solid fa-calendar-day" style="margin-left: 4px;"></i>
                    من تاريخ
                </label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); width: 100%;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                    <i class="fa-solid fa-calendar-day" style="margin-left: 4px;"></i>
                    إلى تاريخ
                </label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); width: 100%;">
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; height: 42px;">
                    <i class="fa-solid fa-filter"></i>
                    <span>تطبيق الفلترة</span>
                </button>
                @if(request()->anyFilled(['q', 'captain_id', 'payment_method', 'date_from', 'date_to', 'status']))
                    <a href="{{ route('admin.orders') }}" class="btn btn-outline" style="height: 42px;" title="إعادة ضبط الفلاتر">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- جدول الطلبات -->
    <div class="panel">
        <div class="table-responsive">
            <table class="table" id="ordersTable">
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>العميل</th>
                        <th>التاريخ</th>
                        <th>طريقة الدفع</th>
                        <th>الإجمالي</th>
                        <th>إسناد لكابتن</th>
                        <th>حالة الطلب</th>
                        <th style="text-align: center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td style="font-weight: 800; color: var(--primary);">#{{ $order->id }}</td>
                            <td>
                                <div style="font-weight: 700;">{{ $order->customer_name }}</div>
                                <small style="color: var(--text-muted); font-size: 11px;">
                                    <i class="fa-solid fa-phone" style="font-size: 10px; margin-left: 4px;"></i>
                                    {{ $order->customer_phone }}
                                </small>
                            </td>
                            <td style="color: var(--text-muted); font-size: 12px; white-space: nowrap;">
                                {{ $order->created_at->format('Y-m-d') }}<br>
                                <small>{{ $order->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <span class="status-badge" style="background: #F4F7F8; color: var(--text-main); font-size: 11px;">
                                    {{ $order->payment_method === 'cash' ? 'نقدًا' : strtoupper($order->payment_method) }}
                                </span>
                            </td>
                            <td style="font-weight: 800; white-space: nowrap;">
                                {{ number_format($order->total_amount, 2) }} <small style="font-size: 11px; font-weight: normal; color: var(--text-muted);">ريال</small>
                                @if($order->discount_amount > 0)
                                    <small style="display: block; color: var(--danger); font-size: 10px; font-weight: 700;">
                                        خصم {{ number_format($order->discount_amount, 0) }} ريال ({{ $order->coupon_code }})
                                    </small>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.orders.assignCaptain', $order) }}" method="POST">
                                    @csrf
                                    <select name="captain_id" onchange="this.form.submit()" style="padding: 7px 10px; border-radius: 8px; border: 1px solid var(--border); font-size: 12px; outline: none; background: white; color: var(--text-main); max-width: 160px;">
                                        <option value="">-- اختر كابتن --</option>
                                        @foreach($captains as $captain)
                                            <option value="{{ $captain->id }}" {{ $order->assigned_captain_id == $captain->id ? 'selected' : '' }}>
                                                {{ $captain->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td>
                                <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                                    @csrf
                                    @php
                                        $badgeBg = match($order->status) {
                                            'completed' => 'rgba(39, 174, 96, 0.12)',
                                            'cancelled' => 'rgba(255, 77, 77, 0.12)',
                                            'pending' => 'rgba(242, 153, 74, 0.14)',
                                            default => 'rgba(6, 132, 148, 0.12)',
                                        };
                                        $badgeColor = match($order->status) {
                                            'completed' => '#168447',
                                            'cancelled' => '#c93636',
                                            'pending' => '#b96818',
                                            default => 'var(--primary)',
                                        };
                                    @endphp
                                    <select name="status" onchange="this.form.submit()" style="padding: 7px 10px; border-radius: 8px; border: 1px solid var(--border); font-size: 12px; outline: none; background: {{ $badgeBg }}; color: {{ $badgeColor }}; font-weight: 700; max-width: 130px;">
                                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>معلق</option>
                                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>قيد التجهيز</option>
                                        <option value="arrived_store" {{ $order->status == 'arrived_store' ? 'selected' : '' }}>وصل المتجر</option>
                                        <option value="on_the_way" {{ $order->status == 'on_the_way' ? 'selected' : '' }}>في الطريق</option>
                                        <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                                    </select>
                                </form>
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline" style="padding: 6px 14px; font-size: 12px;" title="عرض التفاصيل">
                                    <i class="fa-solid fa-eye"></i>
                                    <span>التفاصيل</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 45px; color: var(--text-muted);">
                                <i class="fa-solid fa-inbox" style="font-size: 36px; display: block; margin-bottom: 10px; color: #CCD2D6;"></i>
                                لا توجد طلبات مطابقة لمعايير البحث
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
            عرض <strong>{{ $orders->firstItem() ?? 0 }}</strong> إلى <strong>{{ $orders->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $orders->total() }}</strong> طلب
        </div>
        <div>
            {{ $orders->links() }}
        </div>
    </div>
@endsection
