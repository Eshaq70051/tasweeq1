@extends('admin.layout')

@section('title', 'الرئيسية المالية - لوحة التحكم')
@section('page_title', 'النظام المالي والمحاسبي')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: var(--text-main);">لوحة العمليات المالية والمحاسبة</h2>
        <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 13px;">نظرة شاملة على مبيعات المطاعم، ذمم الكباتن، رواتب الموظفين، وحركة الصندوق</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('admin.finance.transactions') }}#createModal" class="btn btn-primary" onclick="openNewTxnModal()">
            <i class="fa-solid fa-plus-circle"></i>
            <span>إضافة سند / عملية جديدة</span>
        </a>
        <a href="{{ route('admin.finance.reports') }}" class="btn btn-outline">
            <i class="fa-solid fa-file-invoice-dollar"></i>
            <span>التقارير المالية</span>
        </a>
    </div>
</div>

<!-- بطاقات الإحصائيات المالية الأساسية -->
<div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
    <!-- إجمالي المبيعات -->
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي مبيعات المتاجر</span>
            <span class="value" style="color: var(--primary);">{{ number_format($stats['total_sales'], 2) }} <small style="font-size: 14px; font-weight: 600;">ر.ي</small></span>
            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">هذا الشهر: {{ number_format($stats['this_month_sales'], 2) }} ر.ي</small>
        </div>
        <div class="icon-box" style="background: rgba(6, 132, 148, 0.12); color: var(--primary);">
            <i class="fa-solid fa-shop"></i>
        </div>
    </div>

    <!-- عمولة التطبيق -->
    <div class="stat-card">
        <div class="info">
            <span class="title">عمولات التطبيق المستحقة</span>
            <span class="value" style="color: #27AE60;">{{ number_format($stats['app_commission'], 2) }} <small style="font-size: 14px; font-weight: 600;">ر.ي</small></span>
            <small style="color: #27AE60; font-size: 11px; margin-top: 4px;"><i class="fa-solid fa-arrow-trend-up"></i> إيراد تشغيلي للمنصة</small>
        </div>
        <div class="icon-box" style="background: rgba(39, 174, 96, 0.12); color: #27AE60;">
            <i class="fa-solid fa-percent"></i>
        </div>
    </div>

    <!-- مديونيات الكباتن -->
    <div class="stat-card">
        <div class="info">
            <span class="title">مديونيات الكباتن (كاش محصل)</span>
            <span class="value" style="color: #E67E22;">{{ number_format($stats['captains_outstanding_debt'], 2) }} <small style="font-size: 14px; font-weight: 600;">ر.ي</small></span>
            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">إجمالي الكاش: {{ number_format($stats['captains_cash_collected'], 2) }} ر.ي</small>
        </div>
        <div class="icon-box" style="background: rgba(230, 126, 34, 0.12); color: #E67E22;">
            <i class="fa-solid fa-motorcycle"></i>
        </div>
    </div>

    <!-- مستحقات المطاعم -->
    <div class="stat-card">
        <div class="info">
            <span class="title">صافي مستحقات المطاعم (لهم)</span>
            <span class="value" style="color: #2980B9;">{{ number_format($stats['stores_net_due'], 2) }} <small style="font-size: 14px; font-weight: 600;">ر.ي</small></span>
            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">{{ $stats['total_stores'] }} مطعم ومتجر مسجل</small>
        </div>
        <div class="icon-box" style="background: rgba(41, 128, 185, 0.12); color: #2980B9;">
            <i class="fa-solid fa-utensils"></i>
        </div>
    </div>

    <!-- الرواتب ومسير الشهر -->
    <div class="stat-card">
        <div class="info">
            <span class="title">رواتب الموظفين (المصروفة هذا الشهر)</span>
            <span class="value" style="color: #8E44AD;">{{ number_format($stats['paid_salaries'], 2) }} <small style="font-size: 14px; font-weight: 600;">ر.ي</small></span>
            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">متبقي غير مصروف: {{ number_format($stats['pending_salaries'], 2) }} ر.ي</small>
        </div>
        <div class="icon-box" style="background: rgba(142, 68, 173, 0.12); color: #8E44AD;">
            <i class="fa-solid fa-user-tie"></i>
        </div>
    </div>

    <!-- حركة الصندوق الصافية -->
    <div class="stat-card">
        <div class="info">
            <span class="title">صافي حركة الصندوق (المقبوض - المصروف)</span>
            <span class="value" style="color: {{ $stats['net_cash_flow'] >= 0 ? '#27AE60' : '#E74C3C' }};">
                {{ number_format($stats['net_cash_flow'], 2) }} <small style="font-size: 14px; font-weight: 600;">ر.ي</small>
            </span>
            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">
                قبض: {{ number_format($stats['total_receipts'], 2) }} | صرف: {{ number_format($stats['total_payments'], 2) }}
            </small>
        </div>
        <div class="icon-box" style="background: rgba(39, 174, 96, 0.12); color: #27AE60;">
            <i class="fa-solid fa-cash-register"></i>
        </div>
    </div>
</div>

<!-- بطاقات الوصول السريع للأقسام المالية -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 30px;">
    <a href="{{ route('admin.finance.stores') }}" style="background: white; border-radius: 16px; border: 1px solid var(--border); padding: 18px; text-decoration: none; color: var(--text-main); display: flex; align-items: center; gap: 14px; transition: all 0.25s ease;" onmouseover="this.style.borderColor='var(--primary)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='var(--border)'; this.style.transform='translateY(0)'">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(6, 132, 148, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-utensils"></i>
        </div>
        <div>
            <div style="font-weight: 800; font-size: 14px;">حسابات المطاعم</div>
            <small style="color: var(--text-muted); font-size: 12px;">كشوف الحسابات والمدين والدائن</small>
        </div>
    </a>

    <a href="{{ route('admin.finance.invoices') }}" style="background: white; border-radius: 16px; border: 1px solid var(--border); padding: 18px; text-decoration: none; color: var(--text-main); display: flex; align-items: center; gap: 14px; transition: all 0.25s ease;" onmouseover="this.style.borderColor='var(--primary)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='var(--border)'; this.style.transform='translateY(0)'">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(41, 128, 185, 0.1); color: #2980B9; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-file-invoice"></i>
        </div>
        <div>
            <div style="font-weight: 800; font-size: 14px;">فواتير المطاعم</div>
            <small style="color: var(--text-muted); font-size: 12px;">تسويات المبيعات وإصدار الفواتير</small>
        </div>
    </a>

    <a href="{{ route('admin.finance.captains') }}" style="background: white; border-radius: 16px; border: 1px solid var(--border); padding: 18px; text-decoration: none; color: var(--text-main); display: flex; align-items: center; gap: 14px; transition: all 0.25s ease;" onmouseover="this.style.borderColor='var(--primary)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='var(--border)'; this.style.transform='translateY(0)'">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(230, 126, 34, 0.1); color: #E67E22; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-motorcycle"></i>
        </div>
        <div>
            <div style="font-weight: 800; font-size: 14px;">حسابات الكباتن</div>
            <small style="color: var(--text-muted); font-size: 12px;">الكاش المحصل وسداد الذمم</small>
        </div>
    </a>

    <a href="{{ route('admin.finance.payroll') }}" style="background: white; border-radius: 16px; border: 1px solid var(--border); padding: 18px; text-decoration: none; color: var(--text-main); display: flex; align-items: center; gap: 14px; transition: all 0.25s ease;" onmouseover="this.style.borderColor='var(--primary)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='var(--border)'; this.style.transform='translateY(0)'">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(142, 68, 173, 0.1); color: #8E44AD; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-money-check-dollar"></i>
        </div>
        <div>
            <div style="font-weight: 800; font-size: 14px;">مسير الرواتب</div>
            <small style="color: var(--text-muted); font-size: 12px;">صرف الرواتب الشهرية والبدلات</small>
        </div>
    </a>

    <a href="{{ route('admin.finance.transactions') }}" style="background: white; border-radius: 16px; border: 1px solid var(--border); padding: 18px; text-decoration: none; color: var(--text-main); display: flex; align-items: center; gap: 14px; transition: all 0.25s ease;" onmouseover="this.style.borderColor='var(--primary)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='var(--border)'; this.style.transform='translateY(0)'">
        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(39, 174, 96, 0.1); color: #27AE60; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-receipt"></i>
        </div>
        <div>
            <div style="font-weight: 800; font-size: 14px;">العمليات والسندات</div>
            <small style="color: var(--text-muted); font-size: 12px;">سندات القبض والصرف والقيود</small>
        </div>
    </a>
</div>

<!-- جدول أحدث العمليات المحاسبية -->
<div style="background: white; border-radius: 18px; border: 1px solid var(--border); padding: 22px; margin-bottom: 25px; box-shadow: 0 4px 14px rgba(0,0,0,0.02);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <h3 style="margin: 0; font-size: 16px; font-weight: 800;">آخر العمليات والسندات المسجلة</h3>
        </div>
        <a href="{{ route('admin.finance.transactions') }}" class="btn btn-outline" style="font-size: 12px; padding: 6px 14px;">
            <span>عرض كافة العمليات</span>
            <i class="fa-solid fa-arrow-left"></i>
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted);">
                    <th style="padding: 12px 14px;">رقم السند</th>
                    <th style="padding: 12px 14px;">التاريخ</th>
                    <th style="padding: 12px 14px;">النوع</th>
                    <th style="padding: 12px 14px;">الطرف المستفيد</th>
                    <th style="padding: 12px 14px;">البيان</th>
                    <th style="padding: 12px 14px;">المبلغ</th>
                    <th style="padding: 12px 14px;">طريقة الدفع</th>
                    <th style="padding: 12px 14px; text-align: center;">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestTransactions as $txn)
                    <tr style="border-bottom: 1px solid var(--border); transition: background 0.15s ease;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">
                        <td style="padding: 14px; font-weight: 700; color: var(--primary); font-family: monospace; font-size: 13px;">
                            {{ $txn->transaction_number }}
                        </td>
                        <td style="padding: 14px; color: var(--text-muted);">
                            {{ $txn->transaction_date ? \Carbon\Carbon::parse($txn->transaction_date)->format('Y/m/d') : '-' }}
                        </td>
                        <td style="padding: 14px;">
                            @if($txn->type === 'receipt')
                                <span style="background: rgba(39, 174, 96, 0.1); color: #27AE60; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 700;">
                                    <i class="fa-solid fa-arrow-down-left"></i> سند قبض
                                </span>
                            @elseif($txn->type === 'payment')
                                <span style="background: rgba(231, 76, 60, 0.1); color: #E74C3C; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 700;">
                                    <i class="fa-solid fa-arrow-up-right"></i> سند صرف
                                </span>
                            @else
                                <span style="background: rgba(41, 128, 185, 0.1); color: #2980B9; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 700;">
                                    <i class="fa-solid fa-scale-balanced"></i> قيد تسوية
                                </span>
                            @endif
                        </td>
                        <td style="padding: 14px; font-weight: 700;">
                            <span style="font-size: 11px; color: var(--text-muted); display: block;">
                                @if($txn->party_type === 'store') متجر / مطعم
                                @elseif($txn->party_type === 'captain') كابتن توصيل
                                @elseif($txn->party_type === 'employee') موظف
                                @else حساب عام @endif
                            </span>
                            {{ $txn->resolved_party_name }}
                        </td>
                        <td style="padding: 14px; max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $txn->title }}">
                            {{ $txn->title }}
                        </td>
                        <td style="padding: 14px; font-weight: 800; font-size: 14px; color: {{ $txn->direction === 'debit' ? '#E74C3C' : '#27AE60' }};">
                            {{ $txn->direction === 'debit' ? '-' : '+' }}{{ number_format($txn->amount, 2) }} ر.ي
                        </td>
                        <td style="padding: 14px; color: var(--text-muted);">
                            <span style="background: #F4F7F8; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 600;">
                                {{ $txn->payment_method === 'cash' ? 'نقدي (كاش)' : ($txn->payment_method === 'bank_transfer' ? 'تحويل بنكي' : $txn->payment_method) }}
                            </span>
                        </td>
                        <td style="padding: 14px; text-align: center;">
                            <a href="{{ route('admin.finance.transactions.print', $txn) }}" target="_blank" class="btn btn-outline" style="padding: 5px 10px; font-size: 11px;" title="طباعة السند">
                                <i class="fa-solid fa-print"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding: 35px; text-align: center; color: var(--text-muted);">
                            <i class="fa-solid fa-receipt" style="font-size: 32px; color: #CBD5E1; margin-bottom: 10px; display: block;"></i>
                            لم يتم تسجيل أي عمليات محاسبية حتى الآن. يمكنك إضافة سند قبض أو صرف بالنقر على الزر أعلاه.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
