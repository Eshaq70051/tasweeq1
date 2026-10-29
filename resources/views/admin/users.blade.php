@extends('admin.layout')

@section('title', 'إدارة المستخدمين')
@section('page_title', 'إدارة الحسابات')

@section('content')
    @php
        $editingUser = request()->has('edit') ? \App\Models\User::find(request('edit')) : null;
    @endphp

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; flex-wrap: wrap;">
        <button class="btn btn-primary" onclick="document.getElementById('user-form').style.display='block'; window.scrollTo(0,0);">
            {{ $editingUser ? '📝 تعديل الحساب' : '➕ إضافة مستخدم جديد' }}
        </button>
        <div style="background: white; padding: 10px 20px; border-radius: 99px; border: 1px solid var(--border); font-weight: bold; color: var(--primary);">
            إجمالي الأعضاء: {{ $users->count() }}
        </div>
    </div>

    <!-- نموذج إضافة وتعديل الحسابات -->
    <div id="user-form" class="panel" style="{{ $editingUser ? 'display: block;' : 'display: none;' }} margin-bottom: 25px; border: 2px solid var(--primary-soft);">
        <h3 style="margin-top: 0; color: var(--primary);">{{ $editingUser ? 'تعديل بيانات الحساب' : 'إنشاء حساب جديد' }}</h3>
        <form action="{{ $editingUser ? route('admin.users.update', $editingUser) : route('admin.users.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label style="font-size: 13px; font-weight: bold;">الاسم الكامل</label>
                    <input type="text" name="name" value="{{ old('name', $editingUser->name ?? '') }}" required style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                </div>
                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label style="font-size: 13px; font-weight: bold;">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email', $editingUser->email ?? '') }}" required style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                </div>
                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label style="font-size: 13px; font-weight: bold;">كلمة المرور {{ $editingUser ? '(اختياري)' : '' }}</label>
                    <input type="password" name="password" {{ $editingUser ? '' : 'required' }} style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                </div>
                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label style="font-size: 13px; font-weight: bold;">الصلاحية</label>
                    <select name="role" required style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                        <option value="user" {{ (old('role', $editingUser->role ?? '') == 'user') ? 'selected' : '' }}>👤 مستخدم</option>
                        <option value="captain" {{ (old('role', $editingUser->role ?? '') == 'captain') ? 'selected' : '' }}>🛵 كابتن</option>
                        <option value="admin" {{ (old('role', $editingUser->role ?? '') == 'admin') ? 'selected' : '' }}>🔑 مدير</option>
                    </select>
                </div>
            </div>
            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">{{ $editingUser ? 'تحديث البيانات' : 'حفظ الحساب' }}</button>
                <a href="{{ route('admin.users') }}" class="btn" style="background: #eee; color: #666;">إلغاء</a>
            </div>
        </form>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>الدور</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>
                                <div style="font-weight: bold;">{{ $user->name }}</div>
                                <small style="color: var(--text-muted);">{{ $user->email }}</small>
                            </td>
                            <td>
                                <span class="status-badge" style="background: #eee; color: #555;">{{ $user->role }}</span>
                            </td>
                            <td>
                                <form action="{{ route('admin.users.toggleStatus', $user) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="status-badge {{ $user->status == 'active' ? 'status-success' : 'status-danger' }}" style="border: none; cursor: pointer;">
                                        {{ $user->status == 'active' ? 'نشط' : 'محظور' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div style="display: flex; gap: 10px;">
                                    <a href="{{ route('admin.users', ['edit' => $user->id]) }}" style="text-decoration: none;">✏️</a>
                                    <form action="{{ route('admin.users.delete', $user) }}" method="POST" onsubmit="return confirm('حذف نهائي؟')">
                                        @csrf
                                        <button type="submit" style="background: none; border: none; color: var(--danger); cursor: pointer;">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
