<?php

namespace App\Services;

use App\Models\User;
use App\Models\DeviceToken;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FcmService
{
    protected ?string $serverKey;
    protected ?string $projectId;
    protected ?string $credentialsPath;

    public function __construct()
    {
        $this->serverKey = config('services.fcm.server_key') ?: env('FIREBASE_SERVER_KEY');
        $this->projectId = config('services.fcm.project_id') ?: env('FIREBASE_PROJECT_ID');
        $this->credentialsPath = config('services.fcm.credentials') ?: env('FIREBASE_CREDENTIALS');
    }

    /**
     * إرسال إشعار لمستخدم محدد وتخزينه في جدول الإشعارات
     */
    public function sendToUser(User $user, string $title, string $body, array $data = [], string $type = 'general'): ?UserNotification
    {
        // 1. تسجيل الإشعار في قاعدة البيانات ليظهر في سجل إشعارات التطبيق
        $notification = UserNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'data' => $data,
            'is_read' => false,
        ]);

        // 2. جمع كل رموز الأجهزة النشطة للمستخدم
        $tokens = DeviceToken::where('user_id', $user->id)->pluck('token')->toArray();
        if ($user->fcm_token && !in_array($user->fcm_token, $tokens)) {
            $tokens[] = $user->fcm_token;
        }

        if (empty($tokens)) {
            Log::info("[FCM] User #{$user->id} has no registered FCM device tokens.");
            return $notification;
        }

        // 3. إرسال الإشعار لجميع أجهزة المستخدم
        $this->sendToTokens($tokens, $title, $body, array_merge($data, [
            'notification_id' => (string) $notification->id,
            'type' => $type,
        ]));

        return $notification;
    }

    /**
     * إرسال إشعار لقائمة من الرموز (Tokens)
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        $tokens = array_unique(array_filter($tokens));
        $results = [];

        foreach ($tokens as $token) {
            $results[$token] = $this->sendToToken($token, $title, $body, $data);
        }

        return $results;
    }

    /**
     * إرسال إشعار لرمز جهاز واحد
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        // محاولة الإرسال عبر FCM HTTP v1 أولاً إذا توفر ملف الاعتماد Service Account
        if ($this->credentialsPath && file_exists(base_path($this->credentialsPath))) {
            return $this->sendViaHttpV1($token, $title, $body, $data);
        }

        // أو الإرسال عبر Server Key إذا كان مضبوطاً
        if ($this->serverKey) {
            return $this->sendViaLegacyKey($token, $title, $body, $data);
        }

        // إذا لم تكن مفاتيح Firebase مدخلة بعد في ملف .env
        Log::info("[FCM Simulation] Push Notification Dispatched to token (" . substr($token, 0, 15) . "...): {$title} - {$body}");
        return true;
    }

    /**
     * الإرسال عبر بروتوكول FCM HTTP v1 (الأحدث والأكثر أماناً من Google)
     */
    protected function sendViaHttpV1(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            $accessToken = $this->getGoogleAccessToken();
            if (!$accessToken || !$this->projectId) {
                Log::warning("[FCM v1] Missing access token or project ID.");
                return false;
            }

            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

            // تحويل قيم الـ data إلى strings وفق شروط FCM
            $stringData = [];
            foreach ($data as $k => $v) {
                $stringData[(string) $k] = is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE);
            }

            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $stringData,
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1,
                            ],
                        ],
                    ],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->timeout(10)
                ->post($url, $payload);

            if ($response->successful()) {
                return true;
            }

            Log::error("[FCM v1 Error] Response: " . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error("[FCM v1 Exception] " . $e->getMessage());
            return false;
        }
    }

    /**
     * الإرسال عبر Firebase Legacy Server Key
     */
    protected function sendViaLegacyKey(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            $url = 'https://fcm.googleapis.com/fcm/send';

            $payload = [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'badge' => '1',
                ],
                'data' => array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]),
                'priority' => 'high',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post($url, $payload);

            if ($response->successful()) {
                return true;
            }

            Log::error("[FCM Legacy Error] Response: " . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error("[FCM Legacy Exception] " . $e->getMessage());
            return false;
        }
    }

    /**
     * توليد Google OAuth2 Bearer Token من ملف Service Account بدون مكتبات خارجية
     */
    protected function getGoogleAccessToken(): ?string
    {
        return Cache::remember('firebase_fcm_access_token', 3300, function () {
            $filePath = base_path($this->credentialsPath);
            if (!file_exists($filePath)) {
                return null;
            }

            $serviceAccount = json_decode(file_get_contents($filePath), true);
            if (!$serviceAccount || !isset($serviceAccount['private_key'], $serviceAccount['client_email'])) {
                return null;
            }

            $now = time();
            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $claim = json_encode([
                'iss' => $serviceAccount['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now,
            ]);

            $b64Header = $this->base64UrlEncode($header);
            $b64Claim = $this->base64UrlEncode($claim);
            $dataToSign = $b64Header . '.' . $b64Claim;

            $privateKey = openssl_pkey_get_private($serviceAccount['private_key']);
            if (!$privateKey) {
                return null;
            }

            openssl_sign($dataToSign, $signature, $privateKey, 'SHA256');
            $jwt = $dataToSign . '.' . $this->base64UrlEncode($signature);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error("[FCM OAuth2 Error] " . $response->body());
            return null;
        });
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
