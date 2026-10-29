@extends('admin.layout')

@section('title', 'إدارة وسائل الدفع | تسويق')
@section('page_title', 'إدارة بوابات وطرق الدفع')

@section('content')
    @php($editing = request()->filled('edit') ? $paymentMethods->firstWhere('id', (int) request('edit')) : null)

    <div class="panel" style="border: 2px solid var(--primary-soft); margin-bottom: 25px;">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap; margin-bottom:20px;">
            <div>
                <h3 style="margin:0 0 5px; color:var(--primary); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid {{ $editing ? 'fa-pen-to-square' : 'fa-credit-card' }}"></i>
                    <span>{{ $editing ? 'تعديل بيانات وسيلة الدفع' : 'إضافة وسيلة دفع جديدة' }}</span>
                </h3>
                <span style="color:var(--text-muted); font-size:12px;">تحكم في الاسم، رقم الحساب/المحفظة، الأيقونة، والألوان</span>
            </div>
            @if($editing)
                <a href="{{ route('admin.payment-methods') }}" class="btn btn-outline" style="font-size: 12px;">إلغاء التعديل</a>
            @endif
        </div>
        <form action="{{ $editing ? route('admin.payment-methods.update', $editing) : route('admin.payment-methods.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px;">
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">كود الوسيلة (Code) <span style="color: var(--danger);">*</span></label>
                    <input name="code" value="{{ old('code', $editing->code ?? '') }}" placeholder="مثال: jaib أو kuraimi" required style="width:100%; padding:12px; border:1px solid var(--border); border-radius:12px; direction: ltr; text-align: right;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">اسم الوسيلة <span style="color: var(--danger);">*</span></label>
                    <input name="name" value="{{ old('name', $editing->name ?? '') }}" placeholder="مثال: محفظة جيب" required style="width:100%; padding:12px; border:1px solid var(--border); border-radius:12px;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">رقم الحساب / المحفظة</label>
                    <input name="account_number" value="{{ old('account_number', $editing->account_number ?? '') }}" placeholder="770000000" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:12px; direction: ltr; text-align: right;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">ترتيب الظهور</label>
                    <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $editing->sort_order ?? 0) }}" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:12px;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">لون خلفية البطاقة</label>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <input type="color" name="background_color" value="{{ old('background_color', $editing->background_color ?? '#FFFFFF') }}" oninput="this.nextElementSibling.value=this.value" style="width:48px; height:44px; padding:2px; border:1px solid var(--border); border-radius:10px; cursor: pointer;">
                        <input type="text" value="{{ old('background_color', $editing->background_color ?? '#FFFFFF') }}" oninput="this.previousElementSibling.value=this.value" style="flex: 1; padding: 12px; border: 1px solid var(--border); border-radius: 12px; font-family: monospace;">
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">لون النص</label>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <input type="color" name="text_color" value="{{ old('text_color', $editing->text_color ?? '#1A1C1E') }}" oninput="this.nextElementSibling.value=this.value" style="width:48px; height:44px; padding:2px; border:1px solid var(--border); border-radius:10px; cursor: pointer;">
                        <input type="text" value="{{ old('text_color', $editing->text_color ?? '#1A1C1E') }}" oninput="this.previousElementSibling.value=this.value" style="flex: 1; padding: 12px; border: 1px solid var(--border); border-radius: 12px; font-family: monospace;">
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">أيقونة / شعار الوسيلة</label>
                    <input type="file" name="icon" accept="image/*" style="width:100%; padding:9px; border:1px solid var(--border); border-radius:12px; background: white;">
                </div>
            </div>
            <div style="display:flex; gap:25px; flex-wrap:wrap; margin-top:20px;">
                <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13px; cursor: pointer;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $editing->is_active ?? true) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary);">
                    <span>تفعيل وسيلة الدفع</span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13px; cursor: pointer;">
                    <input type="hidden" name="requires_receipt" value="0">
                    <input type="checkbox" name="requires_receipt" value="1" {{ old('requires_receipt', $editing->requires_receipt ?? false) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary);">
                    <span>إلزام العميل برفع صورة الإيصال</span>
                </label>
            </div>
            <div style="margin-top:22px; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
                    <i class="fa-solid fa-check"></i>
                    <span>{{ $editing ? 'حفظ التعديلات' : 'إضافة وسيلة الدفع' }}</span>
                </button>
            </div>
        </form>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="text-align: center;">الشعار</th>
                        <th>اسم الوسيلة</th>
                        <th>رقم الحساب</th>
                        <th style="text-align: center;">لون الخلفية</th>
                        <th style="text-align: center;">شرط الإيصال</th>
                        <th style="text-align: center;">الحالة</th>
                        <th style="text-align: center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paymentMethods as $method)
                        <tr>
                            <td style="text-align: center;">
                                @if($method->icon)
                                    <img src="{{ route('media.file', ['path' => $method->icon]) }}" style="width:42px;height:42px;object-fit:contain;border-radius:10px;background:{{ $method->background_color }};padding:5px;border:1px solid var(--border);">
                                @else
                                    <div style="display:inline-flex;width:42px;height:42px;align-items:center;justify-content:center;border-radius:10px;background:{{ $method->background_color }};border:1px solid var(--border);color:var(--primary);">
                                        <i class="fa-solid fa-credit-card"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong style="font-size: 14px;">{{ $method->name }}</strong>
                                <small style="display:block;color:var(--text-muted);font-size: 11px;">كود: {{ $method->code }}</small>
                            </td>
                            <td style="font-weight: 700; direction: ltr; text-align: right;">{{ $method->account_number ?: '—' }}</td>
                            <td style="text-align: center;">
                                <span style="display:inline-block;width:28px;height:28px;border-radius:8px;background:{{ $method->background_color }};border:1px solid var(--border);" title="{{ $method->background_color }}"></span>
                            </td>
                            <td style="text-align: center;">
                                <span class="status-badge {{ $method->requires_receipt ? 'status-pending' : 'status-info' }}" style="font-size: 11px;">
                                    {{ $method->requires_receipt ? 'مطلوب إرفاق إيصال' : 'غير مطلوب' }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <form action="{{ route('admin.payment-methods.toggle', $method) }}" method="POST">
                                    @csrf
                                    <button class="status-badge {{ $method->is_active ? 'status-success' : 'status-danger' }}" type="submit" style="border: none; cursor: pointer; padding: 5px 12px;">
                                        <i class="fa-solid {{ $method->is_active ? 'fa-check' : 'fa-xmark' }}" style="font-size: 10px;"></i>
                                        <span>{{ $method->is_active ? 'مفعلة' : 'معطلة' }}</span>
                                    </button>
                                </form>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a class="btn btn-outline" href="{{ route('admin.payment-methods', ['edit' => $method->id]) }}" style="padding:6px 12px;font-size: 11px;">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span>تعديل</span>
                                    </a>
                                    @if($method->code !== 'cash')
                                        <form action="{{ route('admin.payment-methods.delete', $method) }}" method="POST" style="display:inline" onsubmit="return confirm('هل أنت متأكد من حذف وسيلة الدفع هذه؟')">
                                            @csrf
                                            <button class="btn btn-danger" type="submit" style="padding:6px 12px;font-size: 11px;">
                                                <i class="fa-solid fa-trash-can"></i>
                                                <span>حذف</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">
                                <i class="fa-solid fa-credit-card" style="font-size: 32px; display: block; margin-bottom: 10px; color: #CCD2D6;"></i>
                                لا توجد وسائل دفع مضافة
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
