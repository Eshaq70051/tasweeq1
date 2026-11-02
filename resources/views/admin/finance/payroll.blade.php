@extends('admin.layout')

@section('title', 'مسير رواتب شهر ' . $month . ' - الإدارة المالية')
@section('page_title', 'مسير الرواتب الشهرية')

@section('content')
<style>
    @media print {
        body { background: white !important; }
        .sidebar, .top-bar, .no-print, .btn, .filter-card { display: none !important; }
        .main-content { margin-right: 0 !important; padding: 0 !important; width: 100% !important; }
        .print-only { display: block !important; }
    }
    .print-only { display: none; }
</style>

<div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: var(--text-main);">مسير رواتب الموظفين لشهر: {{ $month }}</h2>
        <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 13px;">توليد واعتماد الرواتب الشهرية، احتساب البدلات والخصومات، وتوثيق الصرف</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button type="button" onclick="window.print()" class="btn btn-outline">
            <i class="fa-solid fa-print"></i>
            <span>طباعة كشف المسير</span>
        </button>
        <form method="POST" action="{{ route('admin.finance.payroll.generate') }}" style="display:inline;">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <button type="submit" class="btn btn-primary" onclick="return confirm('هل ترغب في توليد مسير رواتب شهر ({{ $month }}) لجميع الموظفين على رأس العمل؟');">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>توليد مسير رواتب الشهر</span>
            </button>
        </form>
    </div>
</div>

<!-- ترويسة الطباعة الرسمية -->
<div class="print-only" style="margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 15px;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="margin:0; font-size: 24px; font-weight: 800; color: #068494;">منصة تسويق - مسير رواتب الموظفين</h1>
            <p style="margin:4px 0; font-size: 13px; color: #555;">عن شهر: <strong>{{ $month }}</strong> &bull; تاريخ الاستخراج: {{ date('Y/m/d H:i') }}</p>
        </div>
        <div style="text-align: left; font-size: 13px;">
            <div>إجمالي الرواتب: <strong>{{ number_format($stats['total_due'], 2) }} ر.ي</strong></div>
            <div>المسدد: <strong>{{ number_format($stats['total_paid'], 2) }} ر.ي</strong></div>
            <div>المتبقي: <strong>{{ number_format($stats['remaining'], 2) }} ر.ي</strong></div>
        </div>
    </div>
</div>

<!-- بطاقات إحصائيات الشهر -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي استحقاقات الشهر</span>
            <span class="value" style="color: var(--primary);">{{ number_format($stats['total_due'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">{{ $stats['count'] }} موظف في المسير</small>
        </div>
        <div class="icon-box"><i class="fa-solid fa-file-invoice-dollar"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي المبالغ المصروفة</span>
            <span class="value" style="color: #27AE60;">{{ number_format($stats['total_paid'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
            <small style="color: #27AE60; font-size: 11px; margin-top: 4px;">{{ $stats['paid_count'] }} راتب مسدد بالكامل</small>
        </div>
        <div class="icon-box" style="background: rgba(39, 174, 96, 0.12); color: #27AE60;"><i class="fa-solid fa-circle-check"></i></div>
    </div>
    <div class="stat-card" style="border: 2px solid {{ $stats['remaining'] > 0 ? '#E74C3C' : '#27AE60' }};">
        <div class="info">
            <span class="title">المتبقي غير المصروف</span>
            <span class="value" style="color: {{ $stats['remaining'] > 0 ? '#E74C3C' : '#27AE60' }};">
                {{ number_format($stats['remaining'], 2) }} <small style="font-size: 13px;">ر.ي</small>
            </span>
            <small style="font-weight: 700; color: {{ $stats['remaining'] > 0 ? '#E74C3C' : '#27AE60' }}; margin-top: 4px;">
                {{ $stats['pending_count'] }} راتب معلق أو جزئي
            </small>
        </div>
        <div class="icon-box" style="background: rgba(231, 76, 60, 0.12); color: #E74C3C;"><i class="fa-solid fa-hourglass-half"></i></div>
    </div>
</div>

<!-- اختيار الشهر -->
<div class="filter-card no-print">
    <form method="GET" action="{{ route('admin.finance.payroll') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <label style="font-weight: 700; font-size: 13px; color: var(--text-main);">عرض مسير شهر:</label>
        <input type="month" name="month" value="{{ $month }}" style="padding: 9px 14px; border-radius: 12px; border: 1px solid var(--border); font-weight: 700;">
        <button type="submit" class="btn btn-primary" style="padding: 9px 18px;">
            <i class="fa-solid fa-calendar-check"></i>
            <span>عرض</span>
        </button>
        <a href="{{ route('admin.finance.payroll') }}" class="btn btn-outline" style="padding: 9px 14px;">الشهر الحالي</a>
    </form>
</div>

<!-- جدول مسير الرواتب -->
<div style="background: white; border-radius: 18px; border: 1px solid var(--border); padding: 22px; box-shadow: 0 4px 14px rgba(0,0,0,0.02);">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted);">
                    <th style="padding: 12px 14px;">الموظف</th>
                    <th style="padding: 12px 14px;">الوظيفة</th>
                    <th style="padding: 12px 14px;">الأساسي</th>
                    <th style="padding: 12px 14px;">البدلات</th>
                    <th style="padding: 12px 14px;">المكافآت</th>
                    <th style="padding: 12px 14px;">الخصومات</th>
                    <th style="padding: 12px 14px;">صافي المستحق</th>
                    <th style="padding: 12px 14px;">المدفوع</th>
                    <th style="padding: 12px 14px;">المتبقي</th>
                    <th style="padding: 12px 14px; text-align: center;">الحالة</th>
                    <th style="padding: 12px 14px; text-align: center;" class="no-print">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salaries as $sal)
                    <tr style="border-bottom: 1px solid var(--border); transition: background 0.15s ease;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">
                        <td style="padding: 14px; font-weight: 800; color: var(--text-main);">
                            {{ $sal->employee?->name }}
                        </td>
                        <td style="padding: 14px; color: var(--text-muted);">{{ $sal->employee?->job_title }}</td>
                        <td style="padding: 14px;">{{ number_format($sal->basic_salary, 2) }}</td>
                        <td style="padding: 14px; color: #27AE60;">+{{ number_format($sal->allowances, 2) }}</td>
                        <td style="padding: 14px; color: #2980B9;">+{{ number_format($sal->bonus, 2) }}</td>
                        <td style="padding: 14px; color: #E74C3C;">-{{ number_format($sal->deductions, 2) }}</td>
                        <td style="padding: 14px; font-weight: 800; font-size: 14px; color: var(--primary);">
                            {{ number_format($sal->net_salary, 2) }} ر.ي
                        </td>
                        <td style="padding: 14px; color: #27AE60; font-weight: 700;">
                            {{ number_format($sal->paid_amount, 2) }} ر.ي
                        </td>
                        <td style="padding: 14px; color: {{ $sal->remaining_amount > 0 ? '#E74C3C' : '#6C7278' }}; font-weight: 700;">
                            {{ number_format($sal->remaining_amount, 2) }} ر.ي
                        </td>
                        <td style="padding: 14px; text-align: center;">
                            @if($sal->status === 'paid')
                                <span style="background: rgba(39, 174, 96, 0.1); color: #27AE60; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 800;">مسدد بالكامل</span>
                            @elseif($sal->status === 'partial')
                                <span style="background: rgba(230, 126, 34, 0.1); color: #E67E22; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 800;">مسدد جزئياً</span>
                            @else
                                <span style="background: rgba(231, 76, 60, 0.1); color: #E74C3C; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 800;">معلق</span>
                            @endif
                        </td>
                        <td style="padding: 14px; text-align: center;" class="no-print">
                            <button type="button" class="btn btn-primary" style="padding: 6px 12px; font-size: 11px;" onclick="openPaySalaryModal('{{ $sal->id }}', '{{ addslashes($sal->employee?->name) }}', '{{ $sal->remaining_amount }}', '{{ $sal->bonus }}', '{{ $sal->deductions }}')">
                                <i class="fa-solid fa-money-bill-wave"></i>
                                <span>صرف راتب</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="padding: 40px; text-align: center; color: var(--text-muted);">
                            <i class="fa-solid fa-money-check-dollar" style="font-size: 32px; color: #CBD5E1; margin-bottom: 10px; display: block;"></i>
                            لم يتم توليد مسير رواتب لشهر ({{ $month }}) حتى الآن. انقر على زر <strong>"توليد مسير رواتب الشهر"</strong> أعلاه.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: صرف راتب موظف -->
<div id="paySalaryModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 500px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
        <div style="background: var(--primary); color: white; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800;">تسجيل صرف راتب</h3>
            <button type="button" onclick="closePaySalaryModal()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form id="paySalaryForm" method="POST" action="" style="padding: 24px;">
            @csrf
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">الموظف</label>
                <input type="text" id="modal_pay_emp_name" readonly class="form-control" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); background: #F8FAFC; font-weight: 700;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">مكافأة إضافية (ر.ي)</label>
                    <input type="number" step="0.01" name="bonus" id="modal_pay_bonus" value="0" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">خصومات / غياب (ر.ي)</label>
                    <input type="number" step="0.01" name="deductions" id="modal_pay_deductions" value="0" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">المبلغ المصروف الآن (ر.ي) <span style="color:red">*</span></label>
                    <input type="number" step="0.01" name="paid_amount" id="modal_pay_amount" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 800; font-size: 15px;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">تاريخ الصرف <span style="color:red">*</span></label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">طريقة الصرف <span style="color:red">*</span></label>
                <select name="payment_method" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                    <option value="cash">نقدي (كاش)</option>
                    <option value="bank_transfer">تحويل بنكي / صرافة</option>
                    <option value="check">شيك</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">ملاحظات الصرف</label>
                <textarea name="notes" rows="2" placeholder="أية تفاصيل إضافية..." style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closePaySalaryModal()">إلغاء</button>
                <button type="submit" class="btn btn-primary">تأكيد الصرف وتوثيق السند</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPaySalaryModal(id, empName, rem, bonus, ded) {
        document.getElementById('paySalaryForm').action = "/admin/finance/payroll/" + id + "/pay";
        document.getElementById('modal_pay_emp_name').value = empName;
        document.getElementById('modal_pay_amount').value = rem;
        document.getElementById('modal_pay_bonus').value = bonus || 0;
        document.getElementById('modal_pay_deductions').value = ded || 0;
        document.getElementById('paySalaryModal').style.display = 'flex';
    }

    function closePaySalaryModal() {
        document.getElementById('paySalaryModal').style.display = 'none';
    }
</script>
@endsection
