@extends('admin.layout')

@section('title', 'التقارير المالية الشاملة - الإدارة المالية')
@section('page_title', 'التقارير المالية والمحاسبية')

@section('content')
<style>
    @media print {
        body { background: white !important; }
        .sidebar, .top-bar, .no-print, .btn, .filter-card { display: none !important; }
        .main-content { margin-right: 0 !important; padding: 0 !important; width: 100% !important; }
        .print-only { display: block !important; }
        .panel { box-shadow: none !important; border: 1px solid #ccc !important; }
    }
    .print-only { display: none; }
</style>

<div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: var(--text-main);">التقارير المالية والمحاسبية الشاملة</h2>
        <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 13px;">تحليل الأرباح التشغيلية، أرصدة المطاعم والكباتن، ومصاريف الرواتب</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button type="button" onclick="window.print()" class="btn btn-primary">
            <i class="fa-solid fa-print"></i>
            <span>طباعة التقرير المالي</span>
        </button>
    </div>
</div>

<!-- ترويسة خاصة بالطباعة الرسمية -->
<div class="print-only" style="margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 15px;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="margin:0; font-size: 24px; font-weight: 800; color: #068494;">منصة تسويق - التقرير المالي الشامل</h1>
            <p style="margin:4px 0; font-size: 13px; color: #555;">الفترة من: <strong>{{ $reportData['start'] }}</strong> إلى <strong>{{ $reportData['end'] }}</strong> &bull; تاريخ الاستخراج: {{ date('Y/m/d H:i') }}</p>
        </div>
        <div style="text-align: left; font-size: 12px; color: #555;">
            <div>إجمالي المبيعات: <strong>{{ number_format($reportData['total_sales'], 2) }} ر.ي</strong></div>
            <div>عمولة التطبيق: <strong>{{ number_format($reportData['app_commission'], 2) }} ر.ي</strong></div>
        </div>
    </div>
</div>

<!-- شريط فلترة الفترة الزمنية -->
<div class="filter-card no-print">
    <div class="filter-tabs">
        <a href="{{ route('admin.finance.reports', ['period' => 'today']) }}" class="filter-pill {{ $reportData['period'] === 'today' ? 'active' : '' }}">
            <span>اليوم</span>
        </a>
        <a href="{{ route('admin.finance.reports', ['period' => 'this_week']) }}" class="filter-pill {{ $reportData['period'] === 'this_week' ? 'active' : '' }}">
            <span>هذا الأسبوع</span>
        </a>
        <a href="{{ route('admin.finance.reports', ['period' => 'this_month']) }}" class="filter-pill {{ $reportData['period'] === 'this_month' ? 'active' : '' }}">
            <span>هذا الشهر</span>
        </a>
        <a href="{{ route('admin.finance.reports', ['period' => 'last_month']) }}" class="filter-pill {{ $reportData['period'] === 'last_month' ? 'active' : '' }}">
            <span>الشهر السابق</span>
        </a>
        <a href="{{ route('admin.finance.reports', ['period' => 'this_year']) }}" class="filter-pill {{ $reportData['period'] === 'this_year' ? 'active' : '' }}">
            <span>هذا العام</span>
        </a>
    </div>

    <form method="GET" action="{{ route('admin.finance.reports') }}" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
        <input type="hidden" name="period" value="custom">
        <div style="min-width: 150px;">
            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">من تاريخ</label>
            <input type="date" name="date_from" value="{{ $reportData['start'] }}" style="width: 100%; padding: 8px 12px; border-radius: 10px; border: 1px solid var(--border);">
        </div>
        <div style="min-width: 150px;">
            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">إلى تاريخ</label>
            <input type="date" name="date_to" value="{{ $reportData['end'] }}" style="width: 100%; padding: 8px 12px; border-radius: 10px; border: 1px solid var(--border);">
        </div>
        <button type="submit" class="btn btn-outline" style="padding: 9px 18px;">
            <i class="fa-solid fa-calendar-days"></i>
            <span>تطبيق الفترة المخصصة</span>
        </button>
    </form>
</div>

<!-- بطاقات الملخص المالي للفترة المحددة -->
<div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
    <div class="stat-card">
        <div class="info">
            <span class="title">مبيعات الطلبات المكتملة</span>
            <span class="value" style="color: var(--primary);">{{ number_format($reportData['total_sales'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">{{ $reportData['orders_count'] }} طلب مكتمل</small>
        </div>
        <div class="icon-box"><i class="fa-solid fa-bag-shopping"></i></div>
    </div>

    <div class="stat-card">
        <div class="info">
            <span class="title">عمولات التطبيق (إيراد المنصة)</span>
            <span class="value" style="color: #27AE60;">{{ number_format($reportData['app_commission'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
            <small style="color: #27AE60; font-size: 11px; margin-top: 4px;">نسبة تشغيلية</small>
        </div>
        <div class="icon-box" style="background: rgba(39, 174, 96, 0.12); color: #27AE60;"><i class="fa-solid fa-percent"></i></div>
    </div>

    <div class="stat-card">
        <div class="info">
            <span class="title">عمولات الكباتن</span>
            <span class="value" style="color: #E67E22;">{{ number_format($reportData['captain_commissions'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">مستحقات التوصيل</small>
        </div>
        <div class="icon-box" style="background: rgba(230, 126, 34, 0.12); color: #E67E22;"><i class="fa-solid fa-motorcycle"></i></div>
    </div>

    <div class="stat-card">
        <div class="info">
            <span class="title">الرواتب والمصروفات خلال الفترة</span>
            <span class="value" style="color: #8E44AD;">{{ number_format($reportData['salaries_paid'] + $reportData['other_expenses'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">رواتب: {{ number_format($reportData['salaries_paid'], 2) }} | مصاريف: {{ number_format($reportData['other_expenses'], 2) }}</small>
        </div>
        <div class="icon-box" style="background: rgba(142, 68, 173, 0.12); color: #8E44AD;"><i class="fa-solid fa-user-tie"></i></div>
    </div>

    <div class="stat-card" style="border: 2px solid {{ $reportData['net_profit'] >= 0 ? '#27AE60' : '#E74C3C' }};">
        <div class="info">
            <span class="title">صافي الدخل التشغيلي التقديري</span>
            <span class="value" style="color: {{ $reportData['net_profit'] >= 0 ? '#27AE60' : '#E74C3C' }};">
                {{ number_format($reportData['net_profit'], 2) }} <small style="font-size: 13px;">ر.ي</small>
            </span>
            <small style="font-weight: 700; color: {{ $reportData['net_profit'] >= 0 ? '#27AE60' : '#E74C3C' }}; margin-top: 4px;">
                عمولة المنصة - المصروفات
            </small>
        </div>
        <div class="icon-box" style="background: rgba(6, 132, 148, 0.12); color: var(--primary);"><i class="fa-solid fa-wallet"></i></div>
    </div>
</div>

<!-- تقرير أرصدة المطاعم والذمم -->
<div style="background: white; border-radius: 18px; border: 1px solid var(--border); padding: 22px; margin-bottom: 25px; box-shadow: 0 4px 14px rgba(0,0,0,0.02);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 style="margin: 0; font-size: 16px; font-weight: 800;">تقرير أرصدة ومبيعات المطاعم النشطة</h3>
        <span style="font-size: 12px; color: var(--text-muted);">{{ count($reportData['store_reports']) }} مطعم لديه حركة مالية</span>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted);">
                    <th style="padding: 10px 14px;">المتجر / المطعم</th>
                    <th style="padding: 10px 14px; text-align: center;">الطلبات</th>
                    <th style="padding: 10px 14px;">إجمالي المبيعات</th>
                    <th style="padding: 10px 14px;">عمولة التطبيق</th>
                    <th style="padding: 10px 14px;">إجمالي الدائن (له)</th>
                    <th style="padding: 10px 14px;">إجمالي المدين (المسدد له)</th>
                    <th style="padding: 10px 14px;">صافي الرصيد</th>
                    <th style="padding: 10px 14px; text-align: center;">الحالة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['store_reports'] as $sr)
                    @php $s = $sr['store']; $sum = $sr['summary']; @endphp
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px 14px; font-weight: 800; color: var(--text-main);">
                            {{ $s->name }}
                        </td>
                        <td style="padding: 12px 14px; text-align: center; font-weight: 700;">{{ $sum['orders_count'] }}</td>
                        <td style="padding: 12px 14px; font-weight: 700;">{{ number_format($sum['total_sales'], 2) }} ر.ي</td>
                        <td style="padding: 12px 14px; color: #27AE60; font-weight: 700;">{{ number_format($sum['app_commission'], 2) }} ر.ي</td>
                        <td style="padding: 12px 14px; color: #2980B9; font-weight: 700;">{{ number_format($sum['total_credit'], 2) }} ر.ي</td>
                        <td style="padding: 12px 14px; color: #E67E22; font-weight: 700;">{{ number_format($sum['total_debit'], 2) }} ر.ي</td>
                        <td style="padding: 12px 14px; font-weight: 800; font-size: 14px; color: {{ $sum['balance'] > 0 ? '#27AE60' : ($sum['balance'] < 0 ? '#E74C3C' : '#6C7278') }};">
                            {{ number_format(abs($sum['balance']), 2) }} ر.ي
                        </td>
                        <td style="padding: 12px 14px; text-align: center;">
                            @if($sum['balance'] > 0)
                                <span style="background: rgba(39, 174, 96, 0.1); color: #27AE60; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">دائن (له مستحقات)</span>
                            @elseif($sum['balance'] < 0)
                                <span style="background: rgba(231, 76, 60, 0.1); color: #E74C3C; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">مدين (عليه)</span>
                            @else
                                <span style="background: #F4F7F8; color: #6C7278; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">متزن</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding: 25px; text-align: center; color: var(--text-muted);">لا توجد حركات مسجلة للمطاعم.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- تقرير أرصدة ومديونيات الكباتن -->
<div style="background: white; border-radius: 18px; border: 1px solid var(--border); padding: 22px; margin-bottom: 25px; box-shadow: 0 4px 14px rgba(0,0,0,0.02);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 style="margin: 0; font-size: 16px; font-weight: 800;">تقرير أرصدة ومديونيات الكباتن</h3>
        <span style="font-size: 12px; color: var(--text-muted);">{{ count($reportData['captain_reports']) }} كابتن لديه حركة نقدية</span>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted);">
                    <th style="padding: 10px 14px;">الكابتن</th>
                    <th style="padding: 10px 14px; text-align: center;">الطلبات المنجزة</th>
                    <th style="padding: 10px 14px;">الكاش المحصل (مدين عليه)</th>
                    <th style="padding: 10px 14px;">العمولات المكتسبة (دائن له)</th>
                    <th style="padding: 10px 14px;">المبالغ المسددة (دائن)</th>
                    <th style="padding: 10px 14px;">صافي المديونية</th>
                    <th style="padding: 10px 14px; text-align: center;">الحالة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['captain_reports'] as $cr)
                    @php $c = $cr['captain']; $sum = $cr['summary']; @endphp
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px 14px; font-weight: 800; color: var(--text-main);">{{ $c->name }}</td>
                        <td style="padding: 12px 14px; text-align: center; font-weight: 700;">{{ $sum['completed_orders_count'] }}</td>
                        <td style="padding: 12px 14px; font-weight: 700; color: #E67E22;">{{ number_format($sum['cash_collected'], 2) }} ر.ي</td>
                        <td style="padding: 12px 14px; color: #27AE60; font-weight: 700;">+{{ number_format($sum['earned_commission'], 2) }} ر.ي</td>
                        <td style="padding: 12px 14px; color: #2980B9; font-weight: 700;">{{ number_format($sum['paid_debt'], 2) }} ر.ي</td>
                        <td style="padding: 12px 14px; font-weight: 800; font-size: 14px; color: {{ $sum['outstanding_debt'] > 0 ? '#E74C3C' : '#27AE60' }};">
                            {{ number_format($sum['outstanding_debt'], 2) }} ر.ي
                        </td>
                        <td style="padding: 12px 14px; text-align: center;">
                            @if($sum['outstanding_debt'] > 0)
                                <span style="background: rgba(231, 76, 60, 0.1); color: #E74C3C; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">مدين (عليه)</span>
                            @else
                                <span style="background: rgba(39, 174, 96, 0.1); color: #27AE60; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700;">خالي الذمة</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 25px; text-align: center; color: var(--text-muted);">لا توجد حركات نقدية للكباتن.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
