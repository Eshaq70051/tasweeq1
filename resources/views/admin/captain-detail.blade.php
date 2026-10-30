@extends('admin.layout')

@section('title', 'تفاصيل الكابتن | ' . $captain->name)
@section('page_title', 'كشف حساب الكابتن: ' . $captain->name)

@section('content')
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <a href="{{ route('admin.captains') }}" class="btn btn-outline" style="padding: 8px 14px;">
            <i class="fa-solid fa-arrow-right"></i>
            <span>العودة لقائمة الكباتن</span>
        </a>
        <div style="font-weight: 700; font-size: 13px; color: var(--text-muted);">
            <i class="fa-solid fa-phone" style="margin-left: 4px;"></i>
            {{ $captain->phone ?: 'لا يوجد هاتف مسجل' }} | {{ $captain->email }}
        </div>
    </div>

    <!-- البطاقات المالية الأربع الشاملة -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="info">
                <span class="title">إجمالي المحصل نقداً</span>
                <span class="value">{{ number_format($cashCollected, 2) }} <small style="font-size: 13px; font-weight: normal; color: var(--text-muted);">ريال</small></span>
            </div>
            <div class="icon-box" style="background: rgba(45, 156, 219, 0.1); color: #2D9CDB;">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="info">
                <span class="title">إجمالي عمولات الكابتن</span>
                <span class="value" style="color: var(--primary);">{{ number_format($earnedCommission, 2) }} <small style="font-size: 13px; font-weight: normal; color: var(--text-muted);">ريال</small></span>
            </div>
            <div class="icon-box" style="background: var(--primary-soft); color: var(--primary);">
                <i class="fa-solid fa-award"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="info">
                <span class="title">إجمالي السداد المعتمد</span>
                <span class="value" style="color: var(--success);">{{ number_format($paidDebt, 2) }} <small style="font-size: 13px; font-weight: normal; color: var(--text-muted);">ريال</small></span>
            </div>
            <div class="icon-box" style="background: rgba(39, 174, 96, 0.1); color: #27AE60;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="info">
                <span class="title">صافي المديونية المتبقية</span>
                <span class="value" style="color: var(--danger);">{{ number_format($debt, 2) }} <small style="font-size: 13px; font-weight: normal; color: var(--text-muted);">ريال</small></span>
            </div>
            <div class="icon-box" style="background: rgba(255, 77, 77, 0.1); color: var(--danger);">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
        </div>
    </div>

    <!-- دفتر أستاذ الحسابات المالية (Ledger Entries) -->
    <div class="panel">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-book-bookmark" style="color: var(--primary);"></i>
            <span>دفتر الحركات المالية الموثقة (Financial Ledger)</span>
        </h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>التاريخ والوقت</th>
                        <th>نوع الحركة</th>
                        <th>البيان / الوصف</th>
                        <th>المبلغ</th>
                        <th>الرصيد بعد الحركة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledger as $entry)
                        <tr>
                            <td style="color: var(--text-muted); font-size: 12px; white-space: nowrap;">
                                {{ $entry->created_at->format('Y-m-d') }}
                                <small style="display: block;">{{ $entry->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                @php
                                    $typeBadge = match($entry->type) {
                                        'cash_collected' => ['bg' => 'rgba(255, 77, 77, 0.12)', 'color' => 'var(--danger)', 'text' => 'تحصيل كاش (مدين)', 'icon' => 'fa-arrow-down'],
                                        'commission_earned' => ['bg' => 'rgba(6, 132, 148, 0.12)', 'color' => 'var(--primary)', 'text' => 'عمولة توصيل (دائن)', 'icon' => 'fa-award'],
                                        'payment_settlement' => ['bg' => 'rgba(39, 174, 96, 0.12)', 'color' => '#168447', 'text' => 'تسديد للإدارة (دائن)', 'icon' => 'fa-check'],
                                        default => ['bg' => '#EDF2F7', 'color' => 'var(--text-muted)', 'text' => 'تسوية مالية', 'icon' => 'fa-scale-balanced'],
                                    };
                                @endphp
                                <span class="status-badge" style="background: {{ $typeBadge['bg'] }}; color: {{ $typeBadge['color'] }}; font-size: 11px;">
                                    <i class="fa-solid {{ $typeBadge['icon'] }}" style="font-size: 10px;"></i>
                                    <span>{{ $typeBadge['text'] }}</span>
                                </span>
                            </td>
                            <td style="font-weight: 600;">
                                {{ $entry->description }}
                                @if($entry->order_id)
                                    <a href="{{ route('admin.orders.show', $entry->order_id) }}" style="color: var(--primary); font-size: 11px; text-decoration: none; margin-right: 6px;">
                                        (عرض الطلب #{{ $entry->order_id }})
                                    </a>
                                @endif
                            </td>
                            <td style="font-weight: 800; white-space: nowrap;">
                                @if($entry->direction === 'debit')
                                    <span style="color: var(--danger);">+ {{ number_format($entry->amount, 2) }} ريال</span>
                                @else
                                    <span style="color: var(--success);">- {{ number_format($entry->amount, 2) }} ريال</span>
                                @endif
                            </td>
                            <td style="font-weight: 800; color: var(--primary); white-space: nowrap;">
                                {{ number_format($entry->balance_after, 2) }} ريال
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                <i class="fa-solid fa-receipt" style="font-size: 32px; display: block; margin-bottom: 8px; color: #CCD2D6;"></i>
                                لم تسجل أي حركات مالية في دفتر الحسابات بعد
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- جدول طلبات السداد والتسوية المالية -->
    <div class="panel">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-receipt" style="color: var(--primary);"></i>
            <span>طلبات السداد والتسوية المالية</span>
        </h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>المبلغ</th>
                        <th>طريقة التحويل</th>
                        <th>رقم المرجع / الحوالة</th>
                        <th>صورة الإيصال</th>
                        <th>حالة السداد</th>
                        <th style="text-align: center;">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td style="font-weight: 800; color: var(--primary);">{{ number_format($payment->amount, 2) }} ريال</td>
                            <td>
                                @php
                                    $paymentMethods = [
                                        'jaib' => 'محفظة جيب',
                                        'jawali' => 'جوالي',
                                        'one_cash' => 'ون كاش',
                                        'kuraimi' => 'الكريمي',
                                    ];
                                @endphp
                                <span style="font-weight: 700;">{{ $paymentMethods[$payment->payment_method] ?? $payment->payment_method }}</span>
                            </td>
                            <td>{{ $payment->reference ?: '—' }}</td>
                            <td>
                                @if($payment->receipt_image)
                                    <a href="{{ route('media.file', ['path' => $payment->receipt_image]) }}" target="_blank">
                                        <img src="{{ route('media.file', ['path' => $payment->receipt_image]) }}" alt="receipt" style="width:70px;height:50px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                                    </a>
                                @else
                                    <span style="color: var(--text-muted);">لا يوجد</span>
                                @endif
                            </td>
                            <td>
                                <span class="status-badge {{ $payment->status === 'approved' ? 'status-success' : 'status-pending' }}">
                                    <i class="fa-solid {{ $payment->status === 'approved' ? 'fa-check' : 'fa-clock' }}" style="font-size: 10px;"></i>
                                    <span>{{ $payment->status === 'approved' ? 'معتمد ومقبول' : 'قيد المراجعة' }}</span>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @if($payment->status === 'pending')
                                    <form action="{{ route('admin.captain-payments.approve', $payment) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-primary" type="submit" style="padding: 6px 12px; font-size: 11px;">
                                            <i class="fa-solid fa-check-double"></i>
                                            <span>اعتماد السداد</span>
                                        </button>
                                    </form>
                                @else
                                    <small style="color: var(--text-muted); font-size: 11px;">
                                        تم الاعتماد: {{ optional($payment->approved_at)->format('Y-m-d H:i') }}
                                    </small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                لا توجد طلبات سداد مسجلة
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- جدول الطلبات المسندة للكابتن -->
    <div class="panel">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-route" style="color: var(--primary);"></i>
            <span>سجل طلبات التوصيل ومراحل الإنجاز</span>
        </h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>بيانات العميل</th>
                        <th>المبلغ الإجمالي</th>
                        <th>عمولة الكابتن</th>
                        <th>حالة الطلب</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" style="font-weight: 800; color: var(--primary); text-decoration: none;">
                                    #{{ $order->id }}
                                </a>
                                <br>
                                <small style="color: var(--text-muted); font-size: 11px;">{{ $order->created_at->format('Y-m-d H:i') }}</small>
                            </td>
                            <td>
                                <div style="font-weight: 700;">{{ $order->customer_name }}</div>
                                <small style="color: var(--text-muted); font-size: 11px;">{{ $order->customer_phone }}</small>
                            </td>
                            <td style="font-weight: 800;">{{ number_format($order->total_amount, 2) }} ريال</td>
                            <td style="font-weight: 800; color: var(--primary);">{{ number_format($order->captain_commission, 2) }} ريال</td>
                            <td>
                                <span class="status-badge {{ $order->status === 'completed' ? 'status-success' : 'status-info' }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                لم يتم إسناد أي طلبات لهذا الكابتن بعد
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
