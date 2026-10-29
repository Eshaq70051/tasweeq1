@extends('admin.layout')

@section('title', 'تفاصيل الطلب #' . $order->id . ' | تسويق')
@section('page_title', 'تفاصيل الفاتورة والطلب')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('admin.orders') }}" class="btn btn-outline" style="padding: 8px 14px;">
                <i class="fa-solid fa-arrow-right"></i>
                <span>العودة للطلبات</span>
            </a>
            <h2 style="margin:0; font-size: 20px; font-weight: 800; color: var(--primary);">طلب رقم #{{ $order->id }}</h2>
        </div>
        <div style="display: flex; gap: 10px;">
            @if(!$order->is_paid)
                <form action="{{ route('admin.orders.markPaid', $order) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check-double"></i>
                        <span>تأكيد استلام المبلغ</span>
                    </button>
                </form>
            @endif
            <button class="btn btn-outline" onclick="window.print()">
                <i class="fa-solid fa-print"></i>
                <span>طباعة الفاتورة</span>
            </button>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 25px;">
        <div class="panel">
            <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--primary); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-location-dot"></i>
                <span>بيانات العميل والتوصيل</span>
            </h3>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px dashed var(--border);">
                    <span style="color: var(--text-muted); font-size: 13px;">اسم العميل</span>
                    <span style="font-weight: 700;">{{ $order->customer_name }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px dashed var(--border);">
                    <span style="color: var(--text-muted); font-size: 13px;">رقم الهاتف</span>
                    <span style="font-weight: 700; direction: ltr;">{{ $order->customer_phone }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px dashed var(--border);">
                    <span style="color: var(--text-muted); font-size: 13px;">البريد الإلكتروني</span>
                    <span style="font-weight: 600;">{{ $order->customer_email ?? 'لا يوجد' }}</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <span style="color: var(--text-muted); font-size: 13px;">عنوان التوصيل</span>
                    <div style="padding: 12px 14px; background: #F8FBFB; border-radius: 12px; font-weight: 600; font-size: 13px; border: 1px solid var(--border);">
                        {{ $order->customer_address }}
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--primary); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-credit-card"></i>
                <span>حالة الدفع ومعلومات الطلب</span>
            </h3>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px dashed var(--border);">
                    <span style="color: var(--text-muted); font-size: 13px;">طريقة الدفع</span>
                    <span style="font-weight: 800; color: var(--secondary);">
                        @switch($order->payment_method)
                            @case('cash') الدفع عند الاستلام @break
                            @case('jaib') محفظة جيب @break
                            @case('one_cash') ون كاش @break
                            @case('jawali') جوالي @break
                            @case('kuraimi') الكريمي إم فلوس @break
                            @default {{ $order->payment_method ?? 'تحويل بنكي' }}
                        @endswitch
                    </span>
                </div>
                @if($order->transfer_number)
                    <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px dashed var(--border);">
                        <span style="color: var(--text-muted); font-size: 13px;">رقم الحوالة / العملية</span>
                        <span style="font-weight: 800;">{{ $order->transfer_number }}</span>
                    </div>
                @endif
                @if($order->receipt_image)
                    <div style="padding-bottom: 10px; border-bottom: 1px dashed var(--border);">
                        <span style="display:block; color: var(--text-muted); font-size: 13px; margin-bottom: 8px;">إيصال الدفع</span>
                        <a href="{{ route('media.file', ['path' => $order->receipt_image]) }}" target="_blank" style="display: inline-block;">
                            <img src="{{ route('media.file', ['path' => $order->receipt_image]) }}" alt="receipt" style="max-width:180px; max-height:140px; border-radius:12px; object-fit:contain; border:1px solid var(--border);">
                        </a>
                    </div>
                @endif
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px dashed var(--border);">
                    <span style="color: var(--text-muted); font-size: 13px;">حالة الدفع</span>
                    <span class="status-badge {{ $order->is_paid ? 'status-success' : 'status-danger' }}">
                        <i class="fa-solid {{ $order->is_paid ? 'fa-check' : 'fa-hourglass-half' }}" style="font-size: 10px;"></i>
                        <span>{{ $order->is_paid ? 'مدفوع بالكامل' : 'في انتظار الدفع' }}</span>
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px dashed var(--border);">
                    <span style="color: var(--text-muted); font-size: 13px;">توقيت الطلب</span>
                    <span style="font-weight: 600; font-size: 13px;">{{ $order->created_at->format('Y/m/d - H:i A') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-muted); font-size: 13px;">حالة الطلب الحالية</span>
                    <span class="status-badge status-pending" style="font-size: 13px;">
                        <i class="fa-solid fa-clock" style="font-size: 11px;"></i>
                        <span>{{ $order->status }}</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-basket-shopping" style="color: var(--primary);"></i>
            <span>محتويات وأصناف الطلب</span>
        </h3>
        <div class="table-responsive">
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
                        <td colspan="3" style="text-align: left; padding: 25px 15px 15px; font-size: 16px; font-weight: 700;">الإجمالي الكلي:</td>
                        <td style="text-align: left; padding: 25px 15px 15px; font-size: 22px; font-weight: 900; color: var(--primary);">{{ number_format($order->total_amount, 2) }} ريال</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if($order->notes)
        <div class="panel" style="margin-top: 24px; border-right: 4px solid var(--warning);">
            <h4 style="margin: 0 0 8px; color: var(--warning); display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-note-sticky"></i>
                <span>ملاحظات العميل</span>
            </h4>
            <p style="margin: 0; color: var(--text-muted); font-size: 13px;">{{ $order->notes }}</p>
        </div>
    @endif
@endsection
