@extends('admin.layout')

@section('title', 'إدارة المنتجات | تسويق')
@section('page_title', 'المخزون والمنتجات')

@section('content')
    @php
        $editingProduct = request()->has('edit') ? \App\Models\Product::find(request('edit')) : null;
    @endphp

    <!-- إحصائيات سريعة للمنتجات -->
    <div class="stat-grid" style="margin-bottom: 22px;">
        <div class="stat-card">
            <div class="info">
                <span class="title">إجمالي المنتجات</span>
                <span class="value">{{ $stats['total'] ?? $products->total() }}</span>
            </div>
            <div class="icon-box">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="info">
                <span class="title">متوفر بالمخزون</span>
                <span class="value" style="color: var(--success);">{{ $stats['in_stock'] ?? 0 }}</span>
            </div>
            <div class="icon-box" style="background: rgba(39, 174, 96, 0.12); color: #168447;">
                <i class="fa-solid fa-check-double"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="info">
                <span class="title">نفذت الكمية</span>
                <span class="value" style="color: var(--danger);">{{ $stats['out_of_stock'] ?? 0 }}</span>
            </div>
            <div class="icon-box" style="background: rgba(255, 77, 77, 0.12); color: #c93636;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>
    </div>

    <!-- شريط الإجراءات والفلترة -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 15px; flex-wrap: wrap;">
        <button class="btn btn-primary" onclick="document.getElementById('product-form-panel').style.display='block'; window.scrollTo({top: 0, behavior: 'smooth'});">
            <i class="fa-solid fa-plus"></i>
            <span>إضافة منتج جديد</span>
        </button>
    </div>

    <!-- بطاقة الفلترة المتقدمة للمنتجات -->
    <div class="filter-card">
        <form action="{{ route('admin.products') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; align-items: end;">
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                    <i class="fa-solid fa-magnifying-glass" style="margin-left: 4px;"></i>
                    البحث
                </label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="ابحث باسم المنتج أو الوصف..." style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); width: 100%;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                    <i class="fa-solid fa-tag" style="margin-left: 4px;"></i>
                    التصنيف
                </label>
                <select name="category_id" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); width: 100%; background: white;">
                    <option value="">-- كل التصنيفات --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                    <i class="fa-solid fa-warehouse" style="margin-left: 4px;"></i>
                    حالة المخزون
                </label>
                <select name="stock_status" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); width: 100%; background: white;">
                    <option value="">-- الكل --</option>
                    <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>متوفر بالمخزون</option>
                    <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>مخزون منخفض (تنبيه)</option>
                    <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>نفذت الكمية (0)</option>
                </select>
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                    <i class="fa-solid fa-arrow-down-wide-short" style="margin-left: 4px;"></i>
                    الترتيب
                </label>
                <select name="sort" style="padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); width: 100%; background: white;">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>الأحدث أولاً</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>الأقل سعراً</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>الأعلى سعراً</option>
                    <option value="stock_desc" {{ request('sort') == 'stock_desc' ? 'selected' : '' }}>الأكثر كمية</option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>الاسم (أ - ي)</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; height: 42px;">
                    <i class="fa-solid fa-filter"></i>
                    <span>تصفية</span>
                </button>
                @if(request()->anyFilled(['q', 'category_id', 'stock_status', 'sort']))
                    <a href="{{ route('admin.products') }}" class="btn btn-outline" style="height: 42px;" title="إعادة ضبط">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if($selectedCategory)
        <div class="panel" style="display: flex; justify-content: space-between; align-items: center; gap: 15px; margin-bottom: 25px; border-right: 4px solid var(--primary);">
            <div>
                <span style="color: var(--text-muted); font-size: 13px;">تصفح حسب التصنيف:</span>
                <h3 style="margin: 4px 0 0; color: var(--primary); font-size: 18px;">{{ $selectedCategory->name }}</h3>
            </div>
            <a href="{{ route('admin.products') }}" class="btn btn-outline" style="font-size: 12px;">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span>عرض كل المنتجات</span>
            </a>
        </div>
    @endif

    <!-- نموذج الإضافة والتعديل -->
    <div class="panel" id="product-form-panel" style="{{ $editingProduct ? 'display: block;' : 'display: none;' }} margin-bottom: 30px; border: 2px solid var(--primary-soft);">
        <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--primary); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid {{ $editingProduct ? 'fa-pen-to-square' : 'fa-box-open' }}"></i>
            <span>{{ $editingProduct ? 'تعديل بيانات المنتج' : 'إضافة منتج جديد للمخزون' }}</span>
        </h3>
        
        <form action="{{ $editingProduct ? route('admin.products.update', $editingProduct) : route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($selectedCategory)
                <input type="hidden" name="return_category_id" value="{{ $selectedCategory->id }}">
            @endif
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px;">
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">اسم المنتج <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $editingProduct->name ?? '') }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" placeholder="مثال: سماعة بلوتوث" required>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">السعر <span style="color: var(--danger);">*</span></label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $editingProduct->price ?? '') }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" placeholder="0.00" required>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">العملة</label>
                    <select name="currency" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" required>
                        @php $selectedCurrency = old('currency', $editingProduct->currency ?? 'YER'); @endphp
                        <option value="YER" {{ $selectedCurrency == 'YER' ? 'selected' : '' }}>ريال يمني (YER)</option>
                        <option value="SAR" {{ $selectedCurrency == 'SAR' ? 'selected' : '' }}>ريال سعودي (SAR)</option>
                        <option value="USD" {{ $selectedCurrency == 'USD' ? 'selected' : '' }}>دولار أمريكي (USD)</option>
                    </select>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">التصنيف <span style="color: var(--danger);">*</span></label>
                    <select name="category_id" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" required>
                        <option value="">-- اختر التصنيف --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (old('category_id', $editingProduct->category_id ?? '') == $category->id) ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">الكمية في المخزون</label>
                    <input type="number" name="stock" value="{{ old('stock', $editingProduct->stock ?? 0) }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" required>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">حد تنبيه المخزون المنخفض</label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $editingProduct->low_stock_threshold ?? 5) }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" placeholder="5">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">صورة المنتج</label>
                    <input type="file" name="image" accept="image/*" style="padding: 9px; border-radius: 12px; border: 1px solid var(--border); background: white;">
                </div>
            </div>
            <div style="margin-top: 18px;">
                <label style="font-weight: 700; font-size: 13px;">تفاصيل ووصف المنتج</label>
                <textarea name="details" rows="3" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); margin-top: 6px;" placeholder="اكتب وصفاً جذاباً للمنتج...">{{ old('details', $editingProduct->details ?? '') }}</textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <a href="{{ route('admin.products', $selectedCategory ? ['category_id' => $selectedCategory->id] : []) }}" class="btn btn-outline">إلغاء</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-check"></i>
                    <span>حفظ المنتج</span>
                </button>
            </div>
        </form>
    </div>

    <!-- عرض المنتجات بنظام بطاقات احترافي -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;" id="productsContainer">
        @forelse($products as $product)
            <div class="panel product-card-item" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                <div style="width: 100%; height: 170px; background: #F8FBFB; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: center; position: relative;">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: contain; padding: 10px;">
                    @else
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; color: #CBD5E1;">
                            <i class="fa-solid fa-image" style="font-size: 36px; margin-bottom: 6px;"></i>
                            <span style="font-size: 11px;">بدون صورة</span>
                        </div>
                    @endif
                    <div style="position: absolute; top: 12px; left: 12px;">
                        @if($product->stock <= 0)
                            <span class="status-badge status-danger" style="font-size: 11px;">
                                <i class="fa-solid fa-xmark" style="font-size: 10px;"></i>
                                <span>نفذت الكمية</span>
                            </span>
                        @elseif($product->is_low_stock)
                            <span class="status-badge status-pending" style="font-size: 11px;">
                                <i class="fa-solid fa-triangle-exclamation" style="font-size: 10px;"></i>
                                <span>منخفض (باقي {{ $product->stock }})</span>
                            </span>
                        @else
                            <span class="status-badge status-success" style="font-size: 11px;">
                                <i class="fa-solid fa-check" style="font-size: 10px;"></i>
                                <span>{{ $product->stock }} متوفر</span>
                            </span>
                        @endif
                    </div>
                </div>
                <div style="padding: 16px; flex: 1; display: flex; flex-direction: column;">
                    <div style="margin-bottom: 8px;">
                        @if($product->category)
                            <a href="{{ route('admin.products', ['category_id' => $product->category->id]) }}" style="font-size: 11px; background: var(--primary-soft); color: var(--primary); padding: 4px 10px; border-radius: 6px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fa-solid fa-tag" style="font-size: 10px;"></i>
                                <span class="product-category-text">{{ $product->category->name }}</span>
                            </a>
                        @else
                            <span style="font-size: 11px; background: #EDF2F7; color: var(--text-muted); padding: 4px 10px; border-radius: 6px; font-weight: 700;">عام</span>
                        @endif
                    </div>
                    <h4 class="product-title-text" style="margin: 0 0 8px; font-size: 15px; font-weight: 800; line-height: 1.4;">{{ $product->name }}</h4>
                    <div style="font-weight: 800; color: var(--primary); font-size: 18px; margin-bottom: 16px; margin-top: auto;">
                        {{ number_format($product->price, 0) }} <small style="font-size: 11px; font-weight: normal; color: var(--text-muted);">ريال</small>
                    </div>
                    
                    <div style="display: flex; gap: 8px; border-top: 1px solid var(--border); padding-top: 14px;">
                        <a href="{{ route('admin.products', array_merge(request()->query(), ['edit' => $product->id])) }}" class="btn btn-outline" style="flex: 1; font-size: 12px; padding: 8px;">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>تعديل</span>
                        </a>
                        <form action="{{ route('admin.products.delete', $product) }}" method="POST" style="flex: 1;">
                            @csrf
                            @if($selectedCategory)
                                <input type="hidden" name="return_category_id" value="{{ $selectedCategory->id }}">
                            @endif
                            <button type="submit" class="btn btn-danger" style="width: 100%; font-size: 12px; padding: 8px;" onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">
                                <i class="fa-solid fa-trash-can"></i>
                                <span>حذف</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="panel" style="grid-column: 1 / -1; text-align: center; padding: 50px; color: var(--text-muted);">
                <i class="fa-solid fa-boxes-stacked" style="font-size: 38px; margin-bottom: 12px; color: #CCD2D6; display: block;"></i>
                لا توجد منتجات مطابقة لخيارات البحث أو الفلترة
            </div>
        @endforelse
    </div>

    <!-- الترقيم والتنقل بين الصفحات -->
    <div class="pagination-container">
        <div class="info-text">
            عرض <strong>{{ $products->firstItem() ?? 0 }}</strong> إلى <strong>{{ $products->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $products->total() }}</strong> منتج
        </div>
        <div>
            {{ $products->links() }}
        </div>
    </div>
@endsection
