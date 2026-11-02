<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreMenuSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        $query = Store::with(['category'])->withCount(['menuSections', 'products']);

        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('address', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_open', (bool)$request->status);
        }

        if ($request->has('featured') && $request->featured !== '') {
            $query->where('is_featured', (bool)$request->featured);
        }

        $stores = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => Store::count(),
            'open' => Store::where('is_open', true)->count(),
            'featured' => Store::where('is_featured', true)->count(),
            'total_meals' => Product::whereNotNull('store_id')->count(),
        ];

        return view('admin.stores', compact('stores', 'categories', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'distance' => 'nullable|string|max:50',
            'delivery_time' => 'nullable|string|max:50',
            'rating' => 'nullable|numeric|between:1,5',
            'is_open' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:4096',
        ]);

        $data = [
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'address' => $validated['address'] ?? '',
            'phone' => $validated['phone'] ?? '',
            'distance' => $validated['distance'] ?? '1.5 كم',
            'delivery_time' => $validated['delivery_time'] ?? '30-40 دقيقة',
            'rating' => $validated['rating'] ?? 4.8,
            'is_open' => $request->has('is_open'),
            'is_featured' => $request->has('is_featured'),
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('stores/logos', 'public');
        }

        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('stores/banners', 'public');
        }

        $store = Store::create($data);

        return redirect()->route('admin.stores.show', $store)
            ->with('success', 'تم إضافة المتجر بنجاح! يمكنك الآن إضافة أقسام المنيو والوجبات.');
    }

    public function show(Store $store)
    {
        $store->load([
            'category',
            'menuSections' => function ($q) {
                $q->orderBy('sort_order')->with('products');
            },
            'products' => function ($q) {
                $q->latest();
            }
        ]);

        $categories = Category::orderBy('name')->get();

        return view('admin.store-detail', compact('store', 'categories'));
    }

    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'distance' => 'nullable|string|max:50',
            'delivery_time' => 'nullable|string|max:50',
            'rating' => 'nullable|numeric|between:1,5',
            'is_open' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:4096',
        ]);

        $data = [
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'address' => $validated['address'] ?? $store->address,
            'phone' => $validated['phone'] ?? $store->phone,
            'distance' => $validated['distance'] ?? $store->distance,
            'delivery_time' => $validated['delivery_time'] ?? $store->delivery_time,
            'rating' => $validated['rating'] ?? $store->rating,
            'is_open' => $request->has('is_open'),
            'is_featured' => $request->has('is_featured'),
        ];

        if ($request->hasFile('logo')) {
            if ($store->logo) {
                Storage::disk('public')->delete($store->logo);
            }
            $data['logo'] = $request->file('logo')->store('stores/logos', 'public');
        }

        if ($request->hasFile('banner')) {
            if ($store->banner) {
                Storage::disk('public')->delete($store->banner);
            }
            $data['banner'] = $request->file('banner')->store('stores/banners', 'public');
        }

        $store->update($data);

        return back()->with('success', 'تم تحديث بيانات المتجر بنجاح');
    }

    public function destroy(Store $store)
    {
        if ($store->logo) {
            Storage::disk('public')->delete($store->logo);
        }
        if ($store->banner) {
            Storage::disk('public')->delete($store->banner);
        }

        // Delete associated products
        foreach ($store->products as $product) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->delete();
        }

        // Delete menu sections
        $store->menuSections()->delete();

        $store->delete();

        return redirect()->route('admin.stores')->with('success', 'تم حذف المتجر وجميع أقسامه ووجباته بنجاح');
    }

    public function toggleOpen(Store $store)
    {
        $store->update(['is_open' => !$store->is_open]);
        $status = $store->is_open ? 'مفتوح للطلبات' : 'مغلق حالياً';
        return back()->with('success', "تم تحديث حالة المتجر إلى: {$status}");
    }

    public function toggleFeatured(Store $store)
    {
        $store->update(['is_featured' => !$store->is_featured]);
        $status = $store->is_featured ? 'ضمن المتاجر الشائعة' : 'إزالة من الشائعة';
        return back()->with('success', "تم تعديل ظهور المتجر: {$status}");
    }

    // --- إدارة أقسام المنيو ---

    public function storeSection(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $store->menuSections()->create([
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'] ?? ($store->menuSections()->max('sort_order') + 1),
            'is_active' => true,
        ]);

        return back()->with('success', 'تم إضافة قسم المنيو بنجاح');
    }

    public function updateSection(Request $request, StoreMenuSection $section)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $section->update([
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'] ?? $section->sort_order,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'تم تحديث قسم المنيو بنجاح');
    }

    public function deleteSection(StoreMenuSection $section)
    {
        // Unlink or delete products
        Product::where('store_menu_section_id', $section->id)->update(['store_menu_section_id' => null]);
        $section->delete();

        return back()->with('success', 'تم حذف قسم المنيو بنجاح');
    }

    // --- إدارة وجبات ومنتجات المتجر ---

    public function storeProduct(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'details' => 'nullable|string',
            'store_menu_section_id' => 'nullable|exists:store_menu_sections,id',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = [
            'name' => $validated['name'],
            'price' => $validated['price'],
            'old_price' => $validated['old_price'] ?? null,
            'currency' => $validated['currency'] ?? 'ريال',
            'details' => $validated['details'] ?? null,
            'store_id' => $store->id,
            'store_menu_section_id' => $validated['store_menu_section_id'] ?? null,
            'category_id' => $store->category_id,
            'stock' => $validated['stock'] ?? 100,
            'is_available' => $request->has('is_available'),
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return back()->with('success', 'تم إضافة الوجبة/المنتج بنجاح إلى المتجر');
    }

    public function updateProduct(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'details' => 'nullable|string',
            'store_menu_section_id' => 'nullable|exists:store_menu_sections,id',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = [
            'name' => $validated['name'],
            'price' => $validated['price'],
            'old_price' => $validated['old_price'] ?? null,
            'currency' => $validated['currency'] ?? $product->currency,
            'details' => $validated['details'] ?? null,
            'store_menu_section_id' => $validated['store_menu_section_id'] ?? $product->store_menu_section_id,
            'stock' => $validated['stock'] ?? $product->stock,
            'is_available' => $request->has('is_available'),
        ];

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return back()->with('success', 'تم تحديث بيانات الوجبة بنجاح');
    }

    public function deleteProduct(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return back()->with('success', 'تم حذف الوجبة بنجاح');
    }
}
