@extends('admin.layout')

@section('title', 'إعدادات النظام')
@section('page_title', 'إعدادات المتجر والتواصل')

@section('content')
    <div style="display: flex; flex-direction: column; gap: 20px; padding-bottom: 50px;">
        
        <!-- قسم صورة إشعارات التطبيق -->
        <div class="panel">
            <h3 style="margin: 0 0 15px; color: var(--primary);">🔔 صورة إشعارات التطبيق</h3>
            <p style="color: var(--text-muted); font-size: 12px; margin-bottom: 20px;">هذه الصورة تظهر في تنبيهات الجوال للعملاء.</p>
            
            <form action="{{ route('admin.settings.notification_image') }}" method="POST" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                @csrf
                <div style="width: 100px; height: 100px; background: #f8f9fa; border-radius: 20px; border: 2px dashed var(--border); display: flex; align-items: center; justify-content: center; overflow: hidden;">
                    @if(isset($settings['notification_image']))
                        <img src="{{ route('media.file', ['path' => $settings['notification_image']]) }}" alt="notification image" style="width: 100%; height: 100%; object-fit: contain; background: #f8f9fa;">
                    @else
                        <span style="font-size: 30px;">🖼️</span>
                    @endif
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <input type="file" name="notification_image" style="margin-bottom: 15px; display: block; width: 100%;" required>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 25px; font-size: 13px;">تحديث الصورة</button>
                </div>
            </form>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="panel">
                <h3 style="margin: 0 0 20px; color: var(--primary);">🎨 هوية التطبيق والصور</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 20px;">
                    @foreach(['logo' => 'الشعار الرئيسي', 'app_icon' => 'أيقونة التطبيق', 'home_banner' => 'صورة واجهة التطبيق'] as $imageKey => $imageLabel)
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <label style="font-weight: bold; font-size: 13px;">{{ $imageLabel }}</label>
                            <div style="height: 120px; border: 1px dashed var(--border); border-radius: 16px; background: #f8fbfb; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                @if(!empty($settings[$imageKey]))
                                    <img src="{{ route('media.file', ['path' => $settings[$imageKey]]) }}" alt="{{ $imageLabel }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                @else
                                    <span style="color: var(--text-muted); font-size: 12px;">لا توجد صورة</span>
                                @endif
                            </div>
                            <input type="file" name="{{ $imageKey }}" accept="image/*" style="font-size: 12px;">
                        </div>
                    @endforeach
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 20px;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-weight: bold; font-size: 13px;">العبارة التعريفية</label>
                        <input type="text" name="site_tagline" value="{{ $settings['site_tagline'] ?? '' }}" placeholder="متجرك الإلكتروني المفضل" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                    </div>
                </div>
            </div>

            <div class="panel">
                <h3 style="margin: 0 0 20px; color: var(--primary);">🏷️ الخصومات والعروض العامة</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; align-items: end;">
                    <label style="display: flex; align-items: center; gap: 10px; font-weight: bold; font-size: 13px;">
                        <input type="hidden" name="discount_enabled" value="0">
                        <input type="checkbox" name="discount_enabled" value="1" {{ ($settings['discount_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                        تفعيل الخصم العام
                    </label>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-weight: bold; font-size: 13px;">نسبة الخصم %</label>
                        <input type="number" name="discount_percent" min="0" max="100" step="0.01" value="{{ $settings['discount_percent'] ?? '0' }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-weight: bold; font-size: 13px;">نص العرض</label>
                        <input type="text" name="discount_text" value="{{ $settings['discount_text'] ?? 'خصومات تصل إلى 50%' }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                    </div>
                </div>
            </div>

            <div class="panel">
                <h3 style="margin: 0 0 20px; color: var(--primary);">🖌️ ألوان التطبيق</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    @foreach(['primary_color' => 'اللون الرئيسي', 'secondary_color' => 'اللون الثانوي', 'background_color' => 'لون الخلفية'] as $colorKey => $colorLabel)
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <label style="font-weight: bold; font-size: 13px;">{{ $colorLabel }}</label>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <input type="color" value="{{ $settings[$colorKey] ?? '#068494' }}" oninput="this.nextElementSibling.value=this.value" style="width: 48px; height: 42px; padding: 2px; border: 1px solid var(--border); border-radius: 8px;">
                                <input type="text" name="{{ $colorKey }}" value="{{ $settings[$colorKey] ?? '#068494' }}" pattern="^#[0-9A-Fa-f]{6}$" required style="flex: 1; padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="panel">
                <h3 style="margin: 0 0 20px; color: var(--primary);">🔔 إعدادات الإشعارات</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-weight: bold; font-size: 13px;">عنوان الإشعار الافتراضي</label>
                        <input type="text" name="notification_title" value="{{ $settings['notification_title'] ?? '' }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-weight: bold; font-size: 13px;">نص الإشعار الافتراضي</label>
                        <input type="text" name="notification_text" value="{{ $settings['notification_text'] ?? '' }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                    </div>
                </div>
            </div>
            <!-- قنوات التواصل (واتساب، تليجرام، هاتف) -->
            <div class="panel">
                <h3 style="margin: 0 0 20px; color: var(--primary);">📱 قنوات التواصل (تظهر في التطبيق)</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-weight: bold; font-size: 13px;">رقم الجوال الرسمي</label>
                        <input type="text" name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}" placeholder="+966..." style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-weight: bold; font-size: 13px;">رقم الواتساب</label>
                        <input type="text" name="contact_whatsapp" value="{{ $settings['contact_whatsapp'] ?? '' }}" placeholder="966..." style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-weight: bold; font-size: 13px;">معرف التلجرام</label>
                        <input type="text" name="contact_telegram" value="{{ $settings['contact_telegram'] ?? '' }}" placeholder="@username" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                    </div>
                </div>
            </div>

            <!-- معلومات المتجر الأساسية -->
            <div class="panel" style="margin-top: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
                    <h3 style="margin:0; color: var(--primary);">🏢 معلومات المتجر والـ API</h3>
                    <button type="submit" class="btn btn-primary">حفظ كافة الإعدادات</button>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-weight: bold; font-size: 13px;">اسم المتجر</label>
                        <input type="text" name="site_name" value="{{ $settings['site_name'] ?? 'تسويق' }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-weight: bold; font-size: 13px;">البريد الإلكتروني للدعم</label>
                        <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
