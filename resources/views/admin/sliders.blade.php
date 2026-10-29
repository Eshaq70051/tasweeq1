@extends('admin.layout')

@section('title', 'إدارة العروض الترويجية | تسويق')
@section('page_title', 'العروض والبنرات الإعلانية (Sliders)')

@section('content')
    <div class="panel" style="margin-bottom: 30px; border: 2px solid var(--primary-soft);">
        <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--primary); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-file-circle-plus"></i>
            <span>إضافة بنر / شريحة إعلانية جديدة</span>
        </h3>
        <form action="{{ route('admin.sliders.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; align-items: end;">
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">صورة العرض <span style="color: var(--danger);">*</span></label>
                    <input type="file" name="image" accept="image/*" required style="padding: 9px; border: 1px solid var(--border); border-radius: 12px; background: white;">
                    <small style="color: var(--text-muted); font-size: 11px;">المقاس الموصى به: 1920x800 بكسل</small>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">عنوان العرض (اختياري)</label>
                    <input type="text" name="title" placeholder="مثال: خصومات كبرى على الإلكترونيات" style="padding: 12px; border: 1px solid var(--border); border-radius: 12px;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 700; font-size: 13px;">رابط التوجيه (اختياري)</label>
                    <input type="text" name="link" placeholder="https://example.com/offers" style="padding: 12px; border: 1px solid var(--border); border-radius: 12px; direction: ltr; text-align: right;">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>حفظ ونشر العرض</span>
                </button>
            </div>
        </form>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; align-items: stretch;">
        @forelse($sliders as $slider)
            <div class="panel" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                <div style="width: 100%; height: 180px; background: #F8FBFB; display: flex; align-items: center; justify-content: center; overflow: hidden; border-bottom: 1px solid var(--border);">
                    <img src="{{ route('media.file', ['path' => $slider->image_path]) }}" alt="{{ $slider->title ?? 'slider image' }}" style="width: 100%; height: 100%; object-fit: cover;">
                </div>

                <div style="padding: 18px; display: flex; flex-direction: column; flex: 1;">
                    <h4 style="margin: 0 0 8px; font-size: 15px; font-weight: 800;">{{ $slider->title ?? 'بدون عنوان' }}</h4>
                    <p style="color: var(--text-muted); font-size: 12px; margin: 0 0 16px; word-break: break-all; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-link" style="color: var(--primary);"></i>
                        <span style="direction: ltr; text-align: right;">{{ $slider->link ?? 'لا يوجد رابط مخصص' }}</span>
                    </p>

                    <div style="display: flex; gap: 10px; margin-top: auto; border-top: 1px solid var(--border); padding-top: 14px;">
                        <form action="{{ route('admin.sliders.toggle', $slider) }}" method="POST" style="flex: 1;">
                            @csrf
                            <button type="submit" class="btn" style="width: 100%; font-size: 12px; background: {{ $slider->is_active ? 'var(--primary-soft)' : '#EDF2F7' }}; color: {{ $slider->is_active ? 'var(--primary)' : '#718096' }};">
                                <i class="fa-solid {{ $slider->is_active ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                                <span>{{ $slider->is_active ? 'نشط حالياً' : 'معطل مؤقتاً' }}</span>
                            </button>
                        </form>
                        <form action="{{ route('admin.sliders.delete', $slider) }}" method="POST" style="flex: 0.5;" onsubmit="return confirm('هل تريد حذف هذا البنر بالتأكيد؟')">
                            @csrf
                            <button type="submit" class="btn btn-danger" style="width: 100%; font-size: 12px;" title="حذف العرض">
                                <i class="fa-solid fa-trash-can"></i>
                                <span>حذف</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="panel" style="grid-column: 1 / -1; text-align: center; padding: 50px; color: var(--text-muted);">
                <i class="fa-solid fa-images" style="font-size: 38px; margin-bottom: 12px; color: #CCD2D6; display: block;"></i>
                لا توجد عروض أو بنرات ترويجية حالياً
            </div>
        @endforelse
    </div>
@endsection
