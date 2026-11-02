@extends('admin.layout')

@section('title', 'العمليات والسندات المحاسبية - الإدارة المالية')
@section('page_title', 'العمليات والسندات المحاسبية')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: var(--text-main);">دفتر العمليات والسندات المالية</h2>
        <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 13px;">إنشاء وتعديل وإدارة كافة سندات القبض والصرف وقيود التسوية المحاسبية</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button type="button" class="btn btn-primary" onclick="openCreateTxnModal()">
            <i class="fa-solid fa-plus-circle"></i>
            <span>إنشاء عملية / سند جديد</span>
        </button>
    </div>
</div>

<!-- بطاقات إحصائيات السندات -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي المقبوضات (سندات قبض)</span>
            <span class="value" style="color: #27AE60;">{{ number_format($stats['total_receipts'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
            <small style="color: #27AE60; font-size: 11px; margin-top: 4px;"><i class="fa-solid fa-arrow-down-left"></i> إيداعات الخزينة والصندوق</small>
        </div>
        <div class="icon-box" style="background: rgba(39, 174, 96, 0.12); color: #27AE60;"><i class="fa-solid fa-hand-holding-dollar"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي المدفوعات (سندات صرف)</span>
            <span class="value" style="color: #E74C3C;">{{ number_format($stats['total_payments'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
            <small style="color: #E74C3C; font-size: 11px; margin-top: 4px;"><i class="fa-solid fa-arrow-up-right"></i> مدفوعات لمطاعم/رواتب/مصاريف</small>
        </div>
        <div class="icon-box" style="background: rgba(231, 76, 60, 0.12); color: #E74C3C;"><i class="fa-solid fa-money-bill-transfer"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">صافي السيولة النقدية</span>
            @php $net = $stats['total_receipts'] - $stats['total_payments']; @endphp
            <span class="value" style="color: {{ $net >= 0 ? '#27AE60' : '#E74C3C' }};">
                {{ number_format($net, 2) }} <small style="font-size: 13px;">ر.ي</small>
            </span>
            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">فارق القبض والصرف</small>
        </div>
        <div class="icon-box" style="background: rgba(6, 132, 148, 0.12); color: var(--primary);"><i class="fa-solid fa-vault"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي العمليات المسجلة</span>
            <span class="value" style="color: var(--primary);">{{ $stats['total_count'] }}</span>
            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">قيود وسندات معتمدة</small>
        </div>
        <div class="icon-box"><i class="fa-solid fa-receipt"></i></div>
    </div>
</div>

<!-- بطاقة البحث والفلترة المتقدمة -->
<div class="filter-card">
    <form method="GET" action="{{ route('admin.finance.transactions') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 180px;">
            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">بحث بالرقم أو البيان</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="رقم السند، البيان، اسم الطرف..." style="width: 100%; padding: 10px; border-radius: 12px; border: 1px solid var(--border);">
        </div>
        <div style="min-width: 130px;">
            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">نوع السند</label>
            <select name="type" style="width: 100%; padding: 10px; border-radius: 12px; border: 1px solid var(--border);">
                <option value="">كافة الأنواع</option>
                <option value="receipt" {{ request('type') === 'receipt' ? 'selected' : '' }}>سند قبض</option>
                <option value="payment" {{ request('type') === 'payment' ? 'selected' : '' }}>سند صرف</option>
                <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>قيد تسوية</option>
            </select>
        </div>
        <div style="min-width: 130px;">
            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">الطرف</label>
            <select name="party_type" style="width: 100%; padding: 10px; border-radius: 12px; border: 1px solid var(--border);">
                <option value="">كافة الأطراف</option>
                <option value="store" {{ request('party_type') === 'store' ? 'selected' : '' }}>مطعم / متجر</option>
                <option value="captain" {{ request('party_type') === 'captain' ? 'selected' : '' }}>كابتن</option>
                <option value="employee" {{ request('party_type') === 'employee' ? 'selected' : '' }}>موظف</option>
                <option value="general" {{ request('party_type') === 'general' ? 'selected' : '' }}>حساب عام / مصروفات</option>
            </select>
        </div>
        <div style="min-width: 130px;">
            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">طريقة الدفع</label>
            <select name="payment_method" style="width: 100%; padding: 10px; border-radius: 12px; border: 1px solid var(--border);">
                <option value="">الكل</option>
                <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>نقدي (كاش)</option>
                <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>تحويل بنكي</option>
                <option value="check" {{ request('payment_method') === 'check' ? 'selected' : '' }}>شيك</option>
            </select>
        </div>
        <div style="min-width: 130px;">
            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">من تاريخ</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" style="width: 100%; padding: 9px; border-radius: 12px; border: 1px solid var(--border);">
        </div>
        <div style="min-width: 130px;">
            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">إلى تاريخ</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" style="width: 100%; padding: 9px; border-radius: 12px; border: 1px solid var(--border);">
        </div>
        <button type="submit" class="btn btn-primary" style="padding: 10px 18px;">
            <i class="fa-solid fa-filter"></i>
            <span>تصفية</span>
        </button>
        @if(request()->hasAny(['q', 'type', 'party_type', 'payment_method', 'date_from', 'date_to']))
            <a href="{{ route('admin.finance.transactions') }}" class="btn btn-outline" style="padding: 10px 14px;">إلغاء</a>
        @endif
    </form>
</div>

<!-- جدول العمليات المحاسبية الشاملة -->
<div style="background: white; border-radius: 18px; border: 1px solid var(--border); padding: 22px; box-shadow: 0 4px 14px rgba(0,0,0,0.02);">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted);">
                    <th style="padding: 12px 14px;">رقم السند</th>
                    <th style="padding: 12px 14px;">التاريخ</th>
                    <th style="padding: 12px 14px;">نوع السند</th>
                    <th style="padding: 12px 14px;">الطرف المستفيد</th>
                    <th style="padding: 12px 14px;">البيان / الوصف</th>
                    <th style="padding: 12px 14px;">المبلغ (ر.ي)</th>
                    <th style="padding: 12px 14px;">الاتجاه</th>
                    <th style="padding: 12px 14px;">طريقة الدفع</th>
                    <th style="padding: 12px 14px; text-align: center;">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                    <tr style="border-bottom: 1px solid var(--border); transition: background 0.15s ease;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">
                        <td style="padding: 14px; font-weight: 800; color: var(--primary); font-family: monospace;">
                            {{ $txn->transaction_number }}
                        </td>
                        <td style="padding: 14px; color: var(--text-muted);">
                            {{ $txn->transaction_date ? \Carbon\Carbon::parse($txn->transaction_date)->format('Y/m/d') : '-' }}
                        </td>
                        <td style="padding: 14px;">
                            @if($txn->type === 'receipt')
                                <span style="background: rgba(39, 174, 96, 0.1); color: #27AE60; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 800;">
                                    <i class="fa-solid fa-arrow-down-left"></i> سند قبض
                                </span>
                            @elseif($txn->type === 'payment')
                                <span style="background: rgba(231, 76, 60, 0.1); color: #E74C3C; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 800;">
                                    <i class="fa-solid fa-arrow-up-right"></i> سند صرف
                                </span>
                            @else
                                <span style="background: rgba(41, 128, 185, 0.1); color: #2980B9; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 800;">
                                    <i class="fa-solid fa-scale-balanced"></i> قيد تسوية
                                </span>
                            @endif
                        </td>
                        <td style="padding: 14px; font-weight: 700;">
                            <span style="font-size: 10px; color: var(--text-muted); display: block; text-transform: uppercase;">
                                @if($txn->party_type === 'store') متجر / مطعم
                                @elseif($txn->party_type === 'captain') كابتن توصيل
                                @elseif($txn->party_type === 'employee') موظف
                                @else عام / خزينة @endif
                            </span>
                            {{ $txn->resolved_party_name }}
                        </td>
                        <td style="padding: 14px; max-width: 250px;">
                            <div style="font-weight: 700; color: var(--text-main);">{{ $txn->title }}</div>
                            @if($txn->description)
                                <small style="color: var(--text-muted); display: block; font-size: 11px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">{{ $txn->description }}</small>
                            @endif
                        </td>
                        <td style="padding: 14px; font-weight: 800; font-size: 14px; color: {{ $txn->type === 'receipt' ? '#27AE60' : '#E74C3C' }};">
                            {{ number_format($txn->amount, 2) }} ر.ي
                        </td>
                        <td style="padding: 14px;">
                            @if($txn->direction === 'debit')
                                <span style="color: #E74C3C; font-weight: 700; font-size: 12px;">مدين (عليه)</span>
                            @else
                                <span style="color: #27AE60; font-weight: 700; font-size: 12px;">دائن (له)</span>
                            @endif
                        </td>
                        <td style="padding: 14px; color: var(--text-muted); font-size: 12px;">
                            {{ $txn->payment_method === 'cash' ? 'نقدي (كاش)' : ($txn->payment_method === 'bank_transfer' ? 'تحويل بنكي' : $txn->payment_method) }}
                            @if($txn->reference_number)
                                <small style="display: block; font-family: monospace; font-size: 10px; color: var(--primary);">#{{ $txn->reference_number }}</small>
                            @endif
                        </td>
                        <td style="padding: 14px; text-align: center;">
                            <div style="display: inline-flex; gap: 5px;">
                                <a href="{{ route('admin.finance.transactions.print', $txn) }}" target="_blank" class="btn btn-outline" style="padding: 5px 8px; font-size: 11px;" title="طباعة السند">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                <button type="button" class="btn btn-outline" style="padding: 5px 8px; font-size: 11px;" onclick="openEditTxnModal('{{ $txn->id }}', '{{ $txn->transaction_number }}', '{{ $txn->type }}', '{{ $txn->party_type }}', '{{ $txn->party_id }}', '{{ addslashes($txn->party_name ?? '') }}', '{{ $txn->amount }}', '{{ $txn->direction }}', '{{ addslashes($txn->title) }}', '{{ addslashes($txn->description ?? '') }}', '{{ $txn->payment_method }}', '{{ addslashes($txn->reference_number ?? '') }}', '{{ $txn->transaction_date ? \Carbon\Carbon::parse($txn->transaction_date)->format('Y-m-d') : '' }}')" title="تعديل العملية">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.finance.transactions.delete', $txn) }}" onsubmit="return confirm('هل أنت متأكد من حذف العملية المحاسبية ({{ $txn->transaction_number }})؟');" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger" style="padding: 5px 8px; font-size: 11px;" title="حذف">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="padding: 40px; text-align: center; color: var(--text-muted);">
                            <i class="fa-solid fa-receipt" style="font-size: 32px; color: #CBD5E1; margin-bottom: 10px; display: block;"></i>
                            لم يتم العثور على أي عمليات مطابقة.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        <span class="info-text">عرض {{ $transactions->firstItem() ?? 0 }} إلى {{ $transactions->lastItem() ?? 0 }} من إجمالي {{ $transactions->total() }} عملية</span>
        {{ $transactions->links() }}
    </div>
</div>

<!-- Modal: إنشاء عملية / سند جديد -->
<div id="createTxnModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 620px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
        <div style="background: var(--primary); color: white; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800;">إنشاء عملية أو سند محاسبي جديد</h3>
            <button type="button" onclick="closeCreateTxnModal()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.finance.transactions.store') }}" enctype="multipart/form-data" style="padding: 24px;">
            @csrf
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">نوع السند <span style="color:red">*</span></label>
                    <select name="type" id="new_txn_type" onchange="onTypeChanged()" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 700;">
                        <option value="receipt">سند قبض (استلام أموال إلى الصندوق)</option>
                        <option value="payment">سند صرف (خروج أموال من الصندوق)</option>
                        <option value="adjustment">قيد تسوية / يومية</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">الاتجاه المحاسبي للطرف <span style="color:red">*</span></label>
                    <select name="direction" id="new_txn_direction" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 700;">
                        <option value="credit">دائن (لصالح الطرف)</option>
                        <option value="debit">مدين (على الطرف)</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">نوع الطرف <span style="color:red">*</span></label>
                    <select name="party_type" id="new_party_type" onchange="onPartyTypeChanged()" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 700;">
                        <option value="store">متجر / مطعم</option>
                        <option value="captain">كابتن توصيل</option>
                        <option value="employee">موظف</option>
                        <option value="general">حساب عام / إيراد ومصروفات أخرى</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">الطرف المحدد <span style="color:red">*</span></label>
                    
                    <!-- قائمة المتاجر -->
                    <select name="party_id" id="party_store_select" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                        <option value="">-- اختر المتجر / المطعم --</option>
                        @foreach($stores as $st)
                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                        @endforeach
                    </select>

                    <!-- قائمة الكباتن -->
                    <select name="party_id" id="party_captain_select" style="display: none; width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                        <option value="">-- اختر الكابتن --</option>
                        @foreach($captains as $cap)
                            <option value="{{ $cap->id }}">{{ $cap->name }} ({{ $cap->phone }})</option>
                        @endforeach
                    </select>

                    <!-- قائمة الموظفين -->
                    <select name="party_id" id="party_employee_select" style="display: none; width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                        <option value="">-- اختر الموظف --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->job_title }})</option>
                        @endforeach
                    </select>

                    <!-- حقل نصي للحساب العام -->
                    <input type="text" name="party_name" id="party_general_input" placeholder="اسم الجهة أو الحساب العام..." style="display: none; width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">المبلغ (ر.ي) <span style="color:red">*</span></label>
                    <input type="number" step="0.01" name="amount" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 800; font-size: 15px;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">تاريخ العملية <span style="color:red">*</span></label>
                    <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">طريقة الدفع <span style="color:red">*</span></label>
                    <select name="payment_method" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                        <option value="cash">نقدي (كاش بالخزينة)</option>
                        <option value="bank_transfer">تحويل بنكي / صرافة</option>
                        <option value="check">شيك</option>
                        <option value="wallet">محفظة إلكترونية</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">رقم المرجع / الإشعار</label>
                    <input type="text" name="reference_number" placeholder="رقم الشيك أو الحوالة" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">البيان / عنوان العملية <span style="color:red">*</span></label>
                <input type="text" name="title" required placeholder="مثال: تسديد دفعة مبيعات، توريد كاش، مصروف كهرباء..." style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">الوصف والملاحظات التفصيلية</label>
                <textarea name="description" rows="2" placeholder="تفاصيل أو أسباب العملية..." style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);"></textarea>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">مرفق / صورة السند أو الإيصال (اختياري)</label>
                <input type="file" name="attachment" accept="image/*,.pdf" style="width: 100%; font-size: 12px;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeCreateTxnModal()">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ واعتماد العملية</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: تعديل عملية محاسبية قائمة -->
<div id="editTxnModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 620px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
        <div style="background: var(--text-main); color: white; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800;">تعديل العملية المحاسبية: <span id="edit_modal_num"></span></h3>
            <button type="button" onclick="closeEditTxnModal()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form id="editTxnForm" method="POST" action="" enctype="multipart/form-data" style="padding: 24px;">
            @csrf
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">نوع السند <span style="color:red">*</span></label>
                    <select name="type" id="edit_txn_type" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 700;">
                        <option value="receipt">سند قبض</option>
                        <option value="payment">سند صرف</option>
                        <option value="adjustment">قيد تسوية</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">الاتجاه المحاسبي <span style="color:red">*</span></label>
                    <select name="direction" id="edit_txn_direction" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 700;">
                        <option value="debit">مدين (على الطرف)</option>
                        <option value="credit">دائن (لصالح الطرف)</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">نوع الطرف</label>
                    <select name="party_type" id="edit_party_type" onchange="onEditPartyTypeChanged()" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 700;">
                        <option value="store">متجر / مطعم</option>
                        <option value="captain">كابتن</option>
                        <option value="employee">موظف</option>
                        <option value="general">حساب عام</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">اسم / معرف الطرف</label>
                    <input type="text" name="party_name" id="edit_party_name" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                    <input type="hidden" name="party_id" id="edit_party_id">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">المبلغ (ر.ي) <span style="color:red">*</span></label>
                    <input type="number" step="0.01" name="amount" id="edit_amount" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 800; font-size: 15px;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">تاريخ العملية <span style="color:red">*</span></label>
                    <input type="date" name="transaction_date" id="edit_transaction_date" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">طريقة الدفع <span style="color:red">*</span></label>
                    <select name="payment_method" id="edit_payment_method" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                        <option value="cash">نقدي (كاش)</option>
                        <option value="bank_transfer">تحويل بنكي</option>
                        <option value="check">شيك</option>
                        <option value="wallet">محفظة إلكترونية</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">رقم المرجع / الإشعار</label>
                    <input type="text" name="reference_number" id="edit_reference_number" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">البيان / عنوان العملية <span style="color:red">*</span></label>
                <input type="text" name="title" id="edit_title" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">الوصف والملاحظات</label>
                <textarea name="description" id="edit_description" rows="2" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);"></textarea>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">تحديث المرفق (اختياري)</label>
                <input type="file" name="attachment" accept="image/*,.pdf" style="width: 100%; font-size: 12px;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeEditTxnModal()">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateTxnModal() {
        document.getElementById('createTxnModal').style.display = 'flex';
        onPartyTypeChanged();
    }
    function closeCreateTxnModal() {
        document.getElementById('createTxnModal').style.display = 'none';
    }

    function onTypeChanged() {
        const type = document.getElementById('new_txn_type').value;
        const dir = document.getElementById('new_txn_direction');
        if (type === 'receipt') {
            dir.value = 'credit'; // قبض من الطرف يجعله دائن
        } else if (type === 'payment') {
            dir.value = 'debit'; // صرف للطرف يجعله مدين
        }
    }

    function onPartyTypeChanged() {
        const pType = document.getElementById('new_party_type').value;
        const sSelect = document.getElementById('party_store_select');
        const cSelect = document.getElementById('party_captain_select');
        const eSelect = document.getElementById('party_employee_select');
        const gInput = document.getElementById('party_general_input');

        sSelect.style.display = 'none'; sSelect.disabled = true;
        cSelect.style.display = 'none'; cSelect.disabled = true;
        eSelect.style.display = 'none'; eSelect.disabled = true;
        gInput.style.display = 'none'; gInput.disabled = true;

        if (pType === 'store') {
            sSelect.style.display = 'block'; sSelect.disabled = false;
        } else if (pType === 'captain') {
            cSelect.style.display = 'block'; cSelect.disabled = false;
        } else if (pType === 'employee') {
            eSelect.style.display = 'block'; eSelect.disabled = false;
        } else {
            gInput.style.display = 'block'; gInput.disabled = false;
        }
    }

    function openEditTxnModal(id, num, type, pType, pId, pName, amount, direction, title, desc, method, ref, date) {
        document.getElementById('editTxnForm').action = "/admin/finance/transactions/" + id + "/update";
        document.getElementById('edit_modal_num').textContent = num;
        document.getElementById('edit_txn_type').value = type;
        document.getElementById('edit_party_type').value = pType;
        document.getElementById('edit_party_id').value = pId;
        document.getElementById('edit_party_name').value = pName;
        document.getElementById('edit_amount').value = amount;
        document.getElementById('edit_txn_direction').value = direction;
        document.getElementById('edit_title').value = title;
        document.getElementById('edit_description').value = desc;
        document.getElementById('edit_payment_method').value = method;
        document.getElementById('edit_reference_number').value = ref;
        document.getElementById('edit_transaction_date').value = date;
        document.getElementById('editTxnModal').style.display = 'flex';
    }

    function closeEditTxnModal() {
        document.getElementById('editTxnModal').style.display = 'none';
    }
</script>
@endsection
