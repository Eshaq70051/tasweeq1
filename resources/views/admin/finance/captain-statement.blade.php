@extends('admin.layout')

@section('title', 'كشف حساب الكابتن ' . $captain->name . ' - الإدارة المالية')
@section('page_title', 'كشف حساب كابتن تفصيلي')

@section('content')
<style>
    @media print {
        body { background: white !important; }
        .sidebar, .top-bar, .no-print, .btn, .pagination-container { display: none !important; }
        .main-content { margin-right: 0 !important; padding: 0 !important; width: 100% !important; }
        .print-only { display: block !important; }
    }
    .print-only { display: none; }
</style>

<!-- شريط الإجراءات والترويسة -->
<div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <div style="display: flex; align-items: center; gap: 14px;">
        <a href="{{ route('admin.finance.captains') }}" class="btn btn-outline" style="padding: 8px 14px;">
            <i class="fa-solid fa-arrow-right"></i>
            <span>رجوع للكباتن</span>
        </a>
        <div>
            <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: var(--text-main);">كشف حساب الكابتن: {{ $captain->name }}</h2>
            <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 13px;">حركات التحصيل النقدي، العمولات، وسدادات الذمة المالية</p>
        </div>
    </div>
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <button type="button" class="btn btn-primary" onclick="openEditCaptainModal()">
            <i class="fa-solid fa-user-pen"></i>
            <span>تعديل بيانات الكابتن</span>
        </button>
        <button type="button" onclick="window.print()" class="btn btn-outline">
            <i class="fa-solid fa-print"></i>
            <span>طباعة كشف الحساب</span>
        </button>
        <button type="button" class="btn btn-outline" onclick="openReceiptModal()">
            <i class="fa-solid fa-hand-holding-dollar"></i>
            <span>تسجيل سند قبض</span>
        </button>
    </div>
</div>

<!-- ترويسة خاصة بالطباعة الرسمية -->
<div class="print-only" style="margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 15px;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="margin:0; font-size: 24px; font-weight: 800; color: #068494;">منصة تسويق - كشف حساب كابتن</h1>
            <p style="margin:4px 0; font-size: 13px; color: #555;">الكابتن: <strong>{{ $captain->name }}</strong> ({{ $captain->phone ?: 'بدون هاتف' }}) &bull; التاريخ: {{ date('Y/m/d H:i') }}</p>
        </div>
        <div style="text-align: left; font-size: 13px;">
            <div>الطلبات المسلمة: <strong>{{ $summary['completed_orders_count'] }}</strong></div>
            <div>صافي المديونية: <strong>{{ number_format($summary['outstanding_debt'], 2) }} ر.ي</strong></div>
        </div>
    </div>
</div>

<!-- بطاقات الموقف المالي للكابتن -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي الكاش المحصل (مدين عليه)</span>
            <span class="value" style="color: #E67E22;">{{ number_format($summary['cash_collected'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">{{ $summary['completed_orders_count'] }} طلب نقدي</small>
        </div>
        <div class="icon-box" style="background: rgba(230, 126, 34, 0.12); color: #E67E22;"><i class="fa-solid fa-coins"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">عمولات التوصيل المكتسبة (دائن له)</span>
            <span class="value" style="color: #27AE60;">{{ number_format($summary['earned_commission'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
            <small style="color: #27AE60; font-size: 11px; margin-top: 4px;">أرباح التوصيل</small>
        </div>
        <div class="icon-box" style="background: rgba(39, 174, 96, 0.12); color: #27AE60;"><i class="fa-solid fa-route"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">المبالغ المسددة للإدارة</span>
            <span class="value" style="color: #2980B9;">{{ number_format($summary['paid_debt'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">توريدات كاش معتمدة</small>
        </div>
        <div class="icon-box" style="background: rgba(41, 128, 185, 0.12); color: #2980B9;"><i class="fa-solid fa-circle-check"></i></div>
    </div>
    <div class="stat-card" style="border: 2px solid {{ $summary['outstanding_debt'] > 0 ? '#E74C3C' : '#27AE60' }};">
        <div class="info">
            <span class="title">صافي المديونية المتبقية</span>
            <span class="value" style="color: {{ $summary['outstanding_debt'] > 0 ? '#E74C3C' : '#27AE60' }};">
                {{ number_format($summary['outstanding_debt'], 2) }} <small style="font-size: 13px;">ر.ي</small>
            </span>
            <small style="font-weight: 700; color: {{ $summary['outstanding_debt'] > 0 ? '#E74C3C' : '#27AE60' }}; margin-top: 4px;">
                {{ $summary['outstanding_debt'] > 0 ? 'مستحق توريده للخزينة' : 'ذمة الكابتن مبرأة' }}
            </small>
        </div>
        <div class="icon-box" style="background: rgba(231, 76, 60, 0.12); color: #E74C3C;"><i class="fa-solid fa-scale-balanced"></i></div>
    </div>
</div>

<!-- قيود دفتر الأستاذ والسندات المالية -->
<div style="background: white; border-radius: 18px; border: 1px solid var(--border); padding: 22px; margin-bottom: 25px; box-shadow: 0 4px 14px rgba(0,0,0,0.02);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 style="margin: 0; font-size: 16px; font-weight: 800;">سجل العمليات والقيود المالية (دفتر الأستاذ)</h3>
        <button type="button" class="btn btn-outline no-print" onclick="openReceiptModal()" style="font-size: 12px; padding: 6px 14px;">
            <i class="fa-solid fa-plus"></i> إضافة سند قبض
        </button>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted);">
                    <th style="padding: 10px 14px;">التاريخ</th>
                    <th style="padding: 10px 14px;">نوع الحركة</th>
                    <th style="padding: 10px 14px;">البيان</th>
                    <th style="padding: 10px 14px;">المدين (عليه)</th>
                    <th style="padding: 10px 14px;">الدائن (له/مسدد)</th>
                    <th style="padding: 10px 14px;">الرصيد بعد الحركة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ledgerEntries as $entry)
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px 14px; color: var(--text-muted);">{{ $entry->created_at->format('Y/m/d H:i') }}</td>
                        <td style="padding: 12px 14px;">
                            @if($entry->direction === 'debit')
                                <span style="background: rgba(231, 76, 60, 0.1); color: #E74C3C; padding: 3px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">مدين (تحصيل كاش)</span>
                            @else
                                <span style="background: rgba(39, 174, 96, 0.1); color: #27AE60; padding: 3px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">دائن (سداد / عمولة)</span>
                            @endif
                        </td>
                        <td style="padding: 12px 14px;">{{ $entry->description }}</td>
                        <td style="padding: 12px 14px; font-weight: 700; color: #E74C3C;">
                            {{ $entry->direction === 'debit' ? number_format($entry->amount, 2) . ' ر.ي' : '-' }}
                        </td>
                        <td style="padding: 12px 14px; font-weight: 700; color: #27AE60;">
                            {{ $entry->direction === 'credit' ? number_format($entry->amount, 2) . ' ر.ي' : '-' }}
                        </td>
                        <td style="padding: 12px 14px; font-weight: 800; font-family: monospace;">
                            {{ number_format($entry->balance_after, 2) }} ر.ي
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 25px; text-align: center; color: var(--text-muted);">لا توجد قيود مسجلة في دفتر الأستاذ حتى الآن.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: تسجيل سند قبض كاش -->
<div id="receiptModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 500px; overflow: hidden;">
        <div style="background: var(--primary); color: white; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800;">تسجيل سند قبض نقدية من {{ $captain->name }}</h3>
            <button type="button" onclick="closeReceiptModal()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.finance.transactions.store') }}" style="padding: 22px;">
            @csrf
            <input type="hidden" name="type" value="receipt">
            <input type="hidden" name="party_type" value="captain">
            <input type="hidden" name="party_id" value="{{ $captain->id }}">
            <input type="hidden" name="party_name" value="{{ $captain->name }}">
            <input type="hidden" name="direction" value="credit">

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">المبلغ المقبوض (ر.ي) <span style="color:red">*</span></label>
                <input type="number" step="0.01" name="amount" value="{{ $summary['outstanding_debt'] > 0 ? $summary['outstanding_debt'] : '' }}" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 800; font-size: 15px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">التاريخ <span style="color:red">*</span></label>
                    <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">طريقة القبض <span style="color:red">*</span></label>
                    <select name="payment_method" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                        <option value="cash">نقدي بالخزينة (كاش)</option>
                        <option value="bank_transfer">تحويل بنكي / صرافة</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">البيان <span style="color:red">*</span></label>
                <input type="text" name="title" value="توريد كاش مديونية من الكابتن {{ $captain->name }}" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">ملاحظات</label>
                <textarea name="description" rows="2" placeholder="رقم السند أو ملاحظات..." style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeReceiptModal()">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ السند وتحديث الذمة</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReceiptModal() {
        document.getElementById('receiptModal').style.display = 'flex';
    }
    function closeReceiptModal() {
        document.getElementById('receiptModal').style.display = 'none';
    }
    function openEditCaptainModal() {
        document.getElementById('editCaptainModal').style.display = 'flex';
    }
    function closeEditCaptainModal() {
        document.getElementById('editCaptainModal').style.display = 'none';
    }
</script>

<!-- Modal: تعديل بيانات الكابتن -->
<div id="editCaptainModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 550px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
        <div style="background: var(--primary); color: white; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-user-pen"></i>
                <span>تعديل بيانات الكابتن: {{ $captain->name }}</span>
            </h3>
            <button type="button" onclick="closeEditCaptainModal()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.captains.update', $captain) }}" enctype="multipart/form-data" style="padding: 24px;">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">اسم الكابتن <span style="color: red;">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $captain->name) }}" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">رقم الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone', $captain->phone) }}" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">البريد الإلكتروني <span style="color: red;">*</span></label>
                <input type="email" name="email" value="{{ old('email', $captain->email) }}" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">تغيير كلمة المرور (اختياري)</label>
                <input type="password" name="password" placeholder="اتركها فارغة في حال عدم الرغبة في التغيير" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                <small style="color: var(--text-muted); font-size: 11px;">الحد الأدنى 6 خانات في حال التغيير</small>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">حالة الحساب <span style="color: red;">*</span></label>
                    <select name="status" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 700;">
                        <option value="active" {{ $captain->status === 'active' ? 'selected' : '' }}>نشط (مفعل)</option>
                        <option value="inactive" {{ $captain->status === 'inactive' ? 'selected' : '' }}>معطل (محظور مؤقتاً)</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">حالة التوفر والاتصال</label>
                    <select name="is_available" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 700;">
                        <option value="1" {{ $captain->is_available ? 'selected' : '' }}>متصل (جاهز لاستقبال الطلبات)</option>
                        <option value="0" {{ !$captain->is_available ? 'selected' : '' }}>غير متصل (غير متاح)</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">تحديث الصورة الشخصية (اختياري)</label>
                <input type="file" name="avatar" accept="image/*" style="width: 100%; font-size: 12px;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeEditCaptainModal()">إلغاء</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-check"></i>
                    <span>حفظ التعديلات</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
