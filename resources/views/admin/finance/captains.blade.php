@extends('admin.layout')

@section('title', 'حسابات الكباتن والذمم المالية - الإدارة المالية')
@section('page_title', 'حسابات الكباتن والذمم النقدية')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: var(--text-main);">حسابات الكباتن والذمم النقدية</h2>
        <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 13px;">متابعة الكاش المحصل (المدين)، عمولات التوصيل (الدائن)، والمبالغ المسددة للإدارة</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button type="button" class="btn btn-primary" onclick="openCaptainReceiptModal()">
            <i class="fa-solid fa-hand-holding-dollar"></i>
            <span>تسجيل سند قبض كاش من كابتن</span>
        </button>
    </div>
</div>

<!-- بطاقات الموقف المالي الإجمالي للكباتن -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي الكاش المحصل (المدين)</span>
            <span class="value" style="color: #E67E22;">{{ number_format($totals['cash_collected'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">مبالغ استلمها الكباتن نقداً</small>
        </div>
        <div class="icon-box" style="background: rgba(230, 126, 34, 0.12); color: #E67E22;"><i class="fa-solid fa-coins"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">عمولات التوصيل المكتسبة (الدائن)</span>
            <span class="value" style="color: #27AE60;">{{ number_format($totals['earned_commission'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
            <small style="color: #27AE60; font-size: 11px; margin-top: 4px;">حقوق الكباتن من التوصيل</small>
        </div>
        <div class="icon-box" style="background: rgba(39, 174, 96, 0.12); color: #27AE60;"><i class="fa-solid fa-route"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">المبالغ المسددة للإدارة</span>
            <span class="value" style="color: #2980B9;">{{ number_format($totals['paid_debt'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">سداد كاش وتحويلات معتمدة</small>
        </div>
        <div class="icon-box" style="background: rgba(41, 128, 185, 0.12); color: #2980B9;"><i class="fa-solid fa-circle-check"></i></div>
    </div>
    <div class="stat-card" style="border: 2px solid {{ $totals['outstanding_debt'] > 0 ? '#E74C3C' : '#27AE60' }};">
        <div class="info">
            <span class="title">صافي المديونية الحالية (بذمة الكباتن)</span>
            <span class="value" style="color: {{ $totals['outstanding_debt'] > 0 ? '#E74C3C' : '#27AE60' }};">
                {{ number_format($totals['outstanding_debt'], 2) }} <small style="font-size: 13px;">ر.ي</small>
            </span>
            <small style="font-weight: 700; color: {{ $totals['outstanding_debt'] > 0 ? '#E74C3C' : '#27AE60' }}; margin-top: 4px;">
                مستحق توريده إلى خزينة المنصة
            </small>
        </div>
        <div class="icon-box" style="background: rgba(231, 76, 60, 0.12); color: #E74C3C;"><i class="fa-solid fa-scale-balanced"></i></div>
    </div>
</div>

<!-- بطاقة البحث والفلترة -->
<div class="filter-card">
    <form method="GET" action="{{ route('admin.finance.captains') }}" style="display: flex; gap: 12px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 250px;">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="ابحث باسم الكابتن أو رقم الهاتف أو البريد..." style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1px solid var(--border);">
        </div>
        <div style="min-width: 150px;">
            <select name="status" style="width: 100%; padding: 10px; border-radius: 12px; border: 1px solid var(--border);">
                <option value="">كافة الحالات</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غير نشط / محظور</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span>بحث</span>
        </button>
        @if(request()->hasAny(['q', 'status']))
            <a href="{{ route('admin.finance.captains') }}" class="btn btn-outline">إلغاء الفلتر</a>
        @endif
    </form>
</div>

<!-- جدول حسابات الكباتن -->
<div style="background: white; border-radius: 18px; border: 1px solid var(--border); padding: 22px; box-shadow: 0 4px 14px rgba(0,0,0,0.02);">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted);">
                    <th style="padding: 12px 14px;">الكابتن</th>
                    <th style="padding: 12px 14px; text-align: center;">الطلبات المنجزة</th>
                    <th style="padding: 12px 14px;">المحصل كاش (مدين عليه)</th>
                    <th style="padding: 12px 14px;">عمولاته المستحقة (دائن له)</th>
                    <th style="padding: 12px 14px;">المسدد للإدارة (دائن)</th>
                    <th style="padding: 12px 14px;">صافي الذمة (المديونية)</th>
                    <th style="padding: 12px 14px; text-align: center;">الحالة المالية</th>
                    <th style="padding: 12px 14px; text-align: center;">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($captains as $captain)
                    @php $sum = $captainsFinancials[$captain->id] ?? null; @endphp
                    <tr style="border-bottom: 1px solid var(--border); transition: background 0.15s ease;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">
                        <td style="padding: 14px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(230, 126, 34, 0.1); color: #E67E22; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px;">
                                    <i class="fa-solid fa-motorcycle"></i>
                                </div>
                                <div>
                                    <a href="{{ route('admin.finance.captains.statement', $captain) }}" style="font-weight: 800; color: var(--text-main); text-decoration: none; font-size: 14px;">
                                        {{ $captain->name }}
                                    </a>
                                    <div style="color: var(--text-muted); font-size: 11px;">
                                        {{ $captain->phone ?: 'لا يوجد هاتف' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 14px; text-align: center;">
                            <span style="background: #F4F7F8; padding: 4px 10px; border-radius: 99px; font-weight: 700;">
                                {{ $sum ? $sum['completed_orders_count'] : 0 }}
                            </span>
                        </td>
                        <td style="padding: 14px; font-weight: 700; color: #E67E22;">
                            {{ number_format($sum ? $sum['cash_collected'] : 0, 2) }} ر.ي
                        </td>
                        <td style="padding: 14px; font-weight: 700; color: #27AE60;">
                            +{{ number_format($sum ? $sum['earned_commission'] : 0, 2) }} ر.ي
                        </td>
                        <td style="padding: 14px; font-weight: 700; color: #2980B9;">
                            {{ number_format($sum ? $sum['paid_debt'] : 0, 2) }} ر.ي
                            @if(!empty($sum['pending_payments']) && $sum['pending_payments'] > 0)
                                <small style="display: block; color: #F39C12; font-size: 10px;">معلق: {{ number_format($sum['pending_payments'], 2) }} ر.ي</small>
                            @endif
                        </td>
                        <td style="padding: 14px;">
                            @php $debt = $sum ? $sum['outstanding_debt'] : 0; @endphp
                            <div style="font-size: 14px; font-weight: 800; color: {{ $debt > 0 ? '#E74C3C' : '#27AE60' }};">
                                {{ number_format($debt, 2) }} ر.ي
                            </div>
                        </td>
                        <td style="padding: 14px; text-align: center;">
                            @if($debt > 0)
                                <span style="background: rgba(231, 76, 60, 0.1); color: #E74C3C; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 800;">
                                    مدين (عليه مبالغ)
                                </span>
                            @else
                                <span style="background: rgba(39, 174, 96, 0.1); color: #27AE60; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 800;">
                                    خالي الذمة
                                </span>
                            @endif
                        </td>
                        <td style="padding: 14px; text-align: center;">
                            <div style="display: inline-flex; gap: 6px;">
                                <a href="{{ route('admin.finance.captains.statement', $captain) }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 11px;" title="كشف الحساب وسجل العمليات">
                                    <i class="fa-solid fa-file-invoice"></i>
                                    <span>كشف حساب</span>
                                </a>
                                <button type="button" class="btn btn-primary" style="padding: 6px 12px; font-size: 11px;" onclick="openQuickReceiptModal('{{ $captain->id }}', '{{ addslashes($captain->name) }}', '{{ $debt }}')" title="تسجيل سند قبض كاش">
                                    <i class="fa-solid fa-hand-holding-dollar"></i>
                                    <span>قبض كاش</span>
                                </button>
                                <button type="button" class="btn btn-outline" style="padding: 6px 10px; font-size: 11px;" onclick="openEditCaptainModal('{{ $captain->id }}', '{{ addslashes($captain->name) }}', '{{ addslashes($captain->phone ?? '') }}', '{{ addslashes($captain->email) }}', '{{ $captain->status }}', '{{ $captain->is_available ? 1 : 0 }}')" title="تعديل بيانات الكابتن">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding: 40px; text-align: center; color: var(--text-muted);">
                            <i class="fa-solid fa-motorcycle" style="font-size: 32px; color: #CBD5E1; margin-bottom: 10px; display: block;"></i>
                            لم يتم العثور على أي كباتن مسجلين.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        <span class="info-text">عرض {{ $captains->firstItem() ?? 0 }} إلى {{ $captains->lastItem() ?? 0 }} من إجمالي {{ $captains->total() }} كابتن</span>
        {{ $captains->links() }}
    </div>
</div>

<!-- Modal: تسجيل سند قبض من كابتن -->
<div id="captainReceiptModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 520px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
        <div style="background: var(--primary); color: white; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800;">تسجيل سند قبض نقدية من كابتن</h3>
            <button type="button" onclick="closeCaptainReceiptModal()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.finance.transactions.store') }}" style="padding: 24px;">
            @csrf
            <input type="hidden" name="type" value="receipt">
            <input type="hidden" name="party_type" value="captain">
            <input type="hidden" name="direction" value="credit">
            <input type="hidden" name="party_id" id="modal_captain_id" value="">
            <input type="hidden" name="party_name" id="modal_captain_name_hidden" value="">

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">الكابتن</label>
                <input type="text" id="modal_captain_name" readonly class="form-control" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); background: #F8FAFC; font-weight: 700;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">المبلغ المقبوض (ر.ي) <span style="color:red">*</span></label>
                    <input type="number" step="0.01" name="amount" id="modal_captain_amount" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 800; font-size: 15px;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">تاريخ القبض <span style="color:red">*</span></label>
                    <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">طريقة القبض <span style="color:red">*</span></label>
                    <select name="payment_method" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                        <option value="cash">نقدي بالخزينة (كاش)</option>
                        <option value="bank_transfer">تحويل بنكي / صرافة</option>
                        <option value="wallet">محفظة إلكترونية</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">رقم الإشعار / السند</label>
                    <input type="text" name="reference_number" placeholder="مثلاً: REC-8832" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">البيان <span style="color:red">*</span></label>
                <input type="text" name="title" id="modal_captain_title" required value="سداد كاش طلبات من الكابتن" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">ملاحظات</label>
                <textarea name="description" rows="2" placeholder="أية ملاحظات إضافية..." style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeCaptainReceiptModal()">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ وتنزيل من ذمة الكابتن</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openQuickReceiptModal(id, name, debt) {
        document.getElementById('modal_captain_id').value = id;
        document.getElementById('modal_captain_name').value = name;
        document.getElementById('modal_captain_name_hidden').value = name;
        document.getElementById('modal_captain_amount').value = debt > 0 ? debt : '';
        document.getElementById('modal_captain_title').value = "سداد كاش توريد مديونية من الكابتن " + name;
        document.getElementById('captainReceiptModal').style.display = 'flex';
    }

    function closeCaptainReceiptModal() {
        document.getElementById('captainReceiptModal').style.display = 'none';
    }

    function openEditCaptainModal(id, name, phone, email, status, isAvailable) {
        document.getElementById('editCaptainForm').action = "/admin/captains/" + id + "/update";
        document.getElementById('modal_captain_edit_title').textContent = name;
        document.getElementById('edit_cap_name').value = name;
        document.getElementById('edit_cap_phone').value = phone;
        document.getElementById('edit_cap_email').value = email;
        document.getElementById('edit_cap_status').value = status;
        document.getElementById('edit_cap_available').value = isAvailable;
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
                <span>تعديل بيانات الكابتن: <span id="modal_captain_edit_title"></span></span>
            </h3>
            <button type="button" onclick="closeEditCaptainModal()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form id="editCaptainForm" method="POST" action="" enctype="multipart/form-data" style="padding: 24px;">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">اسم الكابتن <span style="color: red;">*</span></label>
                    <input type="text" name="name" id="edit_cap_name" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">رقم الهاتف</label>
                    <input type="text" name="phone" id="edit_cap_phone" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">البريد الإلكتروني <span style="color: red;">*</span></label>
                <input type="email" name="email" id="edit_cap_email" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">كلمة المرور الجديدة (اختياري)</label>
                <input type="password" name="password" placeholder="اتركها فارغة في حال عدم الرغبة في التغيير" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                <small style="color: var(--text-muted); font-size: 11px;">الحد الأدنى 6 خانات في حال التغيير</small>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">حالة الحساب <span style="color: red;">*</span></label>
                    <select name="status" id="edit_cap_status" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 700;">
                        <option value="active">نشط (مفعل)</option>
                        <option value="inactive">معطل (محظور مؤقتاً)</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">حالة التوفر والاتصال</label>
                    <select name="is_available" id="edit_cap_available" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 700;">
                        <option value="1">متصل (جاهز لاستقبال الطلبات)</option>
                        <option value="0">غير متصل (غير متاح)</option>
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
