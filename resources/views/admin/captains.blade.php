@extends('admin.layout')

@section('title', 'إدارة فريق التوصيل')
@section('page_title', 'فريق الكباتن')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; flex-wrap: wrap;">
        <button class="btn btn-primary" onclick="document.getElementById('add-captain-form').style.display='block'">➕ إضافة كابتن جديد</button>
        <div style="background: white; padding: 10px 20px; border-radius: 99px; border: 1px solid var(--border); font-weight: bold; color: var(--primary);">
            إجمالي الكباتن: {{ count($captains) }}
        </div>
    </div>

    <!-- نموذج إضافة كابتن -->
    <div id="add-captain-form" class="panel" style="display: none; margin-bottom: 25px; border: 2px solid var(--primary-soft);">
        <h3 style="margin-top: 0; color: var(--primary);">تسجيل كابتن جديد</h3>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="role" value="captain">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <input type="text" name="name" placeholder="اسم الكابتن" required style="padding: 12px; border-radius: 12px; border: 1px solid var(--border); outline: none;">
                <input type="email" name="email" placeholder="البريد الإلكتروني" required style="padding: 12px; border-radius: 12px; border: 1px solid var(--border); outline: none;">
                <input type="password" name="password" placeholder="كلمة المرور" required style="padding: 12px; border-radius: 12px; border: 1px solid var(--border); outline: none;">
                <input type="text" name="phone" placeholder="رقم الهاتف" required style="padding: 12px; border-radius: 12px; border: 1px solid var(--border); outline: none;">
            </div>
            <div style="margin-top: 15px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">حفظ الكابتن</button>
                <button type="button" class="btn" style="background: #eee;" onclick="document.getElementById('add-captain-form').style.display='none'">إلغاء</button>
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
                        <th>الحالة الآن</th>
                        <th>الحساب المالي</th>
                        <th>حساب النظام</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($captains as $captain)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="user-avatar" style="background: var(--secondary); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                        {{ mb_substr($captain->name, 0, 1) }}
                                    </div>
                                    <div style="font-weight: bold;">{{ $captain->name }}</div>
                                </div>
                            </td>
                            <td>{{ $captain->phone }}<br><small>{{ $captain->email }}</small></td>
                            <td>
                                <span class="status-badge {{ $captain->is_available ? 'status-success' : 'status-danger' }}" style="font-size: 11px;">
                                    {{ $captain->is_available ? '🟢 متصل (جاهز)' : '⚪ غير متوفر' }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: bold; color: var(--danger);">مديونية: {{ number_format($captain->outstanding_debt, 2) }}</div>
                                <small>محصل نقداً: {{ number_format($captain->cash_collected ?? 0, 2) }}</small><br>
                                <small>طلبات مكتملة: {{ $captain->completed_orders_count }}</small><br>
                                <a href="{{ route('admin.captains.show', $captain) }}" style="color: var(--primary); font-weight: bold;">التفاصيل والتسديدات</a>
                            </td>
                            <td>
                                <form action="{{ route('admin.users.toggleStatus', $captain) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="status-badge {{ $captain->status == 'active' ? 'status-success' : 'status-danger' }}" style="border: none; cursor: pointer; padding: 5px 12px;">
                                        {{ $captain->status == 'active' ? 'نشط' : 'معطل' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div style="display: flex; gap: 10px;">
                                    <form action="{{ route('admin.users.updateRole', $captain) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="role" value="user">
                                        <button type="submit" class="btn" style="padding: 5px 10px; font-size: 11px; background: #eee;">إلغاء صفة كابتن</button>
                                    </form>
                                    <form action="{{ route('admin.users.delete', $captain) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الكابتن؟')">
                                        @csrf
                                        <button type="submit" style="background: none; border: none; color: var(--danger); cursor: pointer; font-size: 18px;">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">لا يوجد كباتن مسجلين حالياً</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
