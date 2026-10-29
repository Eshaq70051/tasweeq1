@extends('admin.layout')

@section('title', 'إعدادات النظام | تسويق')
@section('page_title', 'إعدادات المتجر والتطبيق')

@section('content')
    <div style="display: flex; flex-direction: column; gap: 24px; padding-bottom: 50px;">
        
        <!-- قسم صورة إشعارات التطبيق -->
        <div class="panel">
            <h3 style="margin: 0 0 10px; color: var(--primary); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-bell"></i>
                <span>صورة إشعارات التطبيق (Push Notification Banner)</span>
            </h3>
            <p style="color: var(--text-muted); font-size: 12px; margin-bottom: 18px;">تظهر هذه الصورة في مركز التنبيهات على هواتف العملاء عند إرسال عروض عامة.</p>
            
            <form action="{{ route('admin.settings.notification_image') }}" method="POST" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                @csrf
                <div style="width: 100px; height: 100px; background: #F8FBFB; border-radius: 16px; border: 2px dashed var(--border); display: flex; align-items: center; justify-content: center; overflow: hidden;">
                    @if(isset($settings['notification_image']))
                        <img src="{{ route('media.file', ['path' => $settings['notification_image']]) }}" alt="notification image" style="width: 100%; height: 100%; object-fit: contain;">
                    @else
                        <i class="fa-solid fa-image" style="font-size: 28px; color: #CBD5E1;"></i>
                    @endif
                </div>
                <div style="flex: 1; min-width: 220px;">
                    <input type="file" name="notification_image" accept="image/*" style="margin-bottom: 14px; display: block; width: 100%;" required>
                    <button type="submit" class="btn btn-primary" style="padding: 9px 20px; font-size: 13px;">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>تحديث صورة الإشعار</span>
                    </button>
                </div>
            </form>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- الهوية البصرية -->
            <div class="panel">
                <h3 style="margin: 0 0 20px; color: var(--primary); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-palette"></i>
                    <span>الهوية البصرية وصور التطبيق</span>
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 20px;">
                    @foreach(['logo' => 'الشعار الرسمي (Logo)', 'app_icon' => 'أيقونة التطبيق (App Icon)', 'home_banner' => 'صورة الواجهة الرئيسية'] as $imageKey => $imageLabel)
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <label style="font-weight: 700; font-size: 13px;">{{ $imageLabel }}</label>
                            <div style="height: 120px; border: 1px dashed var(--border); border-radius: 14px; background: #F8FBFB; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 10px;">
                                @if(!empty($settings[$imageKey]))
                                    <img src="{{ route('media.file', ['path' => $settings[$imageKey]]) }}" alt="{{ $imageLabel }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                @else
                                    <span style="color: var(--text-muted); font-size: 12px; display: flex; align-items: center; gap: 6px;">
                                        <i class="fa-solid fa-image" style="color: #CBD5E1;"></i>
                                        لا توجد صورة
                                    </span>
                                @endif
                            </div>
                            <input type="file" name="{{ $imageKey }}" accept="image/*" style="font-size: 12px;">
                        </div>
                    @endforeach
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 20px;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 700; font-size: 13px;">العبارة التعريفية (Slogan)</label>
                        <input type="text" name="site_tagline" value="{{ $settings['site_tagline'] ?? '' }}" placeholder="متجرك الإلكتروني المفضل" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                    </div>
                </div>
            </div>

            <!-- الخصومات والعروض العامة -->
            <div class="panel">
                <h3 style="margin: 0 0 20px; color: var(--primary); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-tag"></i>
                    <span>الخصومات والعروض الترويجية العامة</span>
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; align-items: end;">
                    <label style="display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 13px; cursor: pointer;">
                        <input type="hidden" name="discount_enabled" value="0">
                        <input type="checkbox" name="discount_enabled" value="1" {{ ($settings['discount_enabled'] ?? '0') == '1' ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <span>تفعيل شريط الخصم العام</span>
                    </label>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 700; font-size: 13px;">نسبة الخصم %</label>
                        <input type="number" name="discount_percent" min="0" max="100" step="0.01" value="{{ $settings['discount_percent'] ?? '0' }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 700; font-size: 13px;">نص الإعلان والخصم</label>
                        <input type="text" name="discount_text" value="{{ $settings['discount_text'] ?? 'خصومات مميزة لفترة محدودة' }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                    </div>
                </div>
            </div>

            <!-- ألوان التطبيق -->
            <div class="panel">
                <h3 style="margin: 0 0 20px; color: var(--primary); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-brush"></i>
                    <span>نظام ألوان التطبيق (Color Palette)</span>
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    @foreach(['primary_color' => 'اللون الرئيسي', 'secondary_color' => 'اللون الثانوي', 'background_color' => 'لون الخلفية'] as $colorKey => $colorLabel)
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <label style="font-weight: 700; font-size: 13px;">{{ $colorLabel }}</label>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <input type="color" value="{{ $settings[$colorKey] ?? '#068494' }}" oninput="this.nextElementSibling.value=this.value" style="width: 48px; height: 44px; padding: 2px; border: 1px solid var(--border); border-radius: 10px; cursor: pointer;">
                                <input type="text" name="{{ $colorKey }}" value="{{ $settings[$colorKey] ?? '#068494' }}" pattern="^#[0-9A-Fa-f]{6}$" required style="flex: 1; padding: 12px; border-radius: 12px; border: 1px solid var(--border); font-family: monospace;">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- إعدادات الإشعارات الافتراضية -->
            <div class="panel">
                <h3 style="margin: 0 0 20px; color: var(--primary); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>قالب الإشعارات التلقائية</span>
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 700; font-size: 13px;">عنوان الإشعار الافتراضي</label>
                        <input type="text" name="notification_title" value="{{ $settings['notification_title'] ?? '' }}" placeholder="تحديث جديد من تسويق" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 700; font-size: 13px;">نص الإشعار الافتراضي</label>
                        <input type="text" name="notification_text" value="{{ $settings['notification_text'] ?? '' }}" placeholder="تصفح أحدث العروض والمنتجات الحصرية اليوم" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                    </div>
                </div>
            </div>

            <!-- قنوات التواصل والدعم -->
            <div class="panel">
                <h3 style="margin: 0 0 20px; color: var(--primary); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-headset"></i>
                    <span>قنوات الدعم والتواصل (تظهر في التطبيق)</span>
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-phone" style="color: var(--primary);"></i>
                            <span>رقم الاتصال المباشر</span>
                        </label>
                        <input type="text" name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}" placeholder="+967..." style="padding: 12px; border-radius: 12px; border: 1px solid var(--border); direction: ltr; text-align: right;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-brands fa-whatsapp" style="color: #25D366;"></i>
                            <span>رقم الواتساب الرسمي</span>
                        </label>
                        <input type="text" name="contact_whatsapp" value="{{ $settings['contact_whatsapp'] ?? '' }}" placeholder="967..." style="padding: 12px; border-radius: 12px; border: 1px solid var(--border); direction: ltr; text-align: right;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-brands fa-telegram" style="color: #229ED9;"></i>
                            <span>معرف التلغرام</span>
                        </label>
                        <input type="text" name="contact_telegram" value="{{ $settings['contact_telegram'] ?? '' }}" placeholder="@tasweeq_support" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border); direction: ltr; text-align: right;">
                    </div>
                </div>
            </div>

            <!-- معلومات المتجر الأساسية -->
            <div class="panel">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
                    <h3 style="margin:0; color: var(--primary); font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-store"></i>
                        <span>معلومات المتجر وبيانات الاتصال</span>
                    </h3>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
                        <i class="fa-solid fa-check"></i>
                        <span>حفظ كافة الإعدادات</span>
                    </button>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 700; font-size: 13px;">اسم المتجر / التطبيق</label>
                        <input type="text" name="site_name" value="{{ $settings['site_name'] ?? 'تسويق' }}" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);" required>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 700; font-size: 13px;">البريد الإلكتروني للدعم الفني</label>
                        <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}" placeholder="support@tasweeq.com" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
