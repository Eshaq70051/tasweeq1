@extends('admin.layout')

@section('title', 'حسابات الموظفين - الإدارة المالية')
@section('page_title', 'حسابات الموظفين والرواتب')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: var(--text-main);">إدارة حسابات الموظفين</h2>
        <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 13px;">بيانات الكادر الوظيفي، الرواتب الأساسية والبدلات، وكشوف الحسابات الفردية</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('admin.finance.payroll') }}" class="btn btn-outline">
            <i class="fa-solid fa-money-check-dollar"></i>
            <span>مسير الرواتب الشهرية</span>
        </a>
        <button type="button" class="btn btn-primary" onclick="openCreateEmployeeModal()">
            <i class="fa-solid fa-user-plus"></i>
            <span>إضافة موظف جديد</span>
        </button>
    </div>
</div>

<!-- بطاقات إحصائيات الموظفين -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="info">
            <span class="title">إجمالي الموظفين</span>
            <span class="value" style="color: var(--primary);">{{ $stats['total'] }}</span>
        </div>
        <div class="icon-box"><i class="fa-solid fa-users"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">الموظفون على رأس العمل</span>
            <span class="value" style="color: #27AE60;">{{ $stats['active'] }}</span>
        </div>
        <div class="icon-box" style="background: rgba(39, 174, 96, 0.12); color: #27AE60;"><i class="fa-solid fa-user-check"></i></div>
    </div>
    <div class="stat-card">
        <div class="info">
            <span class="title">فاتورة الرواتب الشهرية التقديرية</span>
            <span class="value" style="color: #8E44AD;">{{ number_format($stats['total_payroll'], 2) }} <small style="font-size: 13px;">ر.ي</small></span>
        </div>
        <div class="icon-box" style="background: rgba(142, 68, 173, 0.12); color: #8E44AD;"><i class="fa-solid fa-money-bill-wave"></i></div>
    </div>
</div>

<!-- البحث والفلترة -->
<div class="filter-card">
    <form method="GET" action="{{ route('admin.finance.employees') }}" style="display: flex; gap: 12px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 250px;">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="ابحث باسم الموظف، المسمى الوظيفي، أو الهاتف..." style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1px solid var(--border);">
        </div>
        <div style="min-width: 150px;">
            <select name="status" style="width: 100%; padding: 10px; border-radius: 12px; border: 1px solid var(--border);">
                <option value="">كافة الحالات</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>على رأس العمل</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غير نشط / منتهي</option>
                <option value="on_leave" {{ request('status') === 'on_leave' ? 'selected' : '' }}>في إجازة</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span>بحث</span>
        </button>
        @if(request()->hasAny(['q', 'status']))
            <a href="{{ route('admin.finance.employees') }}" class="btn btn-outline">إلغاء</a>
        @endif
    </form>
</div>

<!-- جدول الموظفين -->
<div style="background: white; border-radius: 18px; border: 1px solid var(--border); padding: 22px; box-shadow: 0 4px 14px rgba(0,0,0,0.02);">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted);">
                    <th style="padding: 12px 14px;">الموظف</th>
                    <th style="padding: 12px 14px;">المسمى الوظيفي</th>
                    <th style="padding: 12px 14px;">الهاتف</th>
                    <th style="padding: 12px 14px;">الراتب الأساسي</th>
                    <th style="padding: 12px 14px;">البدلات</th>
                    <th style="padding: 12px 14px;">إجمالي الراتب</th>
                    <th style="padding: 12px 14px;">تاريخ التعيين</th>
                    <th style="padding: 12px 14px; text-align: center;">الحالة</th>
                    <th style="padding: 12px 14px; text-align: center;">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                    <tr style="border-bottom: 1px solid var(--border); transition: background 0.15s ease;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">
                        <td style="padding: 14px; font-weight: 800; color: var(--text-main);">
                            <a href="{{ route('admin.finance.employees.statement', $emp) }}" style="text-decoration: none; color: var(--text-main);">
                                {{ $emp->name }}
                            </a>
                        </td>
                        <td style="padding: 14px;">
                            <span style="background: #F4F7F8; padding: 4px 10px; border-radius: 8px; font-weight: 700; color: var(--primary);">
                                {{ $emp->job_title }}
                            </span>
                        </td>
                        <td style="padding: 14px; color: var(--text-muted); font-family: monospace;">{{ $emp->phone ?: '-' }}</td>
                        <td style="padding: 14px; font-weight: 700;">{{ number_format($emp->basic_salary, 2) }} ر.ي</td>
                        <td style="padding: 14px; color: #27AE60; font-weight: 700;">{{ number_format($emp->allowances, 2) }} ر.ي</td>
                        <td style="padding: 14px; font-weight: 800; color: #8E44AD; font-size: 14px;">
                            {{ number_format($emp->total_salary, 2) }} ر.ي
                        </td>
                        <td style="padding: 14px; color: var(--text-muted);">{{ $emp->hire_date ? $emp->hire_date->format('Y/m/d') : '-' }}</td>
                        <td style="padding: 14px; text-align: center;">
                            @if($emp->status === 'active')
                                <span style="background: rgba(39, 174, 96, 0.1); color: #27AE60; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 800;">على رأس العمل</span>
                            @elseif($emp->status === 'on_leave')
                                <span style="background: rgba(230, 126, 34, 0.1); color: #E67E22; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 800;">في إجازة</span>
                            @else
                                <span style="background: rgba(231, 76, 60, 0.1); color: #E74C3C; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 800;">غير نشط</span>
                            @endif
                        </td>
                        <td style="padding: 14px; text-align: center;">
                            <div style="display: inline-flex; gap: 6px;">
                                <a href="{{ route('admin.finance.employees.statement', $emp) }}" class="btn btn-outline" style="padding: 6px 10px; font-size: 11px;" title="كشف الحساب">
                                    <i class="fa-solid fa-file-invoice"></i>
                                </a>
                                <button type="button" class="btn btn-outline" style="padding: 6px 10px; font-size: 11px;" onclick="openEditEmployeeModal('{{ $emp->id }}', '{{ addslashes($emp->name) }}', '{{ addslashes($emp->phone ?? '') }}', '{{ addslashes($emp->email ?? '') }}', '{{ addslashes($emp->job_title) }}', '{{ $emp->basic_salary }}', '{{ $emp->allowances }}', '{{ $emp->hire_date ? $emp->hire_date->format('Y-m-d') : '' }}', '{{ addslashes($emp->national_id ?? '') }}', '{{ $emp->status }}', '{{ addslashes($emp->notes ?? '') }}')" title="تعديل">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.finance.employees.delete', $emp) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا الموظف؟');" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger" style="padding: 6px 10px; font-size: 11px;" title="حذف">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="padding: 40px; text-align: center; color: var(--text-muted);">
                            <i class="fa-solid fa-user-slash" style="font-size: 32px; color: #CBD5E1; margin-bottom: 10px; display: block;"></i>
                            لم يتم العثور على أي موظفين مسجلين. يمكنك إضافة أول موظف الآن.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        {{ $employees->links() }}
    </div>
</div>

<!-- Modal: إضافة موظف جديد -->
<div id="createEmployeeModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 580px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
        <div style="background: var(--primary); color: white; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800;">إضافة موظف جديد</h3>
            <button type="button" onclick="closeCreateEmployeeModal()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.finance.employees.store') }}" style="padding: 24px;">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">اسم الموظف <span style="color:red">*</span></label>
                    <input type="text" name="name" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">المسمى الوظيفي <span style="color:red">*</span></label>
                    <input type="text" name="job_title" placeholder="محاسب، كول سنتر، مشرف..." required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">رقم الهاتف</label>
                    <input type="text" name="phone" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">البريد الإلكتروني</label>
                    <input type="email" name="email" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">الراتب الأساسي (ر.ي) <span style="color:red">*</span></label>
                    <input type="number" step="0.01" name="basic_salary" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 800;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">البدلات الشهرية (ر.ي)</label>
                    <input type="number" step="0.01" name="allowances" value="0" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">تاريخ التعيين</label>
                    <input type="date" name="hire_date" value="{{ date('Y-m-d') }}" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">الحالة <span style="color:red">*</span></label>
                    <select name="status" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 700;">
                        <option value="active">على رأس العمل (نشط)</option>
                        <option value="on_leave">في إجازة</option>
                        <option value="inactive">غير نشط</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">ملاحظات</label>
                <textarea name="notes" rows="2" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeCreateEmployeeModal()">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ الموظف</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: تعديل بيانات موظف -->
<div id="editEmployeeModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 580px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
        <div style="background: var(--text-main); color: white; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800;">تعديل بيانات الموظف</h3>
            <button type="button" onclick="closeEditEmployeeModal()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer;">&times;</button>
        </div>
        <form id="editEmployeeForm" method="POST" action="" style="padding: 24px;">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">اسم الموظف <span style="color:red">*</span></label>
                    <input type="text" name="name" id="edit_emp_name" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">المسمى الوظيفي <span style="color:red">*</span></label>
                    <input type="text" name="job_title" id="edit_emp_job_title" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">رقم الهاتف</label>
                    <input type="text" name="phone" id="edit_emp_phone" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">البريد الإلكتروني</label>
                    <input type="email" name="email" id="edit_emp_email" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">الراتب الأساسي (ر.ي) <span style="color:red">*</span></label>
                    <input type="number" step="0.01" name="basic_salary" id="edit_emp_basic_salary" required style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 800;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">البدلات الشهرية (ر.ي)</label>
                    <input type="number" step="0.01" name="allowances" id="edit_emp_allowances" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">تاريخ التعيين</label>
                    <input type="date" name="hire_date" id="edit_emp_hire_date" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">الحالة <span style="color:red">*</span></label>
                    <select name="status" id="edit_emp_status" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); font-weight: 700;">
                        <option value="active">على رأس العمل (نشط)</option>
                        <option value="on_leave">في إجازة</option>
                        <option value="inactive">غير نشط</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">ملاحظات</label>
                <textarea name="notes" id="edit_emp_notes" rows="2" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border);"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeEditEmployeeModal()">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateEmployeeModal() {
        document.getElementById('createEmployeeModal').style.display = 'flex';
    }
    function closeCreateEmployeeModal() {
        document.getElementById('createEmployeeModal').style.display = 'none';
    }
    function openEditEmployeeModal(id, name, phone, email, job, salary, allowances, hire, nationalId, status, notes) {
        document.getElementById('editEmployeeForm').action = "/admin/finance/employees/" + id + "/update";
        document.getElementById('edit_emp_name').value = name;
        document.getElementById('edit_emp_job_title').value = job;
        document.getElementById('edit_emp_phone').value = phone;
        document.getElementById('edit_emp_email').value = email;
        document.getElementById('edit_emp_basic_salary').value = salary;
        document.getElementById('edit_emp_allowances').value = allowances;
        document.getElementById('edit_emp_hire_date').value = hire;
        document.getElementById('edit_emp_status').value = status;
        document.getElementById('edit_emp_notes').value = notes;
        document.getElementById('editEmployeeModal').style.display = 'flex';
    }
    function closeEditEmployeeModal() {
        document.getElementById('editEmployeeModal').style.display = 'none';
    }
</script>
@endsection
