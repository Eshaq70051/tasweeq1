@extends('admin.layout')

@section('title', 'إدارة المستخدمين | تسويق')
@section('page_title', 'إدارة المستخدمين والحسابات')

@section('content')
    @php
        $editingUser = request()->has('edit') ? \App\Models\User::find(request('edit')) : null;
    @endphp

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; flex-wrap: wrap;">
        <button class="btn btn-primary" onclick="document.getElementById('user-form').style.display='block'; window.scrollTo({top: 0, behavior: 'smooth'});">
            <i class="fa-solid {{ $editingUser ? 'fa-user-pen' : 'fa-user-plus' }}"></i>
            <span>{{ $editingUser ? 'تعديل الحساب' : 'إضافة مستخدم جديد' }}</span>
        </button>
        <div style="position: relative; flex: 1; min-width: 260px;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
            <input type="text" id="userSearchInput" onkeyup="filterUsers()" placeholder="ابحث باسم المستخدم أو البريد..." style="width: 100%; padding: 12px 42px 12px 16px; border-radius: 14px; border: 1px solid var(--border); outline: none;">
        </div>
        <div style="background: white; padding: 10px 20px; border-radius: 99px; border: 1px solid var(--border); font-weight: 700; color: var(--primary); font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-users"></i>
            <span>إجمالي الأعضاء: {{ $users->count() }}</span>
        </div>
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

    <div class="panel">
        <div class="table-responsive">
            <table class="table" id="usersTable">
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>الدور / الصلاحية</th>
                        <th style="text-align: center;">الحالة</th>
                        <th style="text-align: center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
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
                                    <a href="{{ route('admin.users', ['edit' => $user->id]) }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 11px;" title="تعديل">
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
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function filterUsers() {
            const input = document.getElementById('userSearchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('usersTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const text = tr[i].textContent || tr[i].innerText;
                if (text.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    </script>
@endsection
