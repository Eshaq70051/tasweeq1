@extends('admin.layout')

@section('title', 'إدارة السلايدر الإعلاني')
@section('page_title', 'صور العروض (Slider)')

@section('content')
    <div class="panel" style="margin-bottom: 30px;">
        <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--primary);">➕ إضافة عرض جديد</h3>
        <form action="{{ route('admin.sliders.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; align-items: end;">
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-weight: 700; font-size: 14px;">صورة العرض (1920x800)</label>
                    <input type="file" name="image" required style="padding: 10px; border: 1px solid var(--border); border-radius: 12px;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-weight: 700; font-size: 14px;">العنوان (اختياري)</label>
                    <input type="text" name="title" placeholder="مثال: خصومات الصيف" style="padding: 12px; border: 1px solid var(--border); border-radius: 12px;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-weight: 700; font-size: 14px;">رابط التوجيه (اختياري)</label>
                    <input type="text" name="link" placeholder="https://..." style="padding: 12px; border: 1px solid var(--border); border-radius: 12px;">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 20px;">حفظ ونشر العرض</button>
        </form>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; align-items: stretch;">
        @foreach($sliders as $slider)
            <div class="panel" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; min-height: 420px;">
                <div style="width: 100%; height: 220px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; overflow: hidden; border-bottom: 1px solid var(--border);">
                    <img src="{{ route('media.file', ['path' => $slider->image_path]) }}" alt="{{ $slider->title ?? 'slider image' }}" style="max-width: 100%; max-height: 100%; width: 100%; height: 100%; object-fit: contain; background: #f8f9fa; display: block;">
                </div>

                <div style="padding: 20px; display: flex; flex-direction: column; flex: 1;">
                    <h4 style="margin: 0 0 10px; min-height: 24px;">{{ $slider->title ?? 'بدون عنوان' }}</h4>
                    <p style="color: var(--text-muted); font-size: 12px; margin: 0 0 20px; word-break: break-all; min-height: 36px;">
                        🔗 {{ $slider->link ?? 'لا يوجد رابط' }}
                    </p>

                    <div style="display: flex; gap: 10px; margin-top: auto;">
                        <form action="{{ route('admin.sliders.toggle', $slider) }}" method="POST" style="flex: 1;">
                            @csrf
                            <button type="submit" class="btn" style="width: 100%; background: {{ $slider->is_active ? 'var(--primary-soft)' : '#eee' }}; color: {{ $slider->is_active ? 'var(--primary)' : '#666' }};">
                                {{ $slider->is_active ? '✅ نشط حالياً' : '🌑 معطل' }}
                            </button>
                        </form>
                        <form action="{{ route('admin.sliders.delete', $slider) }}" method="POST" style="flex: 0.5;">
                            @csrf
                            <button type="submit" class="btn" style="width: 100%; background: rgba(255, 77, 77, 0.05); color: var(--danger);" onclick="return confirm('حذف هذا العرض؟')">🗑️</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
