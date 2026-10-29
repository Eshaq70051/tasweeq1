@extends('admin.layout')

@section('title', 'إدارة المنتجات')
@section('page_title', 'المخزون والمنتجات')

@section('content')
    @php
        $editingProduct = request()->has('edit') ? \App\Models\Product::find(request('edit')) : null;
    @endphp

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; flex-wrap: wrap;">
        <button class="btn btn-primary" onclick="document.getElementById('product-form-panel').style.display='block'; window.scrollTo(0,0);">
            ➕ إضافة منتج جديد
        </button>
        <div style="flex: 1; min-width: 250px;">
            <input type="text" placeholder="بحث عن منتج..." style="width: 100%; padding: 12px 20px; border-radius: 15px; border: 1px solid var(--border); outline: none;">
        </div>
    </div>

    @if($selectedCategory)
        <div class="panel" style="display: flex; justify-content: space-between; align-items: center; gap: 15px; margin-bottom: 25px; border-right: 4px solid var(--primary);">
            <div>
                <span style="color: #777; font-size: 13px;">منتجات التصنيف</span>
                <h3 style="margin: 5px 0 0; color: var(--primary);">{{ $selectedCategory->name }}</h3>
            </div>
            <a href="{{ route('admin.products') }}" class="btn" style="background: #eee; color: #666;">عرض كل المنتجات</a>
        </div>
    @endif

    <!-- نموذج الإضافة المتجاوب -->
    <div class="panel" id="product-form-panel" style="{{ $editingProduct ? 'display: block;' : 'display: none;' }} margin-bottom: 30px; border: 2px solid var(--primary-soft);">
        <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--primary);">{{ $editingProduct ? '📝 تعديل المنتج' : '📦 إضافة منتج جديد' }}</h3>
        
        <form action="{{ $editingProduct ? route('admin.products.update', $editingProduct) : route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($selectedCategory)
                <input type="hidden" name="return_category_id" value="{{ $selectedCategory->id }}">
            @endif
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-weight: 700; font-size: 13px;">اسم المنتج</label>
                    <input type="text" name="name" value="{{ old('name', $editingProduct->name ?? '') }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" required>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-weight: 700; font-size: 13px;">السعر (ريال)</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $editingProduct->price ?? '') }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" required>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-weight: 700; font-size: 13px;">العملة</label>
                    <select name="currency" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" required>
                        @php $selectedCurrency = old('currency', $editingProduct->currency ?? 'YER'); @endphp
                        <option value="YER" {{ $selectedCurrency == 'YER' ? 'selected' : '' }}>ريال</option>
                    </select>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-weight: 700; font-size: 13px;">التصنيف</label>
                    <select name="category_id" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (old('category_id', $editingProduct->category_id ?? '') == $category->id) ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-weight: 700; font-size: 13px;">المخزون</label>
                    <input type="number" name="stock" value="{{ old('stock', $editingProduct->stock ?? 0) }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" required>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-weight: 700; font-size: 13px;">صورة المنتج</label>
                    <input type="file" name="image" style="padding: 8px;">
                </div>
            </div>
            <div style="margin-top: 20px;">
                <label style="font-weight: 700; font-size: 13px;">وصف المنتج</label>
                <textarea name="details" rows="3" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); margin-top: 8px;">{{ old('details', $editingProduct->details ?? '') }}</textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <a href="{{ route('admin.products', $selectedCategory ? ['category_id' => $selectedCategory->id] : []) }}" class="btn" style="background: #eee;">إلغاء</a>
                <button type="submit" class="btn btn-primary">حفظ المنتج</button>
            </div>
        </form>
    </div>

    <!-- عرض المنتجات بنظام بطاقات متجاوب -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
        @foreach($products as $product)
            <div class="panel" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                <div style="width: 100%; height: 160px; background: #f8f9fa;">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: contain; background: #f8f9fa;">
                    @else
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #ddd; font-size: 40px;">📦</div>
                    @endif
                </div>
                <div style="padding: 15px; flex: 1;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        @if($product->category)
                            <a href="{{ route('admin.products', ['category_id' => $product->category->id]) }}" style="font-size: 11px; background: var(--primary-soft); color: var(--primary); padding: 3px 8px; border-radius: 5px; font-weight: bold; text-decoration: none;">
                                {{ $product->category->name }}
                            </a>
                        @else
                            <span style="font-size: 11px; background: var(--primary-soft); color: var(--primary); padding: 3px 8px; border-radius: 5px; font-weight: bold;">عام</span>
                        @endif
                        <span class="status-badge {{ $product->stock > 0 ? 'status-success' : 'status-danger' }}" style="font-size: 10px;">{{ $product->stock }} متوفر</span>
                    </div>
                    <h4 style="margin: 0 0 5px; font-size: 16px;">{{ $product->name }}</h4>
                    <div style="font-weight: 800; color: var(--primary); font-size: 18px; margin-bottom: 15px;">{{ number_format($product->price, 0) }} ريال</div>
                    
                    <div style="display: flex; gap: 10px; border-top: 1px solid var(--border); padding-top: 15px;">
                        <a href="{{ route('admin.products', ['edit' => $product->id, 'category_id' => request('category_id', $product->category_id)]) }}" class="btn" style="flex: 1; font-size: 12px; background: var(--primary-soft); color: var(--primary); justify-content: center;">تعديل</a>
                        <form action="{{ route('admin.products.delete', $product) }}" method="POST" style="flex: 1;">
                            @csrf
                            @if($selectedCategory)
                                <input type="hidden" name="return_category_id" value="{{ $selectedCategory->id }}">
                            @endif
                            <button type="submit" class="btn" style="width: 100%; font-size: 12px; background: rgba(255, 77, 77, 0.05); color: var(--danger); justify-content: center;" onclick="return confirm('حذف المنتج؟')">حذف</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
