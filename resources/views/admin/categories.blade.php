@extends('admin.layout')

@section('title', 'إدارة التصنيفات | تسويق')
@section('page_title', 'تصنيفات المنتجات')

@section('content')
    @php
        $editingCategory = request()->has('edit') ? \App\Models\Category::find(request('edit')) : null;
    @endphp

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; flex-wrap: wrap;">
        <button class="btn btn-primary" onclick="document.getElementById('category-form-panel').style.display='block'; window.scrollTo({top: 0, behavior: 'smooth'});">
            <i class="fa-solid fa-plus"></i>
            <span>إضافة تصنيف جديد</span>
        </button>
        <div style="background: white; padding: 10px 20px; border-radius: 99px; border: 1px solid var(--border); font-weight: 700; color: var(--primary); font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-shapes"></i>
            <span>إجمالي التصنيفات: {{ count($categories) }}</span>
        </div>
    </div>

    <!-- نموذج إضافة وتعديل التصنيف -->
    <div class="panel" id="category-form-panel" style="{{ $editingCategory ? 'display: block;' : 'display: none;' }} margin-bottom: 30px; border: 2px solid var(--primary-soft);">
        <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--primary); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid {{ $editingCategory ? 'fa-pen-to-square' : 'fa-folder-plus' }}"></i>
            <span>{{ $editingCategory ? 'تعديل بيانات التصنيف' : 'إضافة تصنيف جديد' }}</span>
        </h3>
        
        <form action="{{ $editingCategory ? route('admin.categories.update', $editingCategory) : route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px;">
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">اسم التصنيف <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $editingCategory->name ?? '') }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" placeholder="مثال: إلكترونيات" required>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">وصف التصنيف</label>
                    <input type="text" name="description" value="{{ old('description', $editingCategory->description ?? '') }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" placeholder="وصف موجز للمنتجات المندرجة">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">أيقونة / صورة التصنيف</label>
                    <input type="file" name="image" accept="image/*" style="padding: 9px; border-radius: 12px; border: 1px solid var(--border); background: #fff;">
                    @if($editingCategory && $editingCategory->image)
                        <img src="{{ route('media.file', ['path' => $editingCategory->image]) }}" alt="category image" style="width: 60px; height: 60px; object-fit: contain; background: #f8f9fa; border-radius: 10px; margin-top: 8px; border: 1px solid var(--border);">
                    @endif
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <a href="{{ route('admin.categories') }}" class="btn btn-outline">إلغاء</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-check"></i>
                    <span>{{ $editingCategory ? 'تحديث التصنيف' : 'حفظ التصنيف' }}</span>
                </button>
            </div>
        </form>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>التصنيف</th>
                        <th>الوصف</th>
                        <th style="text-align: center;">عدد المنتجات</th>
                        <th style="text-align: center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 40px; height: 40px; border-radius: 10px; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                        @if($category->image)
                                            <img src="{{ route('media.file', ['path' => $category->image]) }}" alt="{{ $category->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                                        @else
                                            <i class="fa-solid fa-shapes"></i>
                                        @endif
                                    </div>
                                    <a href="{{ route('admin.products', ['category_id' => $category->id]) }}" style="font-weight: 800; color: var(--primary); text-decoration: none; font-size: 14px;">
                                        {{ $category->name }}
                                    </a>
                                </div>
                            </td>
                            <td style="color: var(--text-muted); font-size: 12px;">
                                {{ $category->description ?? '—' }}
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('admin.products', ['category_id' => $category->id]) }}" class="status-badge" style="background: var(--primary-soft); color: var(--primary); text-decoration: none;">
                                    <i class="fa-solid fa-box" style="font-size: 10px;"></i>
                                    <span>{{ $category->products_count }} منتج</span>
                                </a>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('admin.categories', ['edit' => $category->id]) }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 11px;" title="تعديل">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span>تعديل</span>
                                    </a>
                                    <form action="{{ route('admin.categories.delete', $category) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا التصنيف؟');">
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
                            <td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="fa-solid fa-shapes" style="font-size: 32px; display: block; margin-bottom: 10px; color: #CCD2D6;"></i>
                                لا توجد تصنيفات مسجلة
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
