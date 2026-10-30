@extends('admin.layout')

@section('title', 'إدارة الكوبونات والخصومات | تسويق')
@section('page_title', 'كوبونات وقسائم الخصم')

@section('content')
    @php
        $editingCoupon = request()->has('edit') ? \App\Models\Coupon::find(request('edit')) : null;
    @endphp

    <!-- إحصائيات سريعة للكوبونات -->
    <div class="stat-grid" style="margin-bottom: 22px;">
        <div class="stat-card">
            <div class="info">
                <span class="title">إجمالي الكوبونات</span>
                <span class="value">{{ $stats['total'] ?? $coupons->total() }}</span>
            </div>
            <div class="icon-box">
                <i class="fa-solid fa-ticket"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="info">
                <span class="title">الكوبونات الفعالة</span>
                <span class="value" style="color: var(--success);">{{ $stats['active'] ?? 0 }}</span>
            </div>
            <div class="icon-box" style="background: rgba(39, 174, 96, 0.12); color: #168447;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="info">
                <span class="title">مرات الاستخدام الكلية</span>
                <span class="value" style="color: var(--primary);">{{ $stats['total_uses'] ?? 0 }}</span>
            </div>
            <div class="icon-box" style="background: var(--primary-soft); color: var(--primary);">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>
    </div>

    <!-- شريط الإجراءات والفلترة -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 15px; flex-wrap: wrap;">
        <button class="btn btn-primary" onclick="document.getElementById('coupon-form-panel').style.display='block'; window.scrollTo({top: 0, behavior: 'smooth'});">
            <i class="fa-solid {{ $editingCoupon ? 'fa-pen-to-square' : 'fa-plus' }}"></i>
            <span>{{ $editingCoupon ? 'تعديل بيانات الكوبون' : 'إنشاء كود خصم جديد' }}</span>
        </button>
    </div>

    <!-- بطاقة البحث والفلترة -->
    <div class="filter-card">
        <form action="{{ route('admin.coupons') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; align-items: end;">
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                    <i class="fa-solid fa-magnifying-glass" style="margin-left: 4px;"></i>
                    البحث
                </label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="ابحث بكود الخصم أو الوصف..." style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); width: 100%;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                    <i class="fa-solid fa-percent" style="margin-left: 4px;"></i>
                    نوع الخصم
                </label>
                <select name="type" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); width: 100%; background: white;">
                    <option value="">-- كل الأنواع --</option>
                    <option value="percentage" {{ request('type') == 'percentage' ? 'selected' : '' }}>نسبة مئوية (%)</option>
                    <option value="fixed" {{ request('type') == 'fixed' ? 'selected' : '' }}>مبلغ مالي ثابت (ريال)</option>
                </select>
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                    <i class="fa-solid fa-toggle-on" style="margin-left: 4px;"></i>
                    الحالة
                </label>
                <select name="status" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); width: 100%; background: white;">
                    <option value="">-- كل الحالات --</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>مفعّل</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>معطّل</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; height: 42px;">
                    <i class="fa-solid fa-filter"></i>
                    <span>تصفية</span>
                </button>
                @if(request()->anyFilled(['q', 'type', 'status']))
                    <a href="{{ route('admin.coupons') }}" class="btn btn-outline" style="height: 42px;" title="إعادة ضبط">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- نموذج إنشاء وتعديل الكوبون -->
    <div class="panel" id="coupon-form-panel" style="{{ $editingCoupon ? 'display: block;' : 'display: none;' }} margin-bottom: 25px; border: 2px solid var(--primary-soft);">
        <h3 style="margin-top: 0; color: var(--primary); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid {{ $editingCoupon ? 'fa-pen-to-square' : 'fa-ticket' }}"></i>
            <span>{{ $editingCoupon ? 'تعديل بيانات كود الخصم #' . $editingCoupon->code : 'إنشاء كود خصم جديد' }}</span>
        </h3>
        <form action="{{ $editingCoupon ? route('admin.coupons.update', $editingCoupon) : route('admin.coupons.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13px; font-weight: 700;">كود الخصم (الكوبون) <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $editingCoupon->code ?? '') }}" required placeholder="مثال: SAVE20 أو EID2026" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border); text-transform: uppercase; font-weight: 800; letter-spacing: 1px;">
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13px; font-weight: 700;">نوع الخصم <span style="color: var(--danger);">*</span></label>
                    <select name="type" required style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                        <option value="percentage" {{ old('type', $editingCoupon->type ?? 'percentage') === 'percentage' ? 'selected' : '' }}>نسبة مئوية (%)</option>
                        <option value="fixed" {{ old('type', $editingCoupon->type ?? '') === 'fixed' ? 'selected' : '' }}>مبلغ مالي ثابت (ريال)</option>
                    </select>
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13px; font-weight: 700;">قيمة الخصم <span style="color: var(--danger);">*</span></label>
                    <input type="number" step="0.01" name="value" value="{{ old('value', $editingCoupon->value ?? '') }}" required placeholder="مثلاً: 10 لـ 10% أو 500 ريال" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13px; font-weight: 700;">الحد الأدنى للطلب (ريال)</label>
                    <input type="number" step="0.01" name="min_order_amount" value="{{ old('min_order_amount', $editingCoupon->min_order_amount ?? 0) }}" placeholder="0.00" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13px; font-weight: 700;">سقف الخصم الأقصى (للنسبة المئوية)</label>
                    <input type="number" step="0.01" name="max_discount_amount" value="{{ old('max_discount_amount', $editingCoupon->max_discount_amount ?? '') }}" placeholder="اتركه فارغاً إن لم يكن هناك حد" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13px; font-weight: 700;">إجمالي مرات الاستخدام المسموحة</label>
                    <input type="number" name="usage_limit" value="{{ old('usage_limit', $editingCoupon->usage_limit ?? '') }}" placeholder="اتركه فارغاً لعدد لا نهائي" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13px; font-weight: 700;">أقصى استخدام لكل عميل</label>
                    <input type="number" name="usage_limit_per_user" value="{{ old('usage_limit_per_user', $editingCoupon->usage_limit_per_user ?? 1) }}" min="1" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13px; font-weight: 700;">تاريخ الانتهاء</label>
                    <input type="date" name="expires_at" value="{{ old('expires_at', $editingCoupon && $editingCoupon->expires_at ? $editingCoupon->expires_at->format('Y-m-d') : '') }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="margin-top: 14px;">
                <label style="font-size: 13px; font-weight: 700;">وصف العرض أو الملاحظات</label>
                <input type="text" name="description" value="{{ old('description', $editingCoupon->description ?? '') }}" placeholder="مثلاً: عرض نهاية الأسبوع أو خصم للعملاء الجدد" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border); width: 100%; margin-top: 6px;">
            </div>

            <div style="margin-top: 16px; display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="is_active" value="1" id="coupon_active" {{ old('is_active', $editingCoupon ? $editingCoupon->is_active : true) ? 'checked' : '' }} style="width: 18px; height: 18px;">
                <label for="coupon_active" style="font-size: 13px; font-weight: 700; cursor: pointer;">تفعيل الكوبون للعمل فوراً</label>
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <a href="{{ route('admin.coupons') }}" class="btn btn-outline">إلغاء</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-check"></i>
                    <span>{{ $editingCoupon ? 'تحديث الكوبون' : 'حفظ الكوبون' }}</span>
                </button>
            </div>
        </form>
    </div>

    <!-- جدول الكوبونات -->
    <div class="panel">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>الكود</th>
                        <th>نوع وقيمة الخصم</th>
                        <th>شروط التطبيق</th>
                        <th>مرات الاستخدام</th>
                        <th>تاريخ الانتهاء</th>
                        <th style="text-align: center;">الحالة</th>
                        <th style="text-align: center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="background: var(--primary-soft); color: var(--primary); width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                                        <i class="fa-solid fa-ticket"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 800; font-size: 15px; color: var(--primary); letter-spacing: 0.5px;">{{ $coupon->code }}</div>
                                        <small style="color: var(--text-muted); font-size: 11px;">{{ $coupon->description ?: 'بدون وصف' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($coupon->type === 'percentage')
                                    <span class="status-badge" style="background: rgba(6, 132, 148, 0.12); color: var(--primary); font-weight: 800; font-size: 13px;">
                                        <i class="fa-solid fa-percent" style="font-size: 10px;"></i>
                                        <span>خصم {{ $coupon->value }}%</span>
                                    </span>
                                    @if($coupon->max_discount_amount)
                                        <small style="display: block; color: var(--text-muted); font-size: 10px; margin-top: 4px;">(سقف أقصى: {{ number_format($coupon->max_discount_amount, 2) }} ريال)</small>
                                    @endif
                                @else
                                    <span class="status-badge status-success" style="font-weight: 800; font-size: 13px;">
                                        <span>خصم {{ number_format($coupon->value, 2) }} ريال</span>
                                    </span>
                                @endif
                            </td>
                            <td style="font-size: 12px;">
                                <div>أدنى طلب: <strong>{{ number_format($coupon->min_order_amount, 2) }}</strong> ريال</div>
                                <small style="color: var(--text-muted);">{{ $coupon->usage_limit_per_user }} استخدام/عميل</small>
                            </td>
                            <td>
                                <div style="font-weight: 700; font-size: 13px;">
                                    {{ $coupon->used_count }}
                                    @if($coupon->usage_limit)
                                        <span style="color: var(--text-muted); font-size: 11px;">/ {{ $coupon->usage_limit }}</span>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 11px;">(غير محدود)</span>
                                    @endif
                                </div>
                            </td>
                            <td style="font-size: 12px; color: var(--text-muted);">
                                @if($coupon->expires_at)
                                    @if($coupon->expires_at->isPast())
                                        <span style="color: var(--danger); font-weight: 700;">منتهي ({{ $coupon->expires_at->format('Y-m-d') }})</span>
                                    @else
                                        <span>{{ $coupon->expires_at->format('Y-m-d') }}</span>
                                    @endif
                                @else
                                    <span style="color: var(--success); font-weight: 600;">مستمر بلا انتهاء</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <form action="{{ route('admin.coupons.toggle', $coupon) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="status-badge {{ $coupon->is_active ? 'status-success' : 'status-danger' }}" style="border: none; cursor: pointer;" title="انقر لتغيير التفعيل">
                                        <i class="fa-solid {{ $coupon->is_active ? 'fa-check' : 'fa-ban' }}" style="font-size: 10px;"></i>
                                        <span>{{ $coupon->is_active ? 'مفعّل' : 'معطّل' }}</span>
                                    </button>
                                </form>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('admin.coupons', array_merge(request()->query(), ['edit' => $coupon->id])) }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 11px;" title="تعديل">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span>تعديل</span>
                                    </a>
                                    <form action="{{ route('admin.coupons.delete', $coupon) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الكوبون نهائياً؟')">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 11px;" title="حذف">
                                            <i class="fa-solid fa-trash-can"></i>
                                            <span>حذف</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="fa-solid fa-ticket" style="font-size: 32px; display: block; margin-bottom: 10px; color: #CCD2D6;"></i>
                                لا توجد كوبونات خصم مسجلة حتى الآن
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- الترقيم والتنقل بين الصفحات -->
    <div class="pagination-container">
        <div class="info-text">
            عرض <strong>{{ $coupons->firstItem() ?? 0 }}</strong> إلى <strong>{{ $coupons->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $coupons->total() }}</strong> كوبون
        </div>
        <div>
            {{ $coupons->links() }}
        </div>
    </div>
@endsection
