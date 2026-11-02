@extends('admin.layout')

@section('title', 'كشف حساب ' . $store->name . ' - الإدارة المالية')
@section('page_title', 'كشف حساب مالي تفصيلي')

@section('content')
<style>
    @media print {
        body { background: white !important; }
        .sidebar, .top-bar, .no-print, .btn, .pagination-container { display: none !important; }
        .main-content { margin-right: 0 !important; padding: 0 !important; width: 100% !important; }
        .print-only { display: block !important; }
        .panel-print { border: none !important; box-shadow: none !important; }
    }
    .print-only { display: none; }
</style>

<!-- شريط الإجراءات والترويسة -->
<div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <div style="display: flex; align-items: center; gap: 14px;">
        <a href="{{ route('admin.finance.stores') }}" class="btn btn-outline" style="padding: 8px 14px;">
            <i class="fa-solid fa-arrow-right"></i>
            <span>رجوع للمطاعم</span>
        </a>
        <div>
            <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: var(--text-main);">كشف حساب: {{ $store->name }}</h2>
            <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 13px;">سجل المبيعات، عمولات التطبيق، الدفعات المسددة، والفواتير</p>
        </div>
    </div>
    <div style="display: flex; gap: 10px;">
        <button type="button" onclick="window.print()" class="btn btn-outline">
            <i class="fa-solid fa-print"></i>
            <span>طباعة كشف الحساب</span>
        </button>
        <button type="button" class="btn btn-outline" onclick="openInvoiceModal()">
            <i class="fa-solid fa-file-circle-plus"></i>
            <span>إصدار فاتورة تسوية</span>
        </button>
        <button type="button" class="btn btn-primary" onclick="openPaymentModal()">
            <i class="fa-solid fa-money-bill-wave"></i>
            <span>تسجيل سند صرف / قبض</span>
        </button>
    </div>
</div>

<!-- ترويسة خاصة بالطباعة الرسمية -->
<div class="print-only" style="margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 15px;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="margin:0; font-size: 24px; font-weight: 800; color: #068494;">منصة تسويق - كشف حساب مالي</h1>
            <p style="margin:4px 0; font-size: 13px; color: #555;">الطرف: <strong>{{ $store->name }}</strong> &bull; التاريخ: {{ date('Y/m/d H:i') }}</p>
        </div>
        <div style="text-align: left; font-size: 12px; color: #555;">
            <div>هاتف المتجر: {{ $store->phone ?: 'غير محدد' }}</div>
            <div>العنوان: {{ $store->address ?: 'غير محدد' }}</div>
            <div>نسبة العمولة: {{ $store->commission_rate ?? 10 }}%</div>
        </div>
    </div>
</div>

<!-- بطاقة معلومات المتجر والبيانات البنكية -->
<div style="background: white; border-radius: 18px; border: 1px solid var(--border); padding: 22px; margin-bottom: 25px; box-shadow: 0 4px 14px rgba(0,0,0,0.02);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            @if($store->logo_url)
                <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" style="width: 56px; height: 56px; border-radius: 14px; object-fit: cover; border: 1px solid var(--border);">
            @else
                <div style="width: 56px; height: 56px; border-radius: 14px; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="fa-solid fa-store"></i>
                </div>
            @endif
            <div>
                <h3 style="margin: 0; font-size: 18px; font-weight: 800;">{{ $store->name }}</h3>
                <div style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">
                    <span><i class="fa-solid fa-phone" style="margin-left: 4px;"></i> {{ $store->phone ?: 'لا يوجد هاتف' }}</span>
                    <span style="margin: 0 10px;">&bull;</span>
                    <span><i class="fa-solid fa-location-dot" style="margin-left: 4px;"></i> {{ $store->address ?: 'اليمن' }}</span>
                    <span style="margin: 0 10px;">&bull;</span>
                    <span>نسبة العمولة: <strong>{{ $store->commission_rate ?? 10 }}%</strong></span>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 16px; background: #F8FAFC; padding: 12px 18px; border-radius: 14px; border: 1px solid var(--border); font-size: 12px;">
            <div>
                <span style="color: var(--text-muted); display: block;">البنك:</span>
                <strong>{{ $store->bank_name ?: 'غير مسجل' }}</strong>
            </div>
            <div style="border-right: 1px solid var(--border); padding-right: 16px;">
                <span style="color: var(--text-muted); display: block;">رقم الحساب:</span>
                <strong style="font-family: monospace;">{{ $store->account_number ?: 'غير مسجل' }}</strong>
            </div>
            <div style="border-right: 1px solid var(--border); padding-right: 16px;">
                <span style="color: var(--text-muted); display: block;">الآيبان IBAN:</span>
                <strong style="font-family: monospace;">{{ $store->iban ?: 'غير مسجل' }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- بطاقات الموقف المالي للمتجر -->
<div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي المبيعات (الطلبات)</span>
            <span class="value" style="color: var(--primary);">{{ number_format($summary['total_sales'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">{{ $summary['orders_count'] }} طلب مكتمل</small>
        </div>
        <div class="icon-box"><i class="fa-solid fa-bag-shopping"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">عمولة التطبيق ({{ $summary['commission_rate'] }}%)</span>
            <span class="value" style="color: #27AE60;">{{ number_format($summary['app_commission'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
            <small style="color: #27AE60; font-size: 11px; margin-top: 4px;">مستحقات المنصة</small>
        </div>
        <div class="icon-box" style="background: rgba(39, 174, 96, 0.12); color: #27AE60;"><i class="fa-solid fa-percent"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي الدائن (حقوق المتجر)</span>
            <span class="value" style="color: #2980B9;">{{ number_format($summary['total_credit'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">صافي مبيعات + تسويات</small>
        </div>
        <div class="icon-box" style="background: rgba(41, 128, 185, 0.12); color: #2980B9;"><i class="fa-solid fa-arrow-down-left"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي المدين (المسدد له)</span>
            <span class="value" style="color: #E67E22;">{{ number_format($summary['total_debit'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">سندات صرف مبالغ</small>
        </div>
        <div class="icon-box" style="background: rgba(230, 126, 34, 0.12); color: #E67E22;"><i class="fa-solid fa-arrow-up-right"></i></div>
    </div>
    <div class="stat-card" style="border: 2px solid {{ $summary['balance'] > 0 ? '#27AE60' : ($summary['balance'] < 0 ? '#E74C3C' : 'var(--border)') }};">
        <div class="info">
            <span class="title">صافي الرصيد الحالي</span>
            <span class="value" style="color: {{ $summary['balance'] > 0 ? '#27AE60' : ($summary['balance'] < 0 ? '#E74C3C' : '#6C7278') }};">
                {{ number_format(abs($summary['balance']), 2) }} <small style="font-size: 13px;">ر.ي</small>
            </span>
            <small style="font-weight: 800; color: {{ $summary['balance'] > 0 ? '#27AE60' : ($summary['balance'] < 0 ? '#E74C3C' : '#6C7278') }}; margin-top: 4px;">
                @if($summary['balance'] > 0)
                    &bull; له بذمة المنصة (دائن)
                @elseif($summary['balance'] < 0)
                    &bull; عليه للمنصة (مدين)
                @else
                    &bull; الحساب متزن (0)
                @endif
            </small>
        </div>
        <div class="icon-box" style="background: rgba(6, 132, 148, 0.12); color: var(--primary);"><i class="fa-solid fa-scale-balanced"></i></div>
    </div>
</div>

<!-- جدول العمليات المحاسبية والسندات -->
<div style="background: white; border-radius: 18px; border: 1px solid var(--border); padding: 22px; margin-bottom: 25px; box-shadow: 0 4px 14px rgba(0,0,0,0.02);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 style="margin: 0; font-size: 16px; font-weight: 800;">سجل العمليات المالية والسندات المتبادلة (دفتر الأستاذ)</h3>
        <button type="button" class="btn btn-outline" onclick="openPaymentModal()" style="font-size: 12px; padding: 6px 14px;">
            <i class="fa-solid fa-plus"></i> إضافة سند
        </button>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted);">
                    <th style="padding: 10px 14px;">رقم السند</th>
                    <th style="padding: 10px 14px;">التاريخ</th>
                    <th style="padding: 10px 14px;">نوع السند</th>
                    <th style="padding: 10px 14px;">البيان</th>
                    <th style="padding: 10px 14px;">المدين (صرف للمتجر)</th>
                    <th style="padding: 10px 14px;">الدائن (إيداع/تسوية)</th>
                    <th style="padding: 10px 14px;">طريقة الدفع</th>
                    <th style="padding: 10px 14px; text-align: center;" class="no-print">طباعة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px 14px; font-weight: 700; color: var(--primary); font-family: monospace;">{{ $txn->transaction_number }}</td>
                        <td style="padding: 12px 14px; color: var(--text-muted);">{{ $txn->transaction_date ? \Carbon\Carbon::parse($txn->transaction_date)->format('Y/m/d') : '-' }}</td>
                        <td style="padding: 12px 14px;">
                            @if($txn->type === 'payment')
                                <span style="background: rgba(231, 76, 60, 0.1); color: #E74C3C; padding: 3px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">سند صرف</span>
                            @elseif($txn->type === 'receipt')
                                <span style="background: rgba(39, 174, 96, 0.1); color: #27AE60; padding: 3px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">سند قبض</span>
                            @else
                                <span style="background: rgba(41, 128, 185, 0.1); color: #2980B9; padding: 3px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">قيد تسوية</span>
                            @endif
                        </td>
                        <td style="padding: 12px 14px;">{{ $txn->title }}</td>
                        <td style="padding: 12px 14px; font-weight: 700; color: #E74C3C;">
                            {{ $txn->direction === 'debit' ? number_format($txn->amount, 2) . ' ر.ي' : '-' }}
                        </td>
                        <td style="padding: 12px 14px; font-weight: 700; color: #27AE60;">
                            {{ $txn->direction === 'credit' ? number_format($txn->amount, 2) . ' ر.ي' : '-' }}
                        </td>
                        <td style="padding: 12px 14px; color: var(--text-muted); font-size: 12px;">{{ $txn->payment_method }}</td>
                        <td style="padding: 12px 14px; text-align: center;" class="no-print">
                            <a href="{{ route('admin.finance.transactions.print', $txn) }}" target="_blank" class="btn btn-outline" style="padding: 4px 8px; font-size: 11px;">
                                <i class="fa-solid fa-print"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding: 25px; text-align: center; color: var(--text-muted);">لم يتم تسجيل أي سندات مالية لهذا المتجر حتى الآن.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- جدول الطلبات الخاصة بالمتجر -->
<div style="background: white; border-radius: 18px; border: 1px solid var(--border); padding: 22px; margin-bottom: 25px; box-shadow: 0 4px 14px rgba(0,0,0,0.02);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 style="margin: 0; font-size: 16px; font-weight: 800;">طلبات المتجر ومبيعاتها</h3>
        <span style="font-size: 13px; color: var(--text-muted); font-weight: 600;">عرض أحدث الطلبات</span>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted);">
                    <th style="padding: 10px 14px;">رقم الطلب</th>
                    <th style="padding: 10px 14px;">تاريخ الطلب</th>
                    <th style="padding: 10px 14px;">العميل</th>
                    <th style="padding: 10px 14px;">إجمالي الطلب</th>
                    <th style="padding: 10px 14px;">عمولة التطبيق ({{ $summary['commission_rate'] }}%)</th>
                    <th style="padding: 10px 14px;">صافي حق المتجر</th>
                    <th style="padding: 10px 14px;">الحالة</th>
                    <th style="padding: 10px 14px; text-align: center;" class="no-print">التفاصيل</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    @php
                        $rate = $store->commission_rate > 0 ? $store->commission_rate : 10.0;
                        $appComm = round(($order->total_amount * $rate) / 100, 2);
                        $storeNet = max(0, $order->total_amount - $appComm);
                    @endphp
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px 14px; font-weight: 700; color: var(--primary);">#{{ $order->id }}</td>
                        <td style="padding: 12px 14px; color: var(--text-muted);">{{ $order->created_at->format('Y/m/d H:i') }}</td>
                        <td style="padding: 12px 14px;">{{ $order->customer_name }}</td>
                        <td style="padding: 12px 14px; font-weight: 700;">{{ number_format($order->total_amount, 2) }} ر.ي</td>
                        <td style="padding: 12px 14px; color: #27AE60; font-weight: 700;">{{ number_format($appComm, 2) }} ر.ي</td>
                        <td style="padding: 12px 14px; color: #2980B9; font-weight: 800;">{{ number_format($storeNet, 2) }} ر.ي</td>
                        <td style="padding: 12px 14px;">
                            @if($order->status === 'completed')
                                <span style="background: rgba(39, 174, 96, 0.1); color: #27AE60; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">مكتمل</span>
                            @else
                                <span style="background: #F4F7F8; color: var(--text-muted); padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">{{ $order->status }}</span>
                            @endif
                        </td>
                        <td style="padding: 12px 14px; text-align: center;" class="no-print">
                            <a href="{{ route('admin.orders.show', $order) }}" target="_blank" class="btn btn-outline" style="padding: 4px 8px; font-size: 11px;">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding: 25px; text-align: center; color: var(--text-muted);">لا توجد طلبات مسجلة لهذا المتجر.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-container no-print">
        {{ $orders->links() }}
    </div>
</div>

<!-- Modal: تسجيل سند مالي للمتجر -->
<div id="paymentModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 520px; overflow: hidden;">
        <div style="background: var(--primary); color: white; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800;">تسجيل سند مالي لـ {{ $store->name }}</h3>
            <button type="button" onclick="closePaymentModal()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.finance.transactions.store') }}" style="padding: 22px;">
            @csrf
            <input type="hidden" name="party_type" value="store">
            <input type="hidden" name="party_id" value="{{ $store->id }}">
            <input type="hidden" name="party_name" value="{{ $store->name }}">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">نوع السند <span style="color:red">*</span></label>
                    <select name="type" id="dir_type" onchange="autoDir()" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                        <option value="payment">سند صرف (تسليم مستحقات للمتجر)</option>
                        <option value="receipt">سند قبض (استلام مبالغ من المتجر)</option>
                        <option value="adjustment">قيد تسوية</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">الاتجاه <span style="color:red">*</span></label>
                    <select name="direction" id="dir_val" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                        <option value="debit">مدين (صرف للمتجر)</option>
                        <option value="credit">دائن (لصالح المتجر)</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">المبلغ (ر.ي) <span style="color:red">*</span></label>
                    <input type="number" step="0.01" name="amount" value="{{ $summary['balance'] > 0 ? $summary['balance'] : '' }}" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 800;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">التاريخ <span style="color:red">*</span></label>
                    <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">طريقة الدفع <span style="color:red">*</span></label>
                    <select name="payment_method" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                        <option value="bank_transfer">تحويل بنكي</option>
                        <option value="cash">نقدي (كاش)</option>
                        <option value="check">شيك</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">رقم المرجع / الإشعار</label>
                    <input type="text" name="reference_number" placeholder="رقم الحوالة أو الشيك" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">البيان <span style="color:red">*</span></label>
                <input type="text" name="title" value="صرف مستحقات مبيعات لـ {{ $store->name }}" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px;">
                <button type="button" class="btn btn-outline" onclick="closePaymentModal()">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ السند</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: إنشاء فاتورة تسوية للمتجر -->
<div id="invoiceModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 480px; overflow: hidden;">
        <div style="background: var(--primary); color: white; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800;">إصدار فاتورة تسوية لـ {{ $store->name }}</h3>
            <button type="button" onclick="closeInvoiceModal()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.finance.stores.generateInvoice', $store) }}" style="padding: 22px;">
            @csrf
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">من تاريخ <span style="color:red">*</span></label>
                <input type="date" name="from_date" value="{{ date('Y-m-01') }}" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
            </div>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">إلى تاريخ <span style="color:red">*</span></label>
                <input type="date" name="to_date" value="{{ date('Y-m-d') }}" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">ملاحظات الفاتورة</label>
                <textarea name="notes" rows="2" placeholder="أية تفاصيل إضافية حول التسوية..." style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeInvoiceModal()">إلغاء</button>
                <button type="submit" class="btn btn-primary">إصدار الفاتورة الآن</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPaymentModal() {
        document.getElementById('paymentModal').style.display = 'flex';
    }
    function closePaymentModal() {
        document.getElementById('paymentModal').style.display = 'none';
    }
    function autoDir() {
        const type = document.getElementById('dir_type').value;
        const dir = document.getElementById('dir_val');
        if (type === 'payment') dir.value = 'debit';
        else if (type === 'receipt') dir.value = 'credit';
    }
    function openInvoiceModal() {
        document.getElementById('invoiceModal').style.display = 'flex';
    }
    function closeInvoiceModal() {
        document.getElementById('invoiceModal').style.display = 'none';
    }
</script>
@endsection
