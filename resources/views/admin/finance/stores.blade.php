@extends('admin.layout')

@section('title', 'حسابات المطاعم والمتاجر - الإدارة المالية')
@section('page_title', 'حسابات المطاعم والمتاجر')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: var(--text-main);">حسابات وذمم المطاعم والمتاجر</h2>
        <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 13px;">متابعة مبيعات الطلبات، نسب العمولات، المبالغ المدينة والدائنة، وصافي المستحقات</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('admin.finance.invoices') }}" class="btn btn-outline">
            <i class="fa-solid fa-file-invoice"></i>
            <span>فواتير وتسويات المطاعم</span>
        </a>
        <button type="button" class="btn btn-primary" onclick="openStoreTransactionModal()">
            <i class="fa-solid fa-plus-circle"></i>
            <span>تسجيل سند مالي لمطعم</span>
        </button>
    </div>
</div>

<!-- بطاقات الملخص الإجمالي لحسابات المطاعم -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي مبيعات المطاعم</span>
            <span class="value" style="color: var(--primary);">{{ number_format($totals['sales'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
        </div>
        <div class="icon-box"><i class="fa-solid fa-chart-line"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">عمولات التطبيق المستحقة</span>
            <span class="value" style="color: #27AE60;">{{ number_format($totals['commission'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
        </div>
        <div class="icon-box" style="background: rgba(39, 174, 96, 0.12); color: #27AE60;"><i class="fa-solid fa-percent"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي الدائن (مستحقات المطاعم)</span>
            <span class="value" style="color: #2980B9;">{{ number_format($totals['credit'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
        </div>
        <div class="icon-box" style="background: rgba(41, 128, 185, 0.12); color: #2980B9;"><i class="fa-solid fa-hand-holding-dollar"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي المدين (المسدد للمطاعم)</span>
            <span class="value" style="color: #E67E22;">{{ number_format($totals['debit'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
        </div>
        <div class="icon-box" style="background: rgba(230, 126, 34, 0.12); color: #E67E22;"><i class="fa-solid fa-money-bill-transfer"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">صافي المتبقي بذمة المنصة</span>
            <span class="value" style="color: {{ $totals['balance'] >= 0 ? '#27AE60' : '#E74C3C' }};">
                {{ number_format($totals['balance'], 2) }} <small style="font-size: 13px;">ر.ي</small>
            </span>
        </div>
        <div class="icon-box" style="background: rgba(6, 132, 148, 0.12); color: var(--primary);"><i class="fa-solid fa-scale-balanced"></i></div>
    </div>
</div>

<!-- بطاقة البحث والفلترة -->
<div class="filter-card">
    <form method="GET" action="{{ route('admin.finance.stores') }}" style="display: flex; gap: 12px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 250px;">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="ابحث باسم المتجر أو رقم الهاتف أو العنوان..." class="form-control" style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1px solid var(--border);">
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span>بحث</span>
        </button>
        @if(request()->has('q'))
            <a href="{{ route('admin.finance.stores') }}" class="btn btn-outline">إلغاء الفلتر</a>
        @endif
    </form>
</div>

<!-- جدول حسابات المطاعم -->
<div style="background: white; border-radius: 18px; border: 1px solid var(--border); padding: 22px; box-shadow: 0 4px 14px rgba(0,0,0,0.02);">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted);">
                    <th style="padding: 12px 14px;">المتجر / المطعم</th>
                    <th style="padding: 12px 14px; text-align: center;">الطلبات</th>
                    <th style="padding: 12px 14px;">إجمالي المبيعات</th>
                    <th style="padding: 12px 14px;">عمولة التطبيق</th>
                    <th style="padding: 12px 14px;">الدائن (له)</th>
                    <th style="padding: 12px 14px;">المدين (المسدد له)</th>
                    <th style="padding: 12px 14px;">صافي الرصيد</th>
                    <th style="padding: 12px 14px; text-align: center;">إجراءات مالية</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allStores as $store)
                    @php $fin = $storesFinancials[$store->id] ?? null; @endphp
                    <tr style="border-bottom: 1px solid var(--border); transition: background 0.15s ease;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">
                        <td style="padding: 14px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                @if($store->logo_url)
                                    <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" style="width: 40px; height: 40px; border-radius: 10px; object-fit: cover; border: 1px solid var(--border);">
                                @else
                                    <div style="width: 40px; height: 40px; border-radius: 10px; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800;">
                                        <i class="fa-solid fa-store"></i>
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.finance.stores.statement', $store) }}" style="font-weight: 800; color: var(--text-main); text-decoration: none; font-size: 14px;">
                                        {{ $store->name }}
                                    </a>
                                    <div style="color: var(--text-muted); font-size: 11px;">
                                        {{ $store->category?->name ?? 'بدون تصنيف' }} &bull; {{ $store->phone ?: 'لا يوجد هاتف' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 14px; text-align: center;">
                            <span style="background: #F4F7F8; padding: 4px 10px; border-radius: 99px; font-weight: 700;">
                                {{ $fin ? $fin['orders_count'] : 0 }}
                            </span>
                        </td>
                        <td style="padding: 14px; font-weight: 700;">
                            {{ number_format($fin ? $fin['total_sales'] : 0, 2) }} ر.ي
                        </td>
                        <td style="padding: 14px;">
                            <span style="color: #27AE60; font-weight: 700;">
                                {{ number_format($fin ? $fin['app_commission'] : 0, 2) }} ر.ي
                            </span>
                            <div style="font-size: 11px; color: var(--text-muted);">نسبة: {{ $store->commission_rate ?? 10 }}%</div>
                        </td>
                        <td style="padding: 14px; color: #2980B9; font-weight: 700;">
                            +{{ number_format($fin ? $fin['total_credit'] : 0, 2) }} ر.ي
                        </td>
                        <td style="padding: 14px; color: #E67E22; font-weight: 700;">
                            -{{ number_format($fin ? $fin['total_debit'] : 0, 2) }} ر.ي
                        </td>
                        <td style="padding: 14px;">
                            @php $bal = $fin ? $fin['balance'] : 0; @endphp
                            @if($bal > 0)
                                <div style="font-weight: 800; color: #27AE60; font-size: 14px;">
                                    {{ number_format($bal, 2) }} ر.ي
                                </div>
                                <span style="background: rgba(39, 174, 96, 0.1); color: #27AE60; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">
                                    له مستحق (دائن)
                                </span>
                            @elseif($bal < 0)
                                <div style="font-weight: 800; color: #E74C3C; font-size: 14px;">
                                    {{ number_format(abs($bal), 2) }} ر.ي
                                </div>
                                <span style="background: rgba(231, 76, 60, 0.1); color: #E74C3C; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">
                                    عليه مبالغ (مدين)
                                </span>
                            @else
                                <div style="font-weight: 700; color: #8C949A; font-size: 13px;">
                                    0.00 ر.ي
                                </div>
                                <span style="background: #F4F7F8; color: #8C949A; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">
                                    الحساب متزن
                                </span>
                            @endif
                        </td>
                        <td style="padding: 14px; text-align: center;">
                            <div style="display: inline-flex; gap: 6px;">
                                <a href="{{ route('admin.finance.stores.statement', $store) }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 11px;" title="كشف الحساب التفصيلي">
                                    <i class="fa-solid fa-file-invoice"></i>
                                    <span>كشف حساب</span>
                                </a>
                                <button type="button" class="btn btn-primary" style="padding: 6px 10px; font-size: 11px;" onclick="openPaymentForStore('{{ $store->id }}', '{{ addslashes($store->name) }}', '{{ $bal }}')" title="صرف مستحقات / سند مالي">
                                    <i class="fa-solid fa-hand-holding-dollar"></i>
                                    <span>صرف</span>
                                </button>
                                <button type="button" class="btn btn-outline" style="padding: 6px 10px; font-size: 11px;" onclick="openStoreSettingsModal('{{ $store->id }}', '{{ addslashes($store->name) }}', '{{ $store->commission_rate ?? 10 }}', '{{ addslashes($store->bank_name ?? '') }}', '{{ addslashes($store->account_number ?? '') }}', '{{ addslashes($store->iban ?? '') }}', '{{ addslashes($store->tax_number ?? '') }}')" title="إعدادات الحساب البنكي والعمولة">
                                    <i class="fa-solid fa-gear"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding: 40px; text-align: center; color: var(--text-muted);">
                            <i class="fa-solid fa-store-slash" style="font-size: 32px; color: #CBD5E1; margin-bottom: 10px; display: block;"></i>
                            لم يتم العثور على أي متاجر مطابقة لبحثك.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        <span class="info-text">عرض {{ $allStores->firstItem() ?? 0 }} إلى {{ $allStores->lastItem() ?? 0 }} من إجمالي {{ $allStores->total() }} متجر</span>
        {{ $allStores->links() }}
    </div>
</div>

<!-- Modal: تسجيل سند مالي لمطعم -->
<div id="storePaymentModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 550px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
        <div style="background: var(--primary); color: white; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 17px; font-weight: 800;">تسجيل عملية مالية لمطعم</h3>
            <button type="button" onclick="closeStorePaymentModal()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.finance.transactions.store') }}" style="padding: 24px;">
            @csrf
            <input type="hidden" name="party_type" value="store">
            <input type="hidden" name="party_id" id="modal_store_id" value="">
            <input type="hidden" name="party_name" id="modal_store_name_hidden" value="">

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">المتجر / المطعم</label>
                <input type="text" id="modal_store_name" readonly class="form-control" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); background: #F8FAFC; font-weight: 700;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">نوع السند <span style="color:red">*</span></label>
                    <select name="type" id="modal_txn_type" onchange="adjustDirection()" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 700;">
                        <option value="payment">سند صرف (تسليم مستحقات للمطعم)</option>
                        <option value="receipt">سند قبض (استلام مبالغ من المطعم)</option>
                        <option value="adjustment">قيد تسوية</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">الاتجاه المحاسبي <span style="color:red">*</span></label>
                    <select name="direction" id="modal_txn_direction" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 700;">
                        <option value="debit">مدين (على المطعم / تخفيض رصيده الدائن)</option>
                        <option value="credit">دائن (لصالح المطعم / إضافة لرصيده)</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">المبلغ (ر.ي) <span style="color:red">*</span></label>
                    <input type="number" step="0.01" name="amount" id="modal_store_amount" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 800; font-size: 15px;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">تاريخ العملية <span style="color:red">*</span></label>
                    <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">طريقة الدفع <span style="color:red">*</span></label>
                    <select name="payment_method" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                        <option value="cash">نقدي (كاش)</option>
                        <option value="bank_transfer">تحويل بنكي</option>
                        <option value="check">شيك</option>
                        <option value="wallet">محفظة إلكترونية</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">رقم المرجع / الحوالة</label>
                    <input type="text" name="reference_number" placeholder="مثلاً: REF-10923" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">البيان / عنوان العملية <span style="color:red">*</span></label>
                <input type="text" name="title" id="modal_store_title" required value="صرف مستحقات مبيعات" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
            </div>

            <div style="margin-bottom: 22px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">ملاحظات تفصيلية</label>
                <textarea name="description" rows="2" placeholder="أية ملاحظات إضافية حول السند..." style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeStorePaymentModal()">إلغاء</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-check"></i>
                    <span>حفظ واعتماد السند</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: إعدادات عمولة وحساب بنكي للمتجر -->
<div id="storeSettingsModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 500px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
        <div style="background: var(--text-main); color: white; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800;">إعدادات الحساب والعمولة</h3>
            <button type="button" onclick="closeStoreSettingsModal()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form id="storeSettingsForm" method="POST" action="" style="padding: 24px;">
            @csrf
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">نسبة عمولة التطبيق (%) <span style="color:red">*</span></label>
                <input type="number" step="0.1" name="commission_rate" id="settings_commission_rate" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 800;">
                <small style="color: var(--text-muted); font-size: 11px;">تقتطع المنصة هذه النسبة من إجمالي مبيعات طلبات المتجر</small>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">اسم البنك / المصرف</label>
                <input type="text" name="bank_name" id="settings_bank_name" placeholder="مثلاً: بنك الكريمي، بنك التضامن..." style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">رقم الحساب</label>
                <input type="text" name="account_number" id="settings_account_number" placeholder="رقم حساب المتجر للتحويلات" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">الآيبان IBAN</label>
                <input type="text" name="iban" id="settings_iban" placeholder="YE..." style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
            </div>

            <div style="margin-bottom: 22px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">الرقم الضريبي (إن وجد)</label>
                <input type="text" name="tax_number" id="settings_tax_number" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeStoreSettingsModal()">إلغاء</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                    <span>حفظ البيانات المالية</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPaymentForStore(id, name, balance) {
        document.getElementById('modal_store_id').value = id;
        document.getElementById('modal_store_name').value = name;
        document.getElementById('modal_store_name_hidden').value = name;
        document.getElementById('modal_store_amount').value = balance > 0 ? balance : '';
        document.getElementById('modal_store_title').value = "صرف مستحقات مبيعات لـ " + name;
        document.getElementById('storePaymentModal').style.display = 'flex';
    }

    function closeStorePaymentModal() {
        document.getElementById('storePaymentModal').style.display = 'none';
    }

    function adjustDirection() {
        const type = document.getElementById('modal_txn_type').value;
        const dir = document.getElementById('modal_txn_direction');
        if (type === 'payment') {
            dir.value = 'debit';
        } else if (type === 'receipt') {
            dir.value = 'credit';
        }
    }

    function openStoreSettingsModal(id, name, rate, bank, acc, iban, tax) {
        document.getElementById('storeSettingsForm').action = "/admin/finance/stores/" + id + "/financials";
        document.getElementById('settings_commission_rate').value = rate;
        document.getElementById('settings_bank_name').value = bank;
        document.getElementById('settings_account_number').value = acc;
        document.getElementById('settings_iban').value = iban;
        document.getElementById('settings_tax_number').value = tax;
        document.getElementById('storeSettingsModal').style.display = 'flex';
    }

    function closeStoreSettingsModal() {
        document.getElementById('storeSettingsModal').style.display = 'none';
    }
</script>
@endsection
