<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * API: Get all settings
     */
    public function index()
    {
        $settings = SiteSetting::all()->pluck('value', 'key');
        
        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * API: Update settings
     */
    public function update(Request $request)
    {
        $this->saveSettings($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully'
        ]);
    }

    /**
     * Web: Update settings
     */
    public function updateFromWeb(Request $request)
    {
        $data = $request->validate([
            'site_name' => 'nullable|string|max:100',
            'site_tagline' => 'nullable|string|max:180',
            'contact_phone' => 'nullable|string|max:40',
            'contact_whatsapp' => 'nullable|string|max:40',
            'contact_telegram' => 'nullable|string|max:100',
            'contact_email' => 'nullable|email|max:150',
            'discount_enabled' => 'nullable|boolean',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_text' => 'nullable|string|max:180',
            'primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'background_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'notification_title' => 'nullable|string|max:120',
            'notification_text' => 'nullable|string|max:300',
            'logo' => 'nullable|image|max:2048',
            'app_icon' => 'nullable|image|max:2048',
            'home_banner' => 'nullable|image|max:4096',
        ]);

        foreach (['logo', 'app_icon', 'home_banner'] as $imageKey) {
            if ($request->hasFile($imageKey)) {
                SiteSetting::set($imageKey, $this->replaceImageSetting($imageKey, $request->file($imageKey)));
            }
        }

        unset($data['logo'], $data['app_icon'], $data['home_banner']);
        $this->saveSettings($data);
        return redirect()->back()->with('success', 'تم حفظ الإعدادات بنجاح');
    }

    private function replaceImageSetting(string $key, $file): string
    {
        $oldPath = SiteSetting::get($key);
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return $file->store('settings', 'public');
    }

    /**
     * Helper to save settings
     */
    private function saveSettings(array $data)
    {
        foreach ($data as $key => $value) {
            if ($value !== null) {
                SiteSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
        }
    }

    /**
     * Web: Update notification image
     */
    public function updateNotificationImageFromWeb(Request $request)
    {
        $request->validate([
            'notification_image' => 'required|image|max:2048'
        ]);

        if ($request->hasFile('notification_image')) {
            // Delete old if exists
            $oldPath = SiteSetting::get('notification_image');
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

            $image = $request->file('notification_image');
            $imageName = 'notification_' . time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('settings', $imageName, 'public');
            
            SiteSetting::set('notification_image', $path);
        }

        return redirect()->back()->with('success', 'تم تحديث صورة الإشعارات بنجاح');
    }

    /**
     * API: Update notification image
     */
    public function updateNotificationImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_image' => 'required|image|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('notification_image')) {
            $image = $request->file('notification_image');
            $path = $image->storeAs('notifications', 'notif_' . time() . '.' . $image->getClientOriginalExtension(), 'public');
            SiteSetting::set('notification_image', $path);
        }

        return response()->json(['success' => true, 'message' => 'Notification image updated']);
    }
}
