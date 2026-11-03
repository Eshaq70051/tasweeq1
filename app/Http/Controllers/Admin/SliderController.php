<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketingSlider;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = MarketingSlider::latest()->get();
        return view('admin.sliders', compact('sliders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
            'title' => 'nullable|string|max:255',
            'link'  => 'nullable|string',
        ]);

        $cloudinary = new CloudinaryService();
        $path = $cloudinary->upload($request->file('image'), 'sliders');

        MarketingSlider::create([
            'title'      => $request->title,
            'image_path' => $path,
            'link'       => $request->link,
            'is_active'  => true,
        ]);

        return redirect()->back()->with('success', 'تم إضافة السلايدر بنجاح');
    }

    public function toggle(MarketingSlider $slider)
    {
        $slider->update(['is_active' => !$slider->is_active]);
        return redirect()->back()->with('success', 'تم تحديث حالة السلايدر');
    }

    public function destroy(MarketingSlider $slider)
    {
        $cloudinary = new CloudinaryService();
        $cloudinary->delete($slider->image_path);
        $slider->delete();
        return redirect()->back()->with('success', 'تم حذف السلايدر بنجاح');
    }
}
