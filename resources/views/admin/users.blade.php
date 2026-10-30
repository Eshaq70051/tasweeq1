@extends('admin.layout')

@section('title', 'إدارة المستخدمين | تسويق')
@section('page_title', 'إدارة المستخدمين والحسابات')

@section('content')
    @php
        $editingUser = request()->has('edit') ? \App\Models\User::find(request('edit')) : null;
    @endphp

    <!-- إحصائيات سريعة للمستخدمين -->
    <div class="stat-grid" style="margin-bottom: 22px;">
        <div class="stat-card">
            <div class="info">
                <span class="title">إجمالي الأعضاء</span>
                <span class="value">{{ $stats['total'] ?? $users->total() }}</span>
            </div>
            <div class="icon-box">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="info">
                <span class="title">الحسابات النشطة</span>
                <span class="value" style="color: var(--success);">{{ $stats['active'] ?? 0 }}</span>
            </div>
            <div class="icon-box" style="background: rgba(39, 174, 96, 0.12); color: #168447;">
                <i class="fa-solid fa-user-check"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="info">
                <span class="title">الحسابات المعطلة</span>
                <span class="value" style="color: var(--danger);">{{ $stats['inactive'] ?? 0 }}</span>
            </div>
            <div class="icon-box" style="background: rgba(255, 77, 77, 0.12); color: #c93636;">
                <i class="fa-solid fa-user-slash"></i>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 15px; flex-wrap: wrap;">
        <button class="btn btn-primary" onclick="document.getElementById('user-form').style.display='block'; window.scrollTo({top: 0, behavior: 'smooth'});">
            <i class="fa-solid {{ $editingUser ? 'fa-user-pen' : 'fa-user-plus' }}"></i>
            <span>{{ $editingUser ? 'تعديل الحساب' : 'إضافة مستخدم جديد' }}</span>
        </button>
    </div>

    <!-- بطاقة البحث والفلترة -->
    <div class="filter-card">
        <form action="{{ route('admin.users') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; align-items: end;">
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                    <i class="fa-solid fa-magnifying-glass" style="margin-left: 4px;"></i>
                    البحث
                </label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="الاسم، البريد الإلكتروني، الهاتف..." style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); width: 100%;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                    <i class="fa-solid fa-shield-halved" style="margin-left: 4px;"></i>
                    الدور / الصلاحية
                </label>
                <select name="role" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); width: 100%; background: white;">
                    <option value="">-- كل الصلاحيات --</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>عميل (مستخدم عادي)</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>مدير نظام</option>
                </select>
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                    <i class="fa-solid fa-toggle-on" style="margin-left: 4px;"></i>
                    حالة الحساب
                </label>
                <select name="status" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); width: 100%; background: white;">
                    <option value="">-- كل الحالات --</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>معطل / محظور</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; height: 42px;">
                    <i class="fa-solid fa-filter"></i>
                    <span>تصفية</span>
                </button>
                @if(request()->anyFilled(['q', 'role', 'status']))
                    <a href="{{ route('admin.users') }}" class="btn btn-outline" style="height: 42px;" title="إعادة ضبط">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- نموذج إضافة وتعديل الحسابات -->
    <div id="user-form" class="panel" style="{{ $editingUser ? 'display: block;' : 'display: none;' }} margin-bottom: 25px; border: 2px solid var(--primary-soft);">
        <h3 style="margin-top: 0; color: var(--primary); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid {{ $editingUser ? 'fa-user-pen' : 'fa-user-plus' }}"></i>
            <span>{{ $editingUser ? 'تعديل بيانات الحساب' : 'إنشاء حساب مستخدم جديد' }}</span>
        </h3>
        <form action="{{ $editingUser ? route('admin.users.update', $editingUser) : route('admin.users.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13px; font-weight: 700;">الاسم الكامل <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $editingUser->name ?? '') }}" required style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" placeholder="الاسم ثلاثي أو ثنائي">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13px; font-weight: 700;">البريد الإلكتروني <span style="color: var(--danger);">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $editingUser->email ?? '') }}" required style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" placeholder="user@example.com">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13px; font-weight: 700;">الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone', $editingUser->phone ?? '') }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" placeholder="777123456">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13px; font-weight: 700;">كلمة المرور {{ $editingUser ? '(اختياري - اتركها فارغة للإبقاء)' : '*' }}</label>
                    <input type="password" name="password" {{ $editingUser ? '' : 'required' }} style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" placeholder="••••••••">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-size: 13px; font-weight: 700;">نوع الصلاحية / الدور <span style="color: var(--danger);">*</span></label>
                    <select name="role" required style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                        <option value="user" {{ (old('role', $editingUser->role ?? '') == 'user') ? 'selected' : '' }}>مستخدم (عميل)</option>
                        <option value="captain" {{ (old('role', $editingUser->role ?? '') == 'captain') ? 'selected' : '' }}>كابتن توصيل</option>
                        <option value="admin" {{ (old('role', $editingUser->role ?? '') == 'admin') ? 'selected' : '' }}>مدير نظام</option>
                    </select>
                </div>
            </div>
            <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <a href="{{ route('admin.users') }}" class="btn btn-outline">إلغاء</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-check"></i>
                    <span>{{ $editingUser ? 'تحديث البيانات' : 'حفظ الحساب' }}</span>
                </button>
            </div>
        </form>
    </div>

    <!-- جدول المستخدمين -->
    <div class="panel">
        <div class="table-responsive">
            <table class="table" id="usersTable">
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>الهاتف</th>
                        <th>الدور / الصلاحية</th>
                        <th>تاريخ التسجيل</th>
                        <th style="text-align: center;">الحالة</th>
                        <th style="text-align: center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px;">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 14px;">{{ $user->name }}</div>
                                        <small style="color: var(--text-muted); font-size: 12px;">{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td style="color: var(--text-muted); font-size: 12px;">
                                {{ $user->phone ?? '—' }}
                            </td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="status-badge" style="background: rgba(6, 132, 148, 0.12); color: var(--primary);">
                                        <i class="fa-solid fa-shield-halved" style="font-size: 10px;"></i>
                                        <span>مدير نظام</span>
                                    </span>
                                @elseif($user->role === 'captain')
                                    <span class="status-badge" style="background: rgba(242, 153, 74, 0.14); color: #B96818;">
                                        <i class="fa-solid fa-motorcycle" style="font-size: 10px;"></i>
                                        <span>كابتن</span>
                                    </span>
                                @else
                                    <span class="status-badge" style="background: #EDF2F7; color: #4A5568;">
                                        <i class="fa-solid fa-user" style="font-size: 10px;"></i>
                                        <span>عميل</span>
                                    </span>
                                @endif
                            </td>
                            <td style="color: var(--text-muted); font-size: 12px;">
                                {{ $user->created_at ? $user->created_at->format('Y-m-d') : '—' }}
                            </td>
                            <td style="text-align: center;">
                                <form action="{{ route('admin.users.toggleStatus', $user) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="status-badge {{ $user->status == 'active' ? 'status-success' : 'status-danger' }}" style="border: none; cursor: pointer;" title="انقر لتغيير الحالة">
                                        <i class="fa-solid {{ $user->status == 'active' ? 'fa-check' : 'fa-ban' }}" style="font-size: 10px;"></i>
                                        <span>{{ $user->status == 'active' ? 'نشط' : 'محظور' }}</span>
                                    </button>
                                </form>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('admin.users', array_merge(request()->query(), ['edit' => $user->id])) }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 11px;" title="تعديل">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span>تعديل</span>
                                    </a>
                                    <form action="{{ route('admin.users.delete', $user) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الحساب نهائياً؟')">
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
                            <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="fa-solid fa-users" style="font-size: 32px; display: block; margin-bottom: 10px; color: #CCD2D6;"></i>
                                لا يوجد مستخدمين مطابقين لخيارات البحث
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
            عرض <strong>{{ $users->firstItem() ?? 0 }}</strong> إلى <strong>{{ $users->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $users->total() }}</strong> مستخدم
        </div>
        <div>
            {{ $users->links() }}
        </div>
    </div>
@endsection
