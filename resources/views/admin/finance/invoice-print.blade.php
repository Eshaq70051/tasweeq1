<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة تسوية مبيعات - {{ $invoice->invoice_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #f8fafc; margin: 0; padding: 30px; color: #1e293b; }
        .invoice-box { max-width: 800px; margin: auto; padding: 35px; background: white; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.03); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #068494; padding-bottom: 20px; margin-bottom: 25px; }
        .title { font-size: 24px; font-weight: 800; color: #068494; margin: 0; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        .table th, .table td { padding: 12px 16px; border: 1px solid #e2e8f0; text-align: right; }
        .table th { background: #f1f5f9; font-weight: 700; }
        .btn-print { background: #068494; color: white; padding: 10px 20px; border-radius: 10px; border: none; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-family: inherit; margin-bottom: 20px; }
        @media print {
            body { padding: 0; background: white; }
            .invoice-box { border: none; box-shadow: none; padding: 10px; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div style="max-width: 800px; margin: auto;" class="no-print">
        <button onclick="window.print()" class="btn-print">
            <i class="fa-solid fa-print"></i>
            <span>طباعة الفاتورة</span>
        </button>
    </div>

    <div class="invoice-box">
        <div class="header">
            <div>
                <h1 class="title">منصة تسويق</h1>
                <div style="font-size: 13px; color: #64748b; margin-top: 4px;">إدارة الحسابات المالية والتسويات</div>
                <div style="font-size: 12px; color: #64748b; margin-top: 2px;">الهاتف: 777000000 | اليمن</div>
            </div>
            <div style="text-align: left;">
                <div style="font-size: 18px; font-weight: 800; color: #0f172a;">فاتورة تسوية مبيعات</div>
                <div style="font-size: 14px; font-weight: 700; color: #068494; font-family: monospace; margin-top: 4px;">{{ $invoice->invoice_number }}</div>
                <div style="font-size: 12px; color: #64748b; margin-top: 4px;">تاريخ الإصدار: {{ $invoice->created_at->format('Y/m/d') }}</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; background: #f8fafc; padding: 18px; border-radius: 12px; font-size: 13px;">
            <div>
                <div style="color: #64748b; margin-bottom: 4px;">فاتورة إلى المتجر / المطعم:</div>
                <div style="font-size: 16px; font-weight: 800; color: #0f172a;">{{ $invoice->store?->name }}</div>
                <div style="margin-top: 4px;">هاتف: {{ $invoice->store?->phone ?: 'غير محدد' }}</div>
                <div>العنوان: {{ $invoice->store?->address ?: 'غير محدد' }}</div>
            </div>
            <div>
                <div style="color: #64748b; margin-bottom: 4px;">تفاصيل فترة التسوية:</div>
                <div>من تاريخ: <strong>{{ $invoice->from_date ? $invoice->from_date->format('Y/m/d') : '-' }}</strong></div>
                <div>إلى تاريخ: <strong>{{ $invoice->to_date ? $invoice->to_date->format('Y/m/d') : '-' }}</strong></div>
                <div>الحساب البنكي: <strong>{{ $invoice->store?->bank_name ?: 'غير مسجل' }} ({{ $invoice->store?->account_number ?: '-' }})</strong></div>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>البند / الوصف</th>
                    <th style="text-align: center;">العدد</th>
                    <th style="text-align: center;">النسبة</th>
                    <th>المبلغ (ر.ي)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>إجمالي مبيعات الطلبات المسلمة خلال الفترة</strong></td>
                    <td style="text-align: center; font-weight: 700;">{{ $invoice->total_orders_count }} طلب</td>
                    <td style="text-align: center;">-</td>
                    <td style="font-weight: 700;">{{ number_format($invoice->total_orders_amount, 2) }} ر.ي</td>
                </tr>
                <tr>
                    <td><strong>عمولة المنصة والتطبيق المتفق عليها</strong></td>
                    <td style="text-align: center;">-</td>
                    <td style="text-align: center; font-weight: 700; color: #068494;">{{ $invoice->commission_rate }}%</td>
                    <td style="color: #e11d48; font-weight: 700;">- {{ number_format($invoice->commission_amount, 2) }} ر.ي</td>
                </tr>
                <tr style="background: #f8fafc; font-size: 15px;">
                    <td colspan="3"><strong>صافي المستحق النهائي للمتجر</strong></td>
                    <td style="color: #068494; font-weight: 800; font-size: 16px;">{{ number_format($invoice->net_amount, 2) }} ر.ي</td>
                </tr>
                <tr>
                    <td colspan="3">المبلغ المسدد حتى الآن</td>
                    <td style="color: #16a34a; font-weight: 700;">{{ number_format($invoice->paid_amount, 2) }} ر.ي</td>
                </tr>
                <tr style="background: #fff1f2;">
                    <td colspan="3"><strong style="color: #be123c;">المبلغ المتبقي بذمة المنصة</strong></td>
                    <td style="color: #be123c; font-weight: 800;">{{ number_format($invoice->remaining_amount, 2) }} ر.ي</td>
                </tr>
            </tbody>
        </table>

        @if($invoice->notes)
            <div style="margin-top: 20px; padding: 12px; background: #f8fafc; border-radius: 8px; font-size: 12px; color: #475569;">
                <strong>ملاحظات:</strong> {{ $invoice->notes }}
            </div>
        @endif

        <div style="display: flex; justify-content: space-between; margin-top: 50px; padding-top: 25px; border-top: 1px dashed #cbd5e1; font-size: 13px;">
            <div style="text-align: center; width: 200px;">
                <div style="font-weight: 700;">المحاسب المسؤول</div>
                <div style="margin-top: 40px; border-bottom: 1px solid #94a3b8;"></div>
            </div>
            <div style="text-align: center; width: 200px;">
                <div style="font-weight: 700;">توقيع واستلام المتجر</div>
                <div style="margin-top: 40px; border-bottom: 1px solid #94a3b8;"></div>
            </div>
        </div>
    </div>
</body>
</html>
