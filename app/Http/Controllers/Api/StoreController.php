<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\StoreMenuSection;
use App\Models\Product;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * قائمة المتاجر والمطاعم مع التصفية والبحث
     */
    public function index(Request $request)
    {
        $query = Store::query();

        $categoryId = $request->route('id') ?? $request->category_id;
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        // خيارات التصفية: الافتراضي، الأقرب، الأعلى تقييماً، جديدنا
        $filter = $request->get('filter', 'default');
        switch ($filter) {
            case 'nearest':
                $query->orderBy('distance', 'asc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'new':
                $query->orderBy('id', 'desc');
                break;
            case 'default':
            default:
                $query->orderBy('is_featured', 'desc')->orderBy('id', 'desc');
                break;
        }

        $stores = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data' => $stores->items(),
            'total' => $stores->total(),
            'current_page' => $stores->currentPage(),
            'last_page' => $stores->lastPage(),
        ]);
    }

    /**
     * المتاجر الأكثر شعبية للشريط الدائري في الرئيسية
     */
    public function popular(Request $request)
    {
        $stores = Store::where('is_open', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('rating', 'desc')
            ->limit(16)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $stores,
        ]);
    }

    /**
     * تفاصيل المطعم مع أقسام المنيو والوجبات
     */
    public function show($id)
    {
        $store = Store::with([
            'category',
            'menuSections' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order')->with(['products' => function ($pq) {
                    $pq->where('is_available', true);
                }]);
            },
            'products' => function ($q) {
                $q->where('is_available', true);
            }
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $store,
        ]);
    }

    /**
     * إضافة مطعم/متجر جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'delivery_time' => 'nullable|string|max:50',
            'distance' => 'nullable|string|max:50',
            'rating' => 'nullable|numeric|between:1,5',
            'is_open' => 'nullable|boolean',
            'logo' => 'nullable|image|max:4096',
            'banner' => 'nullable|image|max:4096',
        ]);

        $cloudinary = new CloudinaryService();
        if ($request->hasFile('logo')) {
            $validated['logo'] = $cloudinary->upload($request->file('logo'), 'stores/logos');
        }

        if ($request->hasFile('banner')) {
            $validated['banner'] = $cloudinary->upload($request->file('banner'), 'stores/banners');
        }

        $store = Store::create($validated);

        // إنشاء أقسام منيو افتراضية لسهولة البدء
        $defaultSections = ['العروض', 'الوجبات الرئيسية', 'المشروبات'];
        foreach ($defaultSections as $index => $sectionName) {
            $store->menuSections()->create([
                'name' => $sectionName,
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تمت إضافة المطعم بنجاح',
            'data' => $store->load('menuSections'),
        ], 201);
    }

    /**
     * إضافة قسم جديد في منيو المطعم
     */
    public function addSection(Request $request, $id)
    {
        $store = Store::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $section = $store->menuSections()->create([
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'] ?? $store->menuSections()->count(),
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تمت إضافة قسم المنيو بنجاح',
            'data' => $section,
        ], 201);
    }

    /**
     * إضافة وجبة / منتج جديد تحت المطعم وقسم المنيو
     */
    public function addProduct(Request $request, $id)
    {
        $store = Store::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'details' => 'nullable|string',
            'store_menu_section_id' => 'nullable|exists:store_menu_sections,id',
            'stock' => 'nullable|integer|min:0',
            'is_available' => 'nullable|boolean',
            'image' => 'nullable|image|max:4096',
        ]);

        $cloudinary = new CloudinaryService();
        if ($request->hasFile('image')) {
            $validated['image'] = $cloudinary->upload($request->file('image'), 'products');
        }

        $validated['store_id'] = $store->id;
        $validated['category_id'] = $store->category_id;
        $validated['is_available'] = $request->boolean('is_available', true);

        $product = Product::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'تمت إضافة الوجبة / المنتج إلى منيو المطعم بنجاح',
            'data' => $product,
        ], 201);
    }
}
