@extends('admin.layout')

@section('title', 'فواتير وتسويات المطاعم - الإدارة المالية')
@section('page_title', 'فواتير وتسويات المطاعم')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: var(--text-main);">فواتير وتسويات مبيعات المطاعم</h2>
        <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 13px;">إدارة الفواتير الدورية للمتاجر، احتساب العمولات، ومتابعة السداد</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('admin.finance.stores') }}" class="btn btn-outline">
            <i class="fa-solid fa-utensils"></i>
            <span>حسابات المطاعم</span>
        </a>
    </div>
</div>

<!-- بطاقات إحصائيات الفواتير -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي الفواتير الصادرة</span>
            <span class="value" style="color: var(--primary);">{{ $stats['total_invoices'] }}</span>
        </div>
        <div class="icon-box"><i class="fa-solid fa-file-invoice"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي المبالغ الصافية المستحقة</span>
            <span class="value" style="color: #2980B9;">{{ number_format($stats['total_amount'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
        </div>
        <div class="icon-box" style="background: rgba(41, 128, 185, 0.12); color: #2980B9;"><i class="fa-solid fa-money-check-dollar"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي المبالغ المسددة</span>
            <span class="value" style="color: #27AE60;">{{ number_format($stats['paid_amount'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
        </div>
        <div class="icon-box" style="background: rgba(39, 174, 96, 0.12); color: #27AE60;"><i class="fa-solid fa-circle-check"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">فواتير غير مسددة أو جزئية</span>
            <span class="value" style="color: #E74C3C;">{{ $stats['unpaid_count'] }}</span>
        </div>
        <div class="icon-box" style="background: rgba(231, 76, 60, 0.12); color: #E74C3C;"><i class="fa-solid fa-clock"></i></div>
    </div>
</div>

<!-- فلترة الفواتير -->
<div class="filter-card">
    <form method="GET" action="{{ route('admin.finance.invoices') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 180px;">
            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">المتجر</label>
            <select name="store_id" style="width: 100%; padding: 10px; border-radius: 12px; border: 1px solid var(--border);">
                <option value="">جميع المتاجر والمطاعم</option>
                @foreach($stores as $st)
                    <option value="{{ $st->id }}" {{ request('store_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="min-width: 140px;">
            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">الحالة</label>
            <select name="status" style="width: 100%; padding: 10px; border-radius: 12px; border: 1px solid var(--border);">
                <option value="">كافة الحالات</option>
                <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>غير مسددة</option>
                <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>مسددة جزئياً</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>مسددة بالكامل</option>
            </select>
        </div>
        <div style="min-width: 140px;">
            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">من تاريخ</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" style="width: 100%; padding: 9px; border-radius: 12px; border: 1px solid var(--border);">
        </div>
        <div style="min-width: 140px;">
            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">إلى تاريخ</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" style="width: 100%; padding: 9px; border-radius: 12px; border: 1px solid var(--border);">
        </div>
        <button type="submit" class="btn btn-primary" style="padding: 10px 18px;">
            <i class="fa-solid fa-filter"></i>
            <span>تصفية</span>
        </button>
        @if(request()->hasAny(['store_id', 'status', 'date_from', 'date_to']))
            <a href="{{ route('admin.finance.invoices') }}" class="btn btn-outline" style="padding: 10px 14px;">إلغاء</a>
        @endif
    </form>
</div>

<!-- جدول فواتير المطاعم -->
<div style="background: white; border-radius: 18px; border: 1px solid var(--border); padding: 22px; box-shadow: 0 4px 14px rgba(0,0,0,0.02);">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted);">
                    <th style="padding: 12px 14px;">رقم الفاتورة</th>
                    <th style="padding: 12px 14px;">المتجر</th>
                    <th style="padding: 12px 14px;">فترة التسوية</th>
                    <th style="padding: 12px 14px; text-align: center;">الطلبات</th>
                    <th style="padding: 12px 14px;">إجمالي المبيعات</th>
                    <th style="padding: 12px 14px;">عمولة المنصة</th>
                    <th style="padding: 12px 14px;">صافي المستحق</th>
                    <th style="padding: 12px 14px;">المدفوع</th>
                    <th style="padding: 12px 14px;">المتبقي</th>
                    <th style="padding: 12px 14px; text-align: center;">الحالة</th>
                    <th style="padding: 12px 14px; text-align: center;">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr style="border-bottom: 1px solid var(--border); transition: background 0.15s ease;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">
                        <td style="padding: 14px; font-weight: 800; color: var(--primary); font-family: monospace;">
                            {{ $inv->invoice_number }}
                        </td>
                        <td style="padding: 14px; font-weight: 700;">
                            <a href="{{ route('admin.finance.stores.statement', $inv->store_id) }}" style="text-decoration: none; color: var(--text-main);">
                                {{ $inv->store?->name ?? 'متجر غير معروف' }}
                            </a>
                        </td>
                        <td style="padding: 14px; color: var(--text-muted); font-size: 12px;">
                            {{ $inv->from_date ? $inv->from_date->format('Y/m/d') : '-' }} &larr; {{ $inv->to_date ? $inv->to_date->format('Y/m/d') : '-' }}
                        </td>
                        <td style="padding: 14px; text-align: center; font-weight: 700;">
                            <span style="background: #F4F7F8; padding: 3px 8px; border-radius: 99px;">{{ $inv->total_orders_count }}</span>
                        </td>
                        <td style="padding: 14px; font-weight: 700;">{{ number_format($inv->total_orders_amount, 2) }} ر.ي</td>
                        <td style="padding: 14px; color: #27AE60; font-weight: 700;">
                            {{ number_format($inv->commission_amount, 2) }} ر.ي
                            <small style="display: block; font-size: 10px; color: var(--text-muted);">{{ $inv->commission_rate }}%</small>
                        </td>
                        <td style="padding: 14px; font-weight: 800; color: #2980B9; font-size: 14px;">
                            {{ number_format($inv->net_amount, 2) }} ر.ي
                        </td>
                        <td style="padding: 14px; color: #27AE60; font-weight: 700;">
                            {{ number_format($inv->paid_amount, 2) }} ر.ي
                        </td>
                        <td style="padding: 14px; color: {{ $inv->remaining_amount > 0 ? '#E74C3C' : '#6C7278' }}; font-weight: 700;">
                            {{ number_format($inv->remaining_amount, 2) }} ر.ي
                        </td>
                        <td style="padding: 14px; text-align: center;">
                            @if($inv->status === 'paid')
                                <span style="background: rgba(39, 174, 96, 0.1); color: #27AE60; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 800;">مسددة بالكامل</span>
                            @elseif($inv->status === 'partial')
                                <span style="background: rgba(230, 126, 34, 0.1); color: #E67E22; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 800;">مسددة جزئياً</span>
                            @else
                                <span style="background: rgba(231, 76, 60, 0.1); color: #E74C3C; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 800;">غير مسددة</span>
                            @endif
                        </td>
                        <td style="padding: 14px; text-align: center;">
                            <div style="display: inline-flex; gap: 6px;">
                                <a href="{{ route('admin.finance.invoices.print', $inv) }}" target="_blank" class="btn btn-outline" style="padding: 6px 10px; font-size: 11px;" title="طباعة الفاتورة">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                <button type="button" class="btn btn-primary" style="padding: 6px 10px; font-size: 11px;" onclick="openPaymentModal('{{ $inv->id }}', '{{ $inv->invoice_number }}', '{{ $inv->net_amount }}', '{{ $inv->paid_amount }}', '{{ $inv->status }}', '{{ $inv->store?->name }}')" title="تحديث السداد">
                                    <i class="fa-solid fa-hand-holding-dollar"></i>
                                    <span>سداد</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="padding: 40px; text-align: center; color: var(--text-muted);">
                            <i class="fa-solid fa-file-invoice" style="font-size: 32px; color: #CBD5E1; margin-bottom: 10px; display: block;"></i>
                            لم يتم العثور على أي فواتير. يمكنك إنشاء فاتورة جديدة من صفحة كشف حساب المتجر.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        <span class="info-text">عرض {{ $invoices->firstItem() ?? 0 }} إلى {{ $invoices->lastItem() ?? 0 }} من إجمالي {{ $invoices->total() }} فاتورة</span>
        {{ $invoices->links() }}
    </div>
</div>

<!-- Modal: سداد الفاتورة -->
<div id="invoicePaymentModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 480px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
        <div style="background: var(--primary); color: white; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800;">تحديث سداد الفاتورة</h3>
            <button type="button" onclick="closeInvoicePaymentModal()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form id="invoicePaymentForm" method="POST" action="" style="padding: 24px;">
            @csrf
            <div style="margin-bottom: 14px; background: #F8FAFC; padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                <div style="font-size: 12px; color: var(--text-muted);">رقم الفاتورة: <strong id="modal_inv_num" style="color: var(--primary);"></strong></div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">المتجر: <strong id="modal_store_label"></strong></div>
                <div style="font-size: 13px; font-weight: 800; margin-top: 6px;">إجمالي المستحق: <span id="modal_inv_total" style="color: #2980B9;"></span> ر.ي</div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">حالة السداد <span style="color:red">*</span></label>
                <select name="status" id="modal_inv_status" onchange="autoFillAmount()" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 700;">
                    <option value="paid">مسددة بالكامل</option>
                    <option value="partial">مسددة جزئياً</option>
                    <option value="unpaid">غير مسددة</option>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">المبلغ المسدد (ر.ي) <span style="color:red">*</span></label>
                    <input type="number" step="0.01" name="paid_amount" id="modal_inv_paid" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 800;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">تاريخ السداد</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">طريقة الدفع</label>
                <select name="payment_method" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                    <option value="bank_transfer">تحويل بنكي</option>
                    <option value="cash">نقدي (كاش)</option>
                    <option value="check">شيك</option>
                </select>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; cursor: pointer;">
                    <input type="checkbox" name="record_transaction" value="1" checked style="width: 16px; height: 16px;">
                    <span>تسجيل سند صرف فوري للمتجر في دفتر الأستاذ</span>
                </label>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">ملاحظات السداد</label>
                <textarea name="notes" rows="2" placeholder="رقم الحوالة أو ملاحظات إضافية..." style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeInvoicePaymentModal()">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ وتحديث السداد</button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentNet = 0;
    function openPaymentModal(id, num, net, paid, status, storeName) {
        currentNet = parseFloat(net);
        document.getElementById('invoicePaymentForm').action = "/admin/finance/invoices/" + id + "/status";
        document.getElementById('modal_inv_num').textContent = num;
        document.getElementById('modal_store_label').textContent = storeName;
        document.getElementById('modal_inv_total').textContent = Number(net).toLocaleString();
        document.getElementById('modal_inv_paid').value = paid;
        document.getElementById('modal_inv_status').value = status;
        document.getElementById('invoicePaymentModal').style.display = 'flex';
    }

    function closeInvoicePaymentModal() {
        document.getElementById('invoicePaymentModal').style.display = 'none';
    }

    function autoFillAmount() {
        const st = document.getElementById('modal_inv_status').value;
        const paidInput = document.getElementById('modal_inv_paid');
        if (st === 'paid') {
            paidInput.value = currentNet;
        } else if (st === 'unpaid') {
            paidInput.value = 0;
        }
    }
</script>
@endsection
