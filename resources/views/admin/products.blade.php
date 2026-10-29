@extends('admin.layout')

@section('title', 'إدارة المنتجات | تسويق')
@section('page_title', 'المخزون والمنتجات')

@section('content')
    @php
        $editingProduct = request()->has('edit') ? \App\Models\Product::find(request('edit')) : null;
    @endphp

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; flex-wrap: wrap;">
        <button class="btn btn-primary" onclick="document.getElementById('product-form-panel').style.display='block'; window.scrollTo({top: 0, behavior: 'smooth'});">
            <i class="fa-solid fa-plus"></i>
            <span>إضافة منتج جديد</span>
        </button>
        <div style="position: relative; flex: 1; min-width: 260px;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
            <input type="text" id="productSearchInput" onkeyup="filterProducts()" placeholder="ابحث باسم المنتج أو التصنيف..." style="width: 100%; padding: 12px 42px 12px 16px; border-radius: 14px; border: 1px solid var(--border); outline: none;">
        </div>
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
                        <span class="status-badge {{ $product->stock > 0 ? 'status-success' : 'status-danger' }}" style="font-size: 11px;">
                            <i class="fa-solid {{ $product->stock > 0 ? 'fa-check' : 'fa-xmark' }}" style="font-size: 10px;"></i>
                            <span>{{ $product->stock > 0 ? $product->stock . ' متوفر' : 'نفذت الكمية' }}</span>
                        </span>
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
                        <a href="{{ route('admin.products', ['edit' => $product->id, 'category_id' => request('category_id', $product->category_id)]) }}" class="btn btn-outline" style="flex: 1; font-size: 12px; padding: 8px;">
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
                لا توجد منتجات مسجلة حالياً
            </div>
        @endforelse
    </div>

    <script>
        function filterProducts() {
            const input = document.getElementById('productSearchInput');
            const filter = input.value.toLowerCase();
            const cards = document.getElementsByClassName('product-card-item');

            for (let i = 0; i < cards.length; i++) {
                const title = cards[i].querySelector('.product-title-text');
                const cat = cards[i].querySelector('.product-category-text');
                const text = (title ? title.innerText : '') + ' ' + (cat ? cat.innerText : '');
                if (text.toLowerCase().indexOf(filter) > -1) {
                    cards[i].style.display = "flex";
                } else {
                    cards[i].style.display = "none";
                }
            }
        }
    </script>
@endsection
