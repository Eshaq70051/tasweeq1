@extends('admin.layout')

@section('title', 'إدارة فريق التوصيل | تسويق')
@section('page_title', 'فريق الكباتن والتوصيل')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; flex-wrap: wrap;">
        <button class="btn btn-primary" onclick="document.getElementById('add-captain-form').style.display='block'; window.scrollTo({top: 0, behavior: 'smooth'});">
            <i class="fa-solid fa-user-plus"></i>
            <span>إضافة كابتن جديد</span>
        </button>
        <div style="background: white; padding: 10px 22px; border-radius: 99px; border: 1px solid var(--border); font-weight: 700; color: var(--primary); font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-motorcycle"></i>
            <span>إجمالي الكباتن: {{ count($captains) }}</span>
        </div>
    </div>

    <!-- نموذج إضافة كابتن -->
    <div id="add-captain-form" class="panel" style="display: none; margin-bottom: 25px; border: 2px solid var(--primary-soft);">
        <h3 style="margin-top: 0; color: var(--primary); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-user-plus"></i>
            <span>تسجيل كابتن توصيل جديد</span>
        </h3>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="role" value="captain">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13px; font-weight: 700;">اسم الكابتن</label>
                    <input type="text" name="name" placeholder="الاسم الكامل" required style="padding: 12px; border-radius: 12px; border: 1px solid var(--border); outline: none;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13px; font-weight: 700;">البريد الإلكتروني</label>
                    <input type="email" name="email" placeholder="captain@example.com" required style="padding: 12px; border-radius: 12px; border: 1px solid var(--border); outline: none;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13px; font-weight: 700;">كلمة المرور</label>
                    <input type="password" name="password" placeholder="••••••••" required style="padding: 12px; border-radius: 12px; border: 1px solid var(--border); outline: none;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13px; font-weight: 700;">رقم الهاتف</label>
                    <input type="text" name="phone" placeholder="770000000" required style="padding: 12px; border-radius: 12px; border: 1px solid var(--border); outline: none;">
                </div>
            </div>
            <div style="margin-top: 18px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('add-captain-form').style.display='none'">إلغاء</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-check"></i>
                    <span>حفظ بيانات الكابتن</span>
                </button>
            </div>
        </form>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>الكابتن</th>
                        <th>التواصل</th>
                        <th style="text-align: center;">حالة الاتصال</th>
                        <th>الحساب المالي</th>
                        <th style="text-align: center;">حالة الحساب</th>
                        <th style="text-align: center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($captains as $captain)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="background: linear-gradient(135deg, var(--secondary), var(--primary)); color: white; width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px;">
                                        {{ mb_substr($captain->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 800; font-size: 14px;">{{ $captain->name }}</div>
                                        <small style="color: var(--text-muted); font-size: 11px;">#{{ $captain->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: 13px; direction: ltr; text-align: right;">
                                    <i class="fa-solid fa-phone" style="font-size: 11px; color: var(--text-muted); margin-left: 4px;"></i>
                                    {{ $captain->phone ?: 'لا يوجد' }}
                                </div>
                                <small style="color: var(--text-muted); font-size: 11px;">{{ $captain->email }}</small>
                            </td>
                            <td style="text-align: center;">
                                @if($captain->is_available)
                                    <span class="status-badge status-success" style="font-size: 11px;">
                                        <span class="online-dot active"></span>
                                        <span>متصل (جاهز)</span>
                                    </span>
                                @else
                                    <span class="status-badge status-pending" style="font-size: 11px; background: #EDF2F7; color: #718096;">
                                        <span class="online-dot offline"></span>
                                        <span>غير متصل</span>
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 800; color: var(--danger); font-size: 13px;">
                                    المديونية: {{ number_format($captain->outstanding_debt, 2) }} ريال
                                </div>
                                <small style="color: var(--text-muted); font-size: 11px; display: block;">
                                    المحصل: {{ number_format($captain->cash_collected ?? 0, 2) }} ريال
                                </small>
                                <small style="color: var(--text-muted); font-size: 11px; display: block;">
                                    الطلبات المسلمة: {{ $captain->completed_orders_count }} طلب
                                </small>
                                <a href="{{ route('admin.captains.show', $captain) }}" style="color: var(--primary); font-weight: 700; font-size: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-top: 4px;">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                    <span>كشف الحساب والتسديدات</span>
                                </a>
                            </td>
                            <td style="text-align: center;">
                                <form action="{{ route('admin.users.toggleStatus', $captain) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="status-badge {{ $captain->status == 'active' ? 'status-success' : 'status-danger' }}" style="border: none; cursor: pointer; padding: 5px 12px;" title="تغيير حالة النشاط">
                                        <i class="fa-solid {{ $captain->status == 'active' ? 'fa-check' : 'fa-ban' }}" style="font-size: 10px;"></i>
                                        <span>{{ $captain->status == 'active' ? 'نشط' : 'معطل' }}</span>
                                    </button>
                                </form>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <form action="{{ route('admin.users.updateRole', $captain) }}" method="POST" onsubmit="return confirm('هل تريد إزالة صفة الكابتن وتحويله إلى مستخدم عادي؟')">
                                        @csrf
                                        <input type="hidden" name="role" value="user">
                                        <button type="submit" class="btn btn-outline" style="padding: 6px 10px; font-size: 11px;" title="إلغاء صفة كابتن">
                                            <i class="fa-solid fa-user-minus"></i>
                                            <span>إلغاء صفة</span>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.users.delete', $captain) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الكابتن نهائياً؟')">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 10px; font-size: 11px;" title="حذف">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="fa-solid fa-motorcycle" style="font-size: 32px; display: block; margin-bottom: 10px; color: #CCD2D6;"></i>
                                لا يوجد كباتن مسجلين حالياً
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
