<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $transaction->type_name }} - {{ $transaction->transaction_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #f8fafc; margin: 0; padding: 30px; color: #1e293b; }
        .voucher-box { max-width: 780px; margin: auto; padding: 35px; background: white; border: 2px solid #068494; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.03); position: relative; }
        .voucher-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 18px; margin-bottom: 25px; }
        .btn-print { background: #068494; color: white; padding: 10px 20px; border-radius: 10px; border: none; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-family: inherit; margin-bottom: 20px; }
        .row-item { display: flex; align-items: baseline; margin-bottom: 16px; font-size: 15px; }
        .row-label { width: 140px; font-weight: 700; color: #475569; flex-shrink: 0; }
        .row-value { flex: 1; border-bottom: 1px dashed #cbd5e1; padding-bottom: 4px; font-weight: 700; color: #0f172a; }
        .amount-box { border: 2px solid #068494; background: rgba(6, 132, 148, 0.05); padding: 8px 18px; border-radius: 10px; font-size: 18px; font-weight: 800; color: #068494; display: inline-block; }
        @media print {
            body { padding: 0; background: white; }
            .voucher-box { border: 1.5px solid #000; box-shadow: none; padding: 20px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div style="max-width: 780px; margin: auto;" class="no-print">
        <button onclick="window.print()" class="btn-print">
            <i class="fa-solid fa-print"></i>
            <span>طباعة السند</span>
        </button>
    </div>

    <div class="voucher-box">
        <div class="voucher-header">
            <div>
                <h1 style="margin: 0; font-size: 22px; font-weight: 800; color: #068494;">منصة تسويق للخدمات والتوصيل</h1>
                <div style="font-size: 12px; color: #64748b; margin-top: 4px;">الإدارة المالية والحسابات العامة</div>
            </div>
            <div style="text-align: center;">
                <div style="background: {{ $transaction->type === 'receipt' ? '#27AE60' : ($transaction->type === 'payment' ? '#E74C3C' : '#2980B9') }}; color: white; padding: 6px 20px; border-radius: 99px; font-size: 18px; font-weight: 800; display: inline-block;">
                    {{ $transaction->type_name }}
                </div>
            </div>
            <div style="text-align: left; font-size: 13px;">
                <div>الرقم: <strong style="font-family: monospace; color: #068494;">{{ $transaction->transaction_number }}</strong></div>
                <div style="margin-top: 4px;">التاريخ: <strong>{{ $transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('Y/m/d') : '-' }}</strong></div>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <div class="amount-box">
                المبلغ: {{ number_format($transaction->amount, 2) }} ر.ي
            </div>
            <div style="font-size: 13px; color: #64748b;">
                طريقة الدفع: <strong>{{ $transaction->payment_method === 'cash' ? 'نقدي (كاش)' : ($transaction->payment_method === 'bank_transfer' ? 'تحويل بنكي' : $transaction->payment_method) }}</strong>
                @if($transaction->reference_number)
                    &bull; المرجع: <strong style="font-family: monospace;">{{ $transaction->reference_number }}</strong>
                @endif
            </div>
        </div>

        <div class="row-item">
            <div class="row-label">{{ $transaction->type === 'receipt' ? 'استلمنا من الأخ / السادة:' : 'صرفنا للأخ / السادة:' }}</div>
            <div class="row-value">{{ $transaction->resolved_party_name }}</div>
        </div>

        <div class="row-item">
            <div class="row-label">مبلغاً وقدره:</div>
            <div class="row-value">{{ number_format($transaction->amount, 2) }} ريال يمني لا غير.</div>
        </div>

        <div class="row-item">
            <div class="row-label">وذلك مقابل:</div>
            <div class="row-value">{{ $transaction->title }}</div>
        </div>

        @if($transaction->description)
            <div class="row-item">
                <div class="row-label">ملاحظات وبيان:</div>
                <div class="row-value" style="font-weight: 400; color: #334155;">{{ $transaction->description }}</div>
            </div>
        @endif

        <div style="display: flex; justify-content: space-between; margin-top: 60px; padding-top: 20px; font-size: 14px;">
            <div style="text-align: center; width: 180px;">
                <div style="font-weight: 700;">المحاسب المسؤول</div>
                <div style="font-size: 12px; color: #64748b; margin-top: 4px;">{{ $transaction->creator?->name ?? 'الإدارة' }}</div>
                <div style="margin-top: 40px; border-bottom: 1px solid #94a3b8;"></div>
            </div>
            <div style="text-align: center; width: 180px;">
                <div style="font-weight: 700;">المدير المالي</div>
                <div style="margin-top: 55px; border-bottom: 1px solid #94a3b8;"></div>
            </div>
            <div style="text-align: center; width: 180px;">
                <div style="font-weight: 700;">توقيع المستلم</div>
                <div style="margin-top: 55px; border-bottom: 1px solid #94a3b8;"></div>
            </div>
        </div>
    </div>
</body>
</html>
