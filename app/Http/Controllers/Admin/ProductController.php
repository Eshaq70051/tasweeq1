<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('created_at', 'desc')
                          ->paginate($request->integer('per_page', 20));

        return ProductResource::collection($products)->additional([
            'success' => true,
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();

        $product = new Product();
        $product->name = $validated['name'];
        $product->price = $validated['price'];
        $product->currency = $validated['currency'];
        $product->details = $validated['details'] ?? null;
        $product->category_id = $validated['category_id'];
        $product->stock = $validated['stock'];
        $product->low_stock_threshold = $validated['low_stock_threshold'] ?? 5;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->image = $path;
        }

        $product->save();

        return (new ProductResource($product))->additional([
            'success' => true,
            'message' => 'Product created successfully',
        ])->response()->setStatusCode(201);
    }

    public function storeFromWeb(StoreProductRequest $request)
    {
        $validated = $request->validated();

        $product = new Product();
        $product->name = $validated['name'];
        $product->price = $validated['price'];
        $product->currency = $validated['currency'];
        $product->details = $validated['details'] ?? null;
        $product->category_id = $validated['category_id'];
        $product->stock = $validated['stock'];
        $product->low_stock_threshold = $validated['low_stock_threshold'] ?? 5;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->image = $path;
        }

        $product->save();

        return redirect()->route('admin.products')->with('success', 'تم إضافة المنتج بنجاح');
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $validated = $request->validated();
        $product->fill($validated);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $product->image = $path;
        }

        $product->save();

        return (new ProductResource($product))->additional([
            'success' => true,
            'message' => 'Product updated successfully',
        ]);
    }

    public function updateFromWeb(UpdateProductRequest $request, Product $product)
    {
        $validated = $request->validated();

        $product->fill([
            'name' => $validated['name'] ?? $product->name,
            'price' => $validated['price'] ?? $product->price,
            'currency' => $validated['currency'] ?? $product->currency,
            'details' => $validated['details'] ?? $product->details,
            'category_id' => $validated['category_id'] ?? $product->category_id,
            'stock' => $validated['stock'] ?? $product->stock,
            'low_stock_threshold' => $validated['low_stock_threshold'] ?? $product->low_stock_threshold,
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->save();

        return redirect()->route('admin.products', $this->productFilter($request))
            ->with('success', 'تم تعديل المنتج بنجاح');
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }

    public function deleteFromWeb(Request $request, Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products', $this->productFilter($request))
            ->with('success', 'تم حذف المنتج بنجاح');
    }

    private function productFilter(Request $request): array
    {
        return $request->filled('return_category_id')
            ? ['category_id' => $request->integer('return_category_id')]
            : [];
    }
}
