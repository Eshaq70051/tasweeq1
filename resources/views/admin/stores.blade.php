@extends('admin.layout')

@section('title', 'إدارة المتاجر والمطاعم | تسويق')
@section('page_title', 'المتاجر والمطاعم')

@section('content')
    @php
        $editingStore = request()->has('edit') ? \App\Models\Store::find(request('edit')) : null;
    @endphp

    <!-- إحصائيات سريعة للمتاجر -->
    <div class="stat-grid" style="margin-bottom: 22px;">
        <div class="stat-card">
            <div class="info">
                <span class="title">إجمالي المتاجر</span>
                <span class="value">{{ $stats['total'] }}</span>
            </div>
            <div class="icon-box">
                <i class="fa-solid fa-store"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="info">
                <span class="title">المتاجر المفتوحة</span>
                <span class="value" style="color: var(--success);">{{ $stats['open'] }}</span>
            </div>
            <div class="icon-box" style="background: rgba(39, 174, 96, 0.12); color: #168447;">
                <i class="fa-solid fa-door-open"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="info">
                <span class="title">المتاجر المميزة (شائع)</span>
                <span class="value" style="color: #f39c12;">{{ $stats['featured'] }}</span>
            </div>
            <div class="icon-box" style="background: rgba(243, 156, 18, 0.12); color: #f39c12;">
                <i class="fa-solid fa-star"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="info">
                <span class="title">إجمالي الوجبات والمنتجات</span>
                <span class="value">{{ $stats['total_meals'] }}</span>
            </div>
            <div class="icon-box" style="background: rgba(6, 132, 148, 0.12); color: var(--primary);">
                <i class="fa-solid fa-utensils"></i>
            </div>
        </div>
    </div>

    <!-- شريط الإجراءات والفلترة -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 15px; flex-wrap: wrap;">
        <button class="btn btn-primary" onclick="document.getElementById('store-form-panel').style.display='block'; window.scrollTo({top: 0, behavior: 'smooth'});">
            <i class="fa-solid fa-plus"></i>
            <span>إضافة متجر / مطعم جديد</span>
        </button>
        <div style="background: white; padding: 8px 18px; border-radius: 99px; border: 1px solid var(--border); font-weight: 700; color: var(--primary); font-size: 13px;">
            <i class="fa-solid fa-shop" style="margin-left: 6px;"></i>
            عدد النتائج: {{ $stores->total() }}
        </div>
    </div>

    <!-- نموذج إضافة وتعديل المتجر -->
    <div class="panel" id="store-form-panel" style="{{ $editingStore ? 'display: block;' : 'display: none;' }} margin-bottom: 30px; border: 2px solid var(--primary-soft);">
        <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--primary); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid {{ $editingStore ? 'fa-pen-to-square' : 'fa-store' }}"></i>
            <span>{{ $editingStore ? 'تعديل بيانات المتجر: ' . $editingStore->name : 'إضافة متجر / مطعم جديد' }}</span>
        </h3>

        <form action="{{ $editingStore ? route('admin.stores.update', $editingStore) : route('admin.stores.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px;">
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">اسم المتجر / المطعم <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $editingStore->name ?? '') }}" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border);" placeholder="مثال: شاورما الريان" required>
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">التصنيف الرئيسي <span style="color: var(--danger);">*</span></label>
                    <select name="category_id" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); background: white;" required>
                        <option value="">اختر التصنيف...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (old('category_id', $editingStore->category_id ?? '') == $category->id) ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">العنوان / الفرع</label>
                    <input type="text" name="address" value="{{ old('address', $editingStore->address ?? '') }}" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border);" placeholder="مثال: شارع الخمسين - أمام الجولة">
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">رقم الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone', $editingStore->phone ?? '') }}" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border);" placeholder="مثال: 777123456">
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">المسافة المقدرة</label>
                    <input type="text" name="distance" value="{{ old('distance', $editingStore->distance ?? '1.5 كم') }}" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border);" placeholder="مثال: 1.5 كم">
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">وقت التوصيل التقديري</label>
                    <input type="text" name="delivery_time" value="{{ old('delivery_time', $editingStore->delivery_time ?? '30-40 دقيقة') }}" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border);" placeholder="مثال: 30-40 دقيقة">
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">التقييم (من 5)</label>
                    <input type="number" step="0.1" min="1" max="5" name="rating" value="{{ old('rating', $editingStore->rating ?? 4.8) }}" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border);">
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">شعار المتجر (دائري)</label>
                    <input type="file" name="logo" accept="image/*" style="padding: 8px; border-radius: 10px; border: 1px solid var(--border); background: white;">
                    @if($editingStore && $editingStore->logo)
                        <img src="{{ str_starts_with($editingStore->logo, 'http') ? $editingStore->logo : route('media.file', ['path' => $editingStore->logo]) }}" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover; margin-top: 6px; border: 2px solid var(--primary);">
                    @endif
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">صورة الغلاف / البانر</label>
                    <input type="file" name="banner" accept="image/*" style="padding: 8px; border-radius: 10px; border: 1px solid var(--border); background: white;">
                    @if($editingStore && $editingStore->banner)
                        <img src="{{ str_starts_with($editingStore->banner, 'http') ? $editingStore->banner : route('media.file', ['path' => $editingStore->banner]) }}" style="width: 90px; height: 45px; border-radius: 8px; object-fit: cover; margin-top: 6px; border: 1px solid var(--border);">
                    @endif
                </div>
            </div>

            <!-- خيارات الحالة -->
            <div style="display: flex; gap: 25px; margin-top: 18px; padding-top: 15px; border-top: 1px dashed var(--border); flex-wrap: wrap;">
                <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 700; font-size: 13px;">
                    <input type="checkbox" name="is_open" value="1" {{ old('is_open', $editingStore ? $editingStore->is_open : true) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary);">
                    <span>المتجر مفتوح ومستعد لاستقبال الطلبات</span>
                </label>

                <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 700; font-size: 13px;">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $editingStore ? $editingStore->is_featured : false) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #f39c12;">
                    <span>إبراز المتجر في قائمة (الأكثر طلباً / الشائع) بالصفحة الرئيسية</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px;">
                <a href="{{ route('admin.stores') }}" class="btn btn-outline">إلغاء</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-check"></i>
                    <span>{{ $editingStore ? 'تحديث بيانات المتجر' : 'حفظ المتجر ومتابعة المنيو' }}</span>
                </button>
            </div>
        </form>
    </div>

    <!-- فلترة وبحث المتاجر -->
    <div class="filter-card">
        <form action="{{ route('admin.stores') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; align-items: end;">
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                    <i class="fa-solid fa-magnifying-glass" style="margin-left: 4px;"></i>
                    بحث بالاسم أو العنوان
                </label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="ابحث باسم المتجر..." style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); width: 100%;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                    <i class="fa-solid fa-shapes" style="margin-left: 4px;"></i>
                    التصنيف
                </label>
                <select name="category_id" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); background: white;">
                    <option value="">جميع التصنيفات</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                    <i class="fa-solid fa-toggle-on" style="margin-left: 4px;"></i>
                    حالة المتجر
                </label>
                <select name="status" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); background: white;">
                    <option value="">الكل</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>مفتوح فقط</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>مغلق فقط</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i class="fa-solid fa-filter"></i>
                    <span>تصفية</span>
                </button>
                <a href="{{ route('admin.stores') }}" class="btn btn-outline" title="إعادة تعيين">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- جدول المتاجر -->
    <div class="panel">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>المتجر</th>
                        <th>التصنيف</th>
                        <th>العنوان / المسافة</th>
                        <th style="text-align: center;">التقييم</th>
                        <th style="text-align: center;">الأقسام والوجبات</th>
                        <th style="text-align: center;">الحالة</th>
                        <th style="text-align: center;">مميز (شائع)</th>
                        <th style="text-align: center;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stores as $store)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    @if($store->logo)
                                        <img src="{{ str_starts_with($store->logo, 'http') ? $store->logo : route('media.file', ['path' => $store->logo]) }}" alt="{{ $store->name }}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-soft);">
                                    @else
                                        <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px;">
                                            <i class="fa-solid fa-store"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight: 700; font-size: 14px; color: var(--text-main);">
                                            <a href="{{ route('admin.stores.show', $store) }}" style="color: inherit; text-decoration: none;">
                                                {{ $store->name }}
                                            </a>
                                        </div>
                                        <div style="font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                                            <i class="fa-regular fa-clock" style="font-size: 11px;"></i>
                                            <span>{{ $store->delivery_time ?? '30 دقيقة' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(6, 132, 148, 0.08); color: var(--primary); padding: 5px 12px; border-radius: 99px; font-size: 12px; font-weight: 700;">
                                    {{ $store->category->name ?? 'غير محدد' }}
                                </span>
                            </td>
                            <td>
                                <div style="font-size: 13px;">{{ $store->address ?: 'صنعاء' }}</div>
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                    <i class="fa-solid fa-location-dot" style="color: var(--primary);"></i>
                                    <span>{{ $store->distance ?? '1.5 كم' }}</span>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; align-items: center; gap: 4px; background: #fff8e1; color: #b78103; padding: 4px 10px; border-radius: 8px; font-weight: 800; font-size: 12px;">
                                    <i class="fa-solid fa-star" style="color: #f1c40f; font-size: 11px;"></i>
                                    <span>{{ number_format($store->rating, 1) }}</span>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('admin.stores.show', $store) }}" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; background: #f0f7f8; border-radius: 10px; color: var(--primary); font-weight: 700; font-size: 12px;">
                                    <i class="fa-solid fa-layer-group"></i>
                                    <span>{{ $store->menu_sections_count }} أقسام ({{ $store->products_count }} وجبة)</span>
                                </a>
                            </td>
                            <td style="text-align: center;">
                                <form action="{{ route('admin.stores.toggle-open', $store) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" style="background: none; border: none; cursor: pointer;" title="انقر لتغيير الحالة">
                                        @if($store->is_open)
                                            <span class="status-badge status-success">
                                                <i class="fa-solid fa-circle-check"></i>
                                                <span>مفتوح</span>
                                            </span>
                                        @else
                                            <span class="status-badge status-danger">
                                                <i class="fa-solid fa-circle-xmark"></i>
                                                <span>مغلق</span>
                                            </span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td style="text-align: center;">
                                <form action="{{ route('admin.stores.toggle-featured', $store) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" style="background: none; border: none; cursor: pointer;" title="انقر لتغيير التمييز">
                                        @if($store->is_featured)
                                            <span style="color: #f39c12; font-size: 16px;"><i class="fa-solid fa-star"></i></span>
                                        @else
                                            <span style="color: #ccc; font-size: 16px;"><i class="fa-regular fa-star"></i></span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('admin.stores.show', $store) }}" class="btn btn-outline" style="padding: 6px 10px; font-size: 12px;" title="إدارة المنيو والوجبات">
                                        <i class="fa-solid fa-utensils" style="color: var(--primary);"></i>
                                    </a>
                                    <a href="{{ route('admin.stores', ['edit' => $store->id]) }}" class="btn btn-outline" style="padding: 6px 10px; font-size: 12px;" title="تعديل المتجر">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.stores.delete', $store) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف المتجر؟ سيتم حذف جميع أقسامه ووجباته التابعة له.');" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 10px; font-size: 12px;" title="حذف المتجر">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="fa-solid fa-store-slash" style="font-size: 38px; color: #cbd5e1; margin-bottom: 12px; display: block;"></i>
                                <span style="font-size: 14px; font-weight: 700;">لا توجد متاجر مضافة حالياً</span>
                                <div style="margin-top: 10px;">
                                    <button class="btn btn-primary" onclick="document.getElementById('store-form-panel').style.display='block'; window.scrollTo({top: 0, behavior: 'smooth'});">
                                        <i class="fa-solid fa-plus"></i>
                                        <span>إضافة أول متجر الآن</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($stores->hasPages())
            <div class="pagination-container">
                <div class="info-text">
                    عرض {{ $stores->firstItem() }} إلى {{ $stores->lastItem() }} من إجمالي {{ $stores->total() }} متجر
                </div>
                <div>
                    {{ $stores->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
