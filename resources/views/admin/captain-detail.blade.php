@extends('admin.layout')

@section('title', 'تفاصيل الكابتن')
@section('page_title', 'حساب الكابتن: ' . $captain->name)

@section('content')
    <div class="stat-grid">
        <div class="stat-card"><div>المحصل نقداً</div><div class="value">{{ number_format($cashCollected, 2) }}</div></div>
        <div class="stat-card"><div>إجمالي السداد المعتمد</div><div class="value">{{ number_format($paidDebt, 2) }}</div></div>
        <div class="stat-card"><div>المديونية الحالية</div><div class="value" style="color: var(--danger);">{{ number_format($debt, 2) }}</div></div>
    </div>

    <div class="panel">
        <h3>طلبات السداد</h3>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>المبلغ</th><th>الطريقة</th><th>المرجع</th><th>الإيصال</th><th>الحالة</th><th>الإجراء</th></tr></thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ ['jaib' => 'جيب', 'jawali' => 'جوالي', 'one_cash' => 'ون كاش'][$payment->payment_method] ?? $payment->payment_method }}</td>
                            <td>{{ $payment->reference ?: '-' }}</td>
                            <td>
                                @if($payment->receipt_image)
                                    <a href="{{ route('media.file', ['path' => $payment->receipt_image]) }}" target="_blank">
                                        <img src="{{ route('media.file', ['path' => $payment->receipt_image]) }}" alt="receipt" style="width:70px;height:50px;object-fit:cover;border-radius:8px;">
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $payment->status === 'approved' ? 'معتمد' : 'قيد المراجعة' }}</td>
                            <td>
                                @if($payment->status === 'pending')
                                    <form action="{{ route('admin.captain-payments.approve', $payment) }}" method="POST">@csrf<button class="btn btn-primary" type="submit">اعتماد السداد</button></form>
                                @else
                                    {{ optional($payment->approved_at)->format('Y-m-d H:i') }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">لا توجد طلبات سداد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <h3>سجل الطلبات والحالات</h3>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>الطلب</th><th>العميل</th><th>المبلغ</th><th>الحالة الحالية</th><th>التسلسل الزمني</th></tr></thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}<br><small>{{ $order->created_at->format('Y-m-d H:i') }}</small></td>
                            <td>{{ $order->customer_name }}<br><small>{{ $order->customer_phone }}</small></td>
                            <td>{{ number_format($order->total_amount, 2) }}</td>
                            <td>{{ $order->status }}</td>
                            <td>
                                @forelse($order->statusHistory as $event)
                                    <div>{{ $event->status }} - {{ $event->created_at->format('Y-m-d H:i') }}</div>
                                @empty
                                    <span style="color: var(--text-muted);">لا يوجد سجل قديم</span>
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">لا توجد طلبات.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection