@extends('admin.layout')

@section('title', 'إدارة التصنيفات')
@section('page_title', 'تصنيفات المنتجات')

@section('content')
    @php
        $editingCategory = request()->has('edit') ? \App\Models\Category::find(request('edit')) : null;
    @endphp

    <div style="margin-bottom: 25px;">
        <button class="btn btn-primary" onclick="document.getElementById('category-form-panel').style.display='block';">
            ➕ إضافة تصنيف جديد
        </button>
    </div>

    <div class="panel" id="category-form-panel" style="{{ $editingCategory ? 'display: block;' : 'display: none;' }} margin-bottom: 30px;">
        <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--primary);">{{ $editingCategory ? 'تعديل التصنيف' : 'إضافة تصنيف جديد' }}</h3>
        
        <form action="{{ $editingCategory ? route('admin.categories.update', $editingCategory) : route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-weight: 700; font-size: 14px;">اسم التصنيف</label>
                    <input type="text" name="name" value="{{ old('name', $editingCategory->name ?? '') }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" required>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-weight: 700; font-size: 14px;">الوصف</label>
                    <input type="text" name="description" value="{{ old('description', $editingCategory->description ?? '') }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-weight: 700; font-size: 14px;">صورة التصنيف</label>
                    <input type="file" name="image" accept="image/*" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border); background: #fff;">
                    @if($editingCategory && $editingCategory->image)
                        <img src="{{ route('media.file', ['path' => $editingCategory->image]) }}" alt="category image" style="width: 80px; height: 80px; object-fit: contain; background: #f8f9fa; border-radius: 12px; margin-top: 8px;">
                    @endif
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <a href="{{ route('admin.categories') }}" class="btn" style="background: #eee; color: #666;">إلغاء</a>
                <button type="submit" class="btn btn-primary">{{ $editingCategory ? 'تحديث' : 'حفظ' }}</button>
            </div>
        </form>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>عدد المنتجات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td style="font-weight: 800;">
                                <a href="{{ route('admin.products', ['category_id' => $category->id]) }}" style="color: var(--primary); text-decoration: none;">
                                    {{ $category->name }}
                                </a>
                            </td>
                            <td style="font-weight: 700; color: var(--primary);">
                                <a href="{{ route('admin.products', ['category_id' => $category->id]) }}" style="color: inherit; text-decoration: none;">
                                    {{ $category->products_count }} منتج
                                </a>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="{{ route('admin.categories', ['edit' => $category->id]) }}" class="btn" style="padding: 6px 12px; font-size: 11px; background: var(--primary-soft); color: var(--primary);">تعديل</a>
                                    <form action="{{ route('admin.categories.delete', $category) }}" method="POST" onsubmit="return confirm('حذف التصنيف؟');">
                                        @csrf
                                        <button type="submit" class="btn" style="padding: 6px 12px; font-size: 11px; background: rgba(255, 77, 77, 0.05); color: var(--danger);">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align: center; padding: 30px;">لا توجد تصنيفات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
