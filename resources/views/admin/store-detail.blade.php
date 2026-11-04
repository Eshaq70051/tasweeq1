@extends('admin.layout')

@section('title', 'إدارة منيو: ' . $store->name . ' | تسويق')
@section('page_title', 'إدارة منيو ووجبات المتجر')

@section('content')
    @php
        $editingSection = request()->has('edit_section') ? \App\Models\StoreMenuSection::find(request('edit_section')) : null;
        $editingMeal = request()->has('edit_meal') ? \App\Models\Product::find(request('edit_meal')) : null;
    @endphp

    <!-- بطاقة معلومات المتجر العلوية -->
    <div class="panel" style="margin-bottom: 25px; background: linear-gradient(135deg, #ffffff 0%, #f4fbfb 100%); border: 1px solid var(--border);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div style="display: flex; align-items: center; gap: 18px;">
                @if($store->logo_url)
                    <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary); box-shadow: 0 4px 12px rgba(6, 132, 148, 0.15);">
                @else
                    <div style="width: 70px; height: 70px; border-radius: 50%; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 28px;">
                        <i class="fa-solid fa-store"></i>
                    </div>
                @endif
                <div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h2 style="margin: 0; font-size: 20px; font-weight: 800; color: var(--text-main);">{{ $store->name }}</h2>
                        <span class="badge" style="background: rgba(6, 132, 148, 0.1); color: var(--primary); padding: 4px 12px; border-radius: 99px; font-size: 12px; font-weight: 700;">
                            {{ $store->category->name ?? 'غير محدد' }}
                        </span>
                        @if($store->is_open)
                            <span class="status-badge status-success"><i class="fa-solid fa-circle-check"></i> مفتوح</span>
                        @else
                            <span class="status-badge status-danger"><i class="fa-solid fa-circle-xmark"></i> مغلق</span>
                        @endif
                    </div>
                    <div style="font-size: 13px; color: var(--text-muted); display: flex; align-items: center; gap: 15px; margin-top: 6px; flex-wrap: wrap;">
                        <span><i class="fa-solid fa-location-dot" style="color: var(--primary); margin-left: 4px;"></i> {{ $store->address ?: 'صنعاء' }}</span>
                        <span><i class="fa-solid fa-route" style="color: var(--primary); margin-left: 4px;"></i> {{ $store->distance ?? '1.5 كم' }}</span>
                        <span><i class="fa-regular fa-clock" style="color: var(--primary); margin-left: 4px;"></i> {{ $store->delivery_time ?? '30 دقيقة' }}</span>
                        <span><i class="fa-solid fa-star" style="color: #f1c40f; margin-left: 4px;"></i> {{ number_format($store->rating, 1) }}</span>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="{{ route('admin.stores', ['edit' => $store->id]) }}" class="btn btn-outline">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>تعديل بيانات المتجر</span>
                </a>
                <a href="{{ route('admin.stores') }}" class="btn btn-outline">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>العودة لقائمة المتاجر</span>
                </a>
            </div>
        </div>
    </div>

    <!-- شبكة منيو المتجر: أقسام على اليمين والوجبات على اليسار -->
    <div style="display: grid; grid-template-columns: 320px 1fr; gap: 24px; align-items: start;">
        
        <!-- العمود الأيمن: أقسام المنيو -->
        <div>
            <div class="panel" style="padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="margin: 0; font-size: 15px; font-weight: 800; color: var(--primary); display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-list-check"></i>
                        <span>أقسام المنيو ({{ $store->menuSections->count() }})</span>
                    </h3>
                </div>

                <!-- نموذج إضافة / تعديل قسم -->
                <form action="{{ $editingSection ? route('admin.stores.sections.update', $editingSection) : route('admin.stores.sections.store', $store) }}" method="POST" style="margin-bottom: 20px; background: #f8fbfb; padding: 14px; border-radius: 12px; border: 1px solid var(--border);">
                    @csrf
                    <div style="font-weight: 700; font-size: 13px; margin-bottom: 10px; color: var(--text-main);">
                        {{ $editingSection ? 'تعديل قسم: ' . $editingSection->name : 'إضافة قسم جديد للمنيو' }}
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <input type="text" name="name" value="{{ old('name', $editingSection->name ?? '') }}" placeholder="اسم القسم (مثال: وجبات سريعة)" required style="padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border); font-size: 13px;">
                        <div style="display: flex; gap: 8px;">
                            <input type="number" name="sort_order" value="{{ old('sort_order', $editingSection->sort_order ?? 0) }}" placeholder="الترتيب" style="width: 80px; padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border); font-size: 13px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 8px 12px; font-size: 12px;">
                                <i class="fa-solid fa-check"></i>
                                <span>{{ $editingSection ? 'حفظ التعديل' : 'إضافة القسم' }}</span>
                            </button>
                            @if($editingSection)
                                <a href="{{ route('admin.stores.show', $store) }}" class="btn btn-outline" style="padding: 8px 10px; font-size: 12px;">إلغاء</a>
                            @endif
                        </div>
                    </div>
                </form>

                <!-- قائمة الأقسام -->
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="{{ route('admin.stores.show', $store) }}" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 13px; {{ !request('section_id') ? 'background: var(--primary); color: white;' : 'background: #f8fafc; color: var(--text-main); border: 1px solid var(--border);' }}">
                        <span>جميع الوجبات</span>
                        <span class="badge" style="background: rgba(0,0,0,0.1); padding: 2px 8px; border-radius: 99px; font-size: 11px;">
                            {{ $store->products->count() }}
                        </span>
                    </a>

                    @forelse($store->menuSections as $section)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; border-radius: 10px; {{ request('section_id') == $section->id ? 'background: rgba(6, 132, 148, 0.12); border: 1px solid var(--primary);' : 'background: #f8fafc; border: 1px solid var(--border);' }}">
                            <a href="{{ route('admin.stores.show', ['store' => $store->id, 'section_id' => $section->id]) }}" style="text-decoration: none; font-weight: 700; font-size: 13px; color: var(--text-main); flex: 1; display: flex; align-items: center; gap: 6px;">
                                <span>{{ $section->name }}</span>
                                <span style="font-size: 11px; color: var(--text-muted);">({{ $section->products->count() }})</span>
                            </a>
                            <div style="display: flex; gap: 4px;">
                                <a href="{{ route('admin.stores.show', ['store' => $store->id, 'edit_section' => $section->id]) }}" style="color: var(--text-muted); padding: 4px; font-size: 12px;" title="تعديل">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form action="{{ route('admin.stores.sections.delete', $section) }}" method="POST" onsubmit="return confirm('حذف هذا القسم؟');" style="display: inline;">
                                    @csrf
                                    <button type="submit" style="background: none; border: none; color: var(--danger); padding: 4px; cursor: pointer; font-size: 12px;" title="حذف">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 20px 10px; color: var(--text-muted); font-size: 13px;">
                            لا توجد أقسام منيو حتى الآن. أضف قسماً من النموذج أعلاه.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- العمود الأيسر: الوجبات والمنتجات -->
        <div>
            <!-- زر فتح إضافة وجبة -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <button class="btn btn-primary" onclick="document.getElementById('meal-form-panel').style.display='block'; window.scrollTo({top: 250, behavior: 'smooth'});">
                    <i class="fa-solid fa-plus"></i>
                    <span>إضافة وجبة / صنف جديد</span>
                </button>
                <div style="font-weight: 700; color: var(--text-muted); font-size: 13px;">
                    إجمالي الوجبات في هذا العرض: 
                    @php
                        $filteredProducts = request('section_id') 
                            ? $store->products->where('store_menu_section_id', request('section_id')) 
                            : $store->products;
                    @endphp
                    {{ $filteredProducts->count() }}
                </div>
            </div>

            <!-- نموذج إضافة / تعديل وجبة -->
            <div class="panel" id="meal-form-panel" style="{{ $editingMeal ? 'display: block;' : 'display: none;' }} margin-bottom: 24px; border: 2px solid var(--primary-soft);">
                <h3 style="margin-top: 0; margin-bottom: 18px; color: var(--primary); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid {{ $editingMeal ? 'fa-pen-to-square' : 'fa-utensils' }}"></i>
                    <span>{{ $editingMeal ? 'تعديل بيانات الوجبة: ' . $editingMeal->name : 'إضافة وجبة جديدة للمتجر' }}</span>
                </h3>

                <form action="{{ $editingMeal ? route('admin.stores.products.update', $editingMeal) : route('admin.stores.products.store', $store) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <label style="font-weight: 700; font-size: 13px;">اسم الوجبة / الصنف <span style="color: var(--danger);">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $editingMeal->name ?? '') }}" placeholder="مثال: شاورما دجاج صاروخ" required style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border);">
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <label style="font-weight: 700; font-size: 13px;">قسم المنيو</label>
                            <select name="store_menu_section_id" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); background: white;">
                                <option value="">بدون قسم محدد</option>
                                @foreach($store->menuSections as $section)
                                    <option value="{{ $section->id }}" {{ (old('store_menu_section_id', $editingMeal->store_menu_section_id ?? request('section_id')) == $section->id) ? 'selected' : '' }}>
                                        {{ $section->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <label style="font-weight: 700; font-size: 13px;">السعر الحالي (ريال) <span style="color: var(--danger);">*</span></label>
                            <input type="number" step="0.01" name="price" value="{{ old('price', $editingMeal->price ?? '') }}" placeholder="مثال: 1500" required style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border);">
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <label style="font-weight: 700; font-size: 13px;">السعر السابق قبل الخصم (اختياري)</label>
                            <input type="number" step="0.01" name="old_price" value="{{ old('old_price', $editingMeal->old_price ?? '') }}" placeholder="مثال: 1800" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border);">
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <label style="font-weight: 700; font-size: 13px;">صورة الوجبة</label>
                            <input type="file" name="image" accept="image/*" style="padding: 8px; border-radius: 10px; border: 1px solid var(--border); background: white;">
                            @if($editingMeal && $editingMeal->image)
                                <img src="{{ str_starts_with($editingMeal->image, 'http') ? $editingMeal->image : route('media.file', ['path' => $editingMeal->image]) }}" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover; margin-top: 6px;">
                            @endif
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <label style="font-weight: 700; font-size: 13px;">الوصف / المكونات</label>
                            <input type="text" name="details" value="{{ old('details', $editingMeal->details ?? '') }}" placeholder="مثال: ثومية، مخلل، بطاطس مقلية" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border);">
                        </div>
                    </div>

                    <div style="margin-top: 15px;">
                        <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 700; font-size: 13px;">
                            <input type="checkbox" name="is_available" value="1" {{ old('is_available', $editingMeal ? $editingMeal->is_available : true) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary);">
                            <span>الوجبة متوفرة للطلب حالياً</span>
                        </label>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px;">
                        <button type="button" class="btn btn-outline" onclick="document.getElementById('meal-form-panel').style.display='none';">إلغاء</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-check"></i>
                            <span>{{ $editingMeal ? 'تحديث بيانات الوجبة' : 'حفظ الوجبة' }}</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- جدول الوجبات والمنتجات -->
            <div class="panel">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>الوجبة / الصنف</th>
                                <th>القسم</th>
                                <th>السعر</th>
                                <th style="text-align: center;">التوفر</th>
                                <th style="text-align: center;">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($filteredProducts as $meal)
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            @if($meal->image)
                                                <img src="{{ str_starts_with($meal->image, 'http') ? $meal->image : route('media.file', ['path' => $meal->image]) }}" alt="{{ $meal->name }}" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover;">
                                            @else
                                                <div style="width: 44px; height: 44px; border-radius: 8px; background: #f0f7f8; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                                    <i class="fa-solid fa-utensils"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div style="font-weight: 700; font-size: 14px;">{{ $meal->name }}</div>
                                                @if($meal->details)
                                                    <div style="font-size: 11px; color: var(--text-muted); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                        {{ $meal->details }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $sec = $store->menuSections->firstWhere('id', $meal->store_menu_section_id);
                                        @endphp
                                        <span class="badge" style="background: rgba(6, 132, 148, 0.08); color: var(--primary); padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 700;">
                                            {{ $sec ? $sec->name : 'عام' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 800; color: var(--primary); font-size: 14px;">
                                            {{ number_format($meal->price) }} {{ $meal->currency }}
                                        </div>
                                        @if($meal->old_price && $meal->old_price > $meal->price)
                                            <div style="font-size: 11px; text-decoration: line-through; color: var(--text-muted);">
                                                {{ number_format($meal->old_price) }} {{ $meal->currency }}
                                            </div>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        @if($meal->is_available)
                                            <span class="status-badge status-success">متوفر</span>
                                        @else
                                            <span class="status-badge status-danger">غير متوفر</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center; white-space: nowrap;">
                                        <div style="display: inline-flex; gap: 6px;">
                                            <a href="{{ route('admin.stores.show', ['store' => $store->id, 'edit_meal' => $meal->id]) }}" class="btn btn-outline" style="padding: 6px 10px; font-size: 12px;" title="تعديل">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <form action="{{ route('admin.stores.products.delete', $meal) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الوجبة؟');" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-danger" style="padding: 6px 10px; font-size: 12px;" title="حذف">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                        <i class="fa-solid fa-bowl-food" style="font-size: 32px; color: #cbd5e1; margin-bottom: 8px; display: block;"></i>
                                        لا توجد وجبات في هذا القسم. اضغط على "إضافة وجبة / صنف جديد" لإضافة أول وجبة.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection
