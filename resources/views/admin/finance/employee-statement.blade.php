@extends('admin.layout')

@section('title', 'كشف حساب الموظف ' . $employee->name . ' - الإدارة المالية')
@section('page_title', 'كشف حساب موظف تفصيلي')

@section('content')
<style>
    @media print {
        body { background: white !important; }
        .sidebar, .top-bar, .no-print, .btn { display: none !important; }
        .main-content { margin-right: 0 !important; padding: 0 !important; width: 100% !important; }
        .print-only { display: block !important; }
    }
    .print-only { display: none; }
</style>

<div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <div style="display: flex; align-items: center; gap: 14px;">
        <a href="{{ route('admin.finance.employees') }}" class="btn btn-outline" style="padding: 8px 14px;">
            <i class="fa-solid fa-arrow-right"></i>
            <span>رجوع للموظفين</span>
        </a>
        <div>
            <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: var(--text-main);">كشف حساب الموظف: {{ $employee->name }}</h2>
            <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 13px;">المسمى: {{ $employee->job_title }} &bull; الهاتف: {{ $employee->phone ?: 'غير مسجل' }}</p>
        </div>
    </div>
    <div style="display: flex; gap: 10px;">
        <button type="button" onclick="window.print()" class="btn btn-outline">
            <i class="fa-solid fa-print"></i>
            <span>طباعة كشف الحساب</span>
        </button>
    </div>
</div>

<!-- ترويسة الطباعة -->
<div class="print-only" style="margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 15px;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="margin:0; font-size: 24px; font-weight: 800; color: #068494;">منصة تسويق - كشف حساب موظف</h1>
            <p style="margin:4px 0; font-size: 13px; color: #555;">الموظف: <strong>{{ $employee->name }}</strong> ({{ $employee->job_title }}) &bull; تاريخ الطباعة: {{ date('Y/m/d H:i') }}</p>
        </div>
        <div style="text-align: left; font-size: 13px;">
            <div>الراتب الأساسي: <strong>{{ number_format($employee->basic_salary, 2) }} ر.ي</strong></div>
            <div>البدلات: <strong>{{ number_format($employee->allowances, 2) }} ر.ي</strong></div>
        </div>
    </div>
</div>

<!-- بطاقات الموقف المالي للموظف -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي الاستحقاقات (الرواتب المعتمدة)</span>
            <span class="value" style="color: var(--primary);">{{ number_format($stats['total_earned'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
        </div>
        <div class="icon-box"><i class="fa-solid fa-receipt"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي المبالغ المصروفة</span>
            <span class="value" style="color: #27AE60;">{{ number_format($stats['total_paid'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
        </div>
        <div class="icon-box" style="background: rgba(39, 174, 96, 0.12); color: #27AE60;"><i class="fa-solid fa-circle-check"></i></div>
    </div>
    <div class="stat-card" style="border: 2px solid {{ $stats['remaining'] > 0 ? '#E74C3C' : '#27AE60' }};">
        <div class="info">
            <span class="title">صافي المستحق المتبقي له</span>
            <span class="value" style="color: {{ $stats['remaining'] > 0 ? '#E74C3C' : '#27AE60' }};">
                {{ number_format($stats['remaining'], 2) }} <small style="font-size: 13px;">ر.ي</small>
            </span>
        </div>
        <div class="icon-box" style="background: rgba(231, 76, 60, 0.12); color: #E74C3C;"><i class="fa-solid fa-scale-balanced"></i></div>
    </div>
</div>

<!-- سجل الرواتب الشهرية للموظف -->
<div style="background: white; border-radius: 18px; border: 1px solid var(--border); padding: 22px; margin-bottom: 25px; box-shadow: 0 4px 14px rgba(0,0,0,0.02);">
    <h3 style="margin: 0 0 16px; font-size: 16px; font-weight: 800;">سجل الرواتب الشهرية</h3>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted);">
                    <th style="padding: 10px 14px;">الشهر</th>
                    <th style="padding: 10px 14px;">الأساسي</th>
                    <th style="padding: 10px 14px;">البدلات</th>
                    <th style="padding: 10px 14px;">المكافآت</th>
                    <th style="padding: 10px 14px;">الخصومات</th>
                    <th style="padding: 10px 14px;">صافي الراتب</th>
                    <th style="padding: 10px 14px;">المبلغ المدفوع</th>
                    <th style="padding: 10px 14px;">المتبقي</th>
                    <th style="padding: 10px 14px; text-align: center;">الحالة</th>
                    <th style="padding: 10px 14px;">تاريخ الصرف</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salaries as $sal)
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px 14px; font-weight: 700; color: var(--primary);">{{ $sal->month }}</td>
                        <td style="padding: 12px 14px;">{{ number_format($sal->basic_salary, 2) }}</td>
                        <td style="padding: 12px 14px; color: #27AE60;">+{{ number_format($sal->allowances, 2) }}</td>
                        <td style="padding: 12px 14px; color: #2980B9;">+{{ number_format($sal->bonus, 2) }}</td>
                        <td style="padding: 12px 14px; color: #E74C3C;">-{{ number_format($sal->deductions, 2) }}</td>
                        <td style="padding: 12px 14px; font-weight: 800; font-size: 14px;">{{ number_format($sal->net_salary, 2) }} ر.ي</td>
                        <td style="padding: 12px 14px; color: #27AE60; font-weight: 700;">{{ number_format($sal->paid_amount, 2) }} ر.ي</td>
                        <td style="padding: 12px 14px; color: {{ $sal->remaining_amount > 0 ? '#E74C3C' : '#6C7278' }}; font-weight: 700;">
                            {{ number_format($sal->remaining_amount, 2) }} ر.ي
                        </td>
                        <td style="padding: 12px 14px; text-align: center;">
                            @if($sal->status === 'paid')
                                <span style="background: rgba(39, 174, 96, 0.1); color: #27AE60; padding: 3px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">مسدد بالكامل</span>
                            @elseif($sal->status === 'partial')
                                <span style="background: rgba(230, 126, 34, 0.1); color: #E67E22; padding: 3px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">مسدد جزئياً</span>
                            @else
                                <span style="background: rgba(231, 76, 60, 0.1); color: #E74C3C; padding: 3px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">معلق</span>
                            @endif
                        </td>
                        <td style="padding: 12px 14px; color: var(--text-muted);">{{ $sal->payment_date ? $sal->payment_date->format('Y/m/d') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="padding: 25px; text-align: center; color: var(--text-muted);">لا توجد رواتب مسجلة لهذا الموظف حتى الآن.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- الحركات المالية والسندات المسجلة للموظف -->
<div style="background: white; border-radius: 18px; border: 1px solid var(--border); padding: 22px; box-shadow: 0 4px 14px rgba(0,0,0,0.02);">
    <h3 style="margin: 0 0 16px; font-size: 16px; font-weight: 800;">سجل السندات والعمليات المالية للموظف</h3>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted);">
                    <th style="padding: 10px 14px;">رقم السند</th>
                    <th style="padding: 10px 14px;">التاريخ</th>
                    <th style="padding: 10px 14px;">النوع</th>
                    <th style="padding: 10px 14px;">البيان</th>
                    <th style="padding: 10px 14px;">المبلغ</th>
                    <th style="padding: 10px 14px;">طريقة الدفع</th>
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
                            @else
                                <span style="background: rgba(39, 174, 96, 0.1); color: #27AE60; padding: 3px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">سند قبض</span>
                            @endif
                        </td>
                        <td style="padding: 12px 14px;">{{ $txn->title }}</td>
                        <td style="padding: 12px 14px; font-weight: 800; color: var(--primary);">{{ number_format($txn->amount, 2) }} ر.ي</td>
                        <td style="padding: 12px 14px; color: var(--text-muted);">{{ $txn->payment_method }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 25px; text-align: center; color: var(--text-muted);">لا توجد حركات مسجلة.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
