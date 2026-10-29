@extends('admin.layout')

@section('title', 'وسائل الدفع')
@section('page_title', 'إدارة وسائل الدفع')

@section('content')
    @php($editing = request()->filled('edit') ? $paymentMethods->firstWhere('id', (int) request('edit')) : null)

    <div class="panel" style="border: 2px solid var(--primary-soft);">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap; margin-bottom:20px;">
            <div>
                <h3 style="margin:0 0 5px; color:var(--primary);">{{ $editing ? 'تعديل وسيلة الدفع' : 'إضافة وسيلة دفع' }}</h3>
                <span style="color:var(--text-muted); font-size:12px;">تحكم في الاسم والرقم والأيقونة والخلفية والتفعيل</span>
            </div>
            @if($editing)<a href="{{ route('admin.payment-methods') }}" class="btn" style="background:#eee;">إلغاء التعديل</a>@endif
        </div>
        <form action="{{ $editing ? route('admin.payment-methods.update', $editing) : route('admin.payment-methods.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:15px;">
                <div><label>الكود</label><input name="code" value="{{ old('code', $editing->code ?? '') }}" placeholder="jaib" required style="width:100%; padding:12px; border:1px solid var(--border); border-radius:12px;"></div>
                <div><label>اسم الوسيلة</label><input name="name" value="{{ old('name', $editing->name ?? '') }}" placeholder="جيب" required style="width:100%; padding:12px; border:1px solid var(--border); border-radius:12px;"></div>
                <div><label>رقم الحساب</label><input name="account_number" value="{{ old('account_number', $editing->account_number ?? '') }}" placeholder="776870051" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:12px;"></div>
                <div><label>الترتيب</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $editing->sort_order ?? 0) }}" style="width:100%; padding:12px; border:1px solid var(--border); border-radius:12px;"></div>
                <div><label>لون الخلفية</label><input type="color" name="background_color" value="{{ old('background_color', $editing->background_color ?? '#FFFFFF') }}" style="width:100%; height:44px; padding:3px; border:1px solid var(--border); border-radius:12px;"></div>
                <div><label>لون النص</label><input type="color" name="text_color" value="{{ old('text_color', $editing->text_color ?? '#1A1C1E') }}" style="width:100%; height:44px; padding:3px; border:1px solid var(--border); border-radius:12px;"></div>
                <div><label>أيقونة الوسيلة</label><input type="file" name="icon" accept="image/*" style="width:100%; padding:9px; border:1px solid var(--border); border-radius:12px;"></div>
            </div>
            <div style="display:flex; gap:25px; flex-wrap:wrap; margin-top:18px;">
                <label><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $editing->is_active ?? true) ? 'checked' : '' }}> تفعيل الوسيلة</label>
                <label><input type="hidden" name="requires_receipt" value="0"><input type="checkbox" name="requires_receipt" value="1" {{ old('requires_receipt', $editing->requires_receipt ?? false) ? 'checked' : '' }}> طلب صورة إيصال</label>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:20px;">{{ $editing ? 'حفظ التعديل' : 'إضافة الوسيلة' }}</button>
        </form>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>الأيقونة</th><th>الوسيلة</th><th>الرقم</th><th>الخلفية</th><th>الإيصال</th><th>الحالة</th><th>الإجراءات</th></tr></thead>
                <tbody>
                    @forelse($paymentMethods as $method)
                        <tr>
                            <td>@if($method->icon)<img src="{{ route('media.file', ['path' => $method->icon]) }}" style="width:42px;height:42px;object-fit:contain;border-radius:10px;background:{{ $method->background_color }};padding:5px;">@else<span style="display:inline-flex;width:42px;height:42px;align-items:center;justify-content:center;border-radius:10px;background:{{ $method->background_color }};">💳</span>@endif</td>
                            <td><strong>{{ $method->name }}</strong><small style="display:block;color:var(--text-muted);">{{ $method->code }}</small></td>
                            <td>{{ $method->account_number ?: '-' }}</td>
                            <td><span style="display:inline-block;width:26px;height:26px;border-radius:7px;background:{{ $method->background_color }};border:1px solid var(--border);"></span></td>
                            <td>{{ $method->requires_receipt ? 'مطلوب' : 'غير مطلوب' }}</td>
                            <td><form action="{{ route('admin.payment-methods.toggle', $method) }}" method="POST">@csrf<button class="status-badge {{ $method->is_active ? 'status-success' : 'status-danger' }}" type="submit">{{ $method->is_active ? 'مفعلة' : 'معطلة' }}</button></form></td>
                            <td><a class="btn" href="{{ route('admin.payment-methods', ['edit' => $method->id]) }}" style="padding:6px 10px;background:var(--primary-soft);color:var(--primary);">تعديل</a> @if($method->code !== 'cash')<form action="{{ route('admin.payment-methods.delete', $method) }}" method="POST" style="display:inline" onsubmit="return confirm('حذف وسيلة الدفع؟')">@csrf<button class="btn" type="submit" style="padding:6px 10px;background:#fff1f1;color:var(--danger);">حذف</button></form>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;padding:35px;">لا توجد وسائل دفع</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
