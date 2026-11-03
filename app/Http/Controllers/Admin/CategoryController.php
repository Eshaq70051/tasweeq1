<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->latest()->get();
        return view('admin.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:5120',
        ]);

        $data = $request->only(['name', 'description']);

        if ($request->hasFile('image')) {
            $cloudinary = new CloudinaryService();
            $data['image'] = $cloudinary->upload($request->file('image'), 'categories');
        }

        Category::create($data);

        return redirect()->route('admin.categories')->with('success', 'تم إضافة التصنيف بنجاح');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:5120',
        ]);

        $data = $request->only(['name', 'description']);

        if ($request->hasFile('image')) {
            $cloudinary = new CloudinaryService();
            $cloudinary->delete($category->image);
            $data['image'] = $cloudinary->upload($request->file('image'), 'categories');
        }

        $category->update($data);

        return redirect()->route('admin.categories')->with('success', 'تم تحديث التصنيف بنجاح');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return redirect()->route('admin.categories')->with('error', 'لا يمكن حذف تصنيف يحتوي على منتجات');
        }

        if ($category->image) {
            $cloudinary = new CloudinaryService();
            $cloudinary->delete($category->image);
        }

        $category->delete();

        return redirect()->route('admin.categories')->with('success', 'تم حذف التصنيف بنجاح');
    }
}
