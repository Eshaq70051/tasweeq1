@extends('admin.layout')

@section('title', 'تفاصيل الطلب #' . $order->id)
@section('page_title', 'تفاصيل الفاتورة والطلب')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="{{ route('admin.orders') }}" class="btn" style="background: white; border: 1px solid var(--border); color: var(--text-muted);">< العودة للطلبات</a>
            <h2 style="margin:0;">طلب رقم #{{ $order->id }}</h2>
        </div>
        <div style="display: flex; gap: 10px;">
            @if(!$order->is_paid)
                <form action="{{ route('admin.orders.markPaid', $order) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">تأكيد استلام المبلغ</button>
                </form>
            @endif
            <button class="btn" style="background: white; border: 1px solid var(--border);">طباعة الفاتورة 🖨️</button>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
        <div class="panel">
            <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--primary);">بيانات العميل والتوصيل</h3>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px dashed var(--border);">
                    <span style="color: var(--text-muted);">اسم العميل</span>
                    <span style="font-weight: 800;">{{ $order->customer_name }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px dashed var(--border);">
                    <span style="color: var(--text-muted);">رقم الهاتف</span>
                    <span style="font-weight: 800; dir: ltr;">{{ $order->customer_phone }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px dashed var(--border);">
                    <span style="color: var(--text-muted);">البريد الإلكتروني</span>
                    <span style="font-weight: 600;">{{ $order->customer_email ?? 'لا يوجد' }}</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <span style="color: var(--text-muted);">عنوان التوصيل</span>
                    <div style="padding: 15px; background: #F9FAFB; border-radius: 12px; font-weight: 600;">
                        {{ $order->customer_address }}
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--primary);">حالة الدفع والطلب</h3>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px dashed var(--border);">
                    <span style="color: var(--text-muted);">طريقة الدفع</span>
                    <span style="font-weight: 800; color: var(--secondary);">
                        @switch($order->payment_method)
                            @case('cash') الدفع عند الاستلام @break
                            @case('jaib') جيب @break
                            @case('one_cash') ون كاش @break
                            @case('jawali') جوالي @break
                            @default تحويل بنكي
                        @endswitch
                    </span>
                </div>
                @if($order->transfer_number)
                    <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px dashed var(--border);">
                        <span style="color: var(--text-muted);">رقم العملية</span>
                        <span style="font-weight: 800;">{{ $order->transfer_number }}</span>
                    </div>
                @endif
                @if($order->receipt_image)
                    <div>
                        <span style="display:block; color: var(--text-muted); margin-bottom:8px;">إيصال الدفع</span>
                        <a href="{{ route('media.file', ['path' => $order->receipt_image]) }}" target="_blank">
                            <img src="{{ route('media.file', ['path' => $order->receipt_image]) }}" alt="receipt" style="max-width:180px; max-height:140px; border-radius:12px; object-fit:contain; border:1px solid var(--border);">
                        </a>
                    </div>
                @endif
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px dashed var(--border);">
                    <span style="color: var(--text-muted);">حالة الدفع</span>
                    <span class="status-badge {{ $order->is_paid ? 'status-success' : 'status-danger' }}">
                        {{ $order->is_paid ? 'مدفوع بالكامل' : 'في انتظار الدفع' }}
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px dashed var(--border);">
                    <span style="color: var(--text-muted);">توقيت الطلب</span>
                    <span style="font-weight: 600;">{{ $order->created_at->format('Y/m/d - H:i A') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">حالة الطلب الحالية</span>
                    <span class="status-badge status-pending" style="font-size: 14px;">{{ $order->status }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <h3 style="margin-top: 0; margin-bottom: 25px;">محتويات الطلب</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th style="text-align: center;">الكمية</th>
                    <th>سعر الوحدة</th>
                    <th style="text-align: left;">الإجمالي الفرعي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            <div style="font-weight: 800;">{{ $item->product_name ?? 'منتج غير معرف' }}</div>
                        </td>
                        <td style="text-align: center; font-weight: 700;">{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price, 2) }} ريال</td>
                        <td style="text-align: left; font-weight: 900; color: var(--primary);">{{ number_format($item->subtotal, 2) }} ريال</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: left; padding: 30px 15px 15px; font-size: 18px; font-weight: 700;">الإجمالي الكلي:</td>
                    <td style="text-align: left; padding: 30px 15px 15px; font-size: 24px; font-weight: 900; color: var(--primary);">{{ number_format($order->total_amount, 2) }} ريال</td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($order->notes)
        <div class="panel" style="margin-top: 30px; border-right: 5px solid var(--warning);">
            <h4 style="margin: 0 0 10px; color: var(--warning);">ملاحظات العميل</h4>
            <p style="margin: 0; color: var(--text-muted);">{{ $order->notes }}</p>
        </div>
    @endif
@endsection
