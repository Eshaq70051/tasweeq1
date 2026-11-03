<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    private string $cloudName;
    private string $apiKey;
    private string $apiSecret;
    private string $uploadUrl;

    public function __construct()
    {
        $this->cloudName = config('cloudinary.cloud_name', env('CLOUDINARY_CLOUD_NAME', ''));
        $this->apiKey    = config('cloudinary.api_key',    env('CLOUDINARY_API_KEY', ''));
        $this->apiSecret = config('cloudinary.api_secret', env('CLOUDINARY_API_SECRET', ''));
        $this->uploadUrl = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";
    }

    /**
     * رفع ملف مرفوع من form إلى Cloudinary
     * يُعيد مسار نسبي مثل: products/abc123.jpg
     * أو URL كامل إذا طُلب ذلك
     */
    public function upload(UploadedFile $file, string $folder): string
    {
        // إذا لم تكن بيانات Cloudinary مضبوطة، احفظ في /tmp (fallback)
        if (empty($this->cloudName) || empty($this->apiKey)) {
            return $this->storeInTmp($file, $folder);
        }

        $timestamp = time();
        $folderPath = "tasweeq/{$folder}";
        $signature = sha1("folder={$folderPath}&timestamp={$timestamp}{$this->apiSecret}");

        $tempPath = $file->getRealPath();

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->uploadUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'file'      => new \CURLFile($tempPath, $file->getMimeType(), $file->getClientOriginalName()),
                'folder'    => $folderPath,
                'api_key'   => $this->apiKey,
                'timestamp' => $timestamp,
                'signature' => $signature,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['secure_url'])) {
                return $data['secure_url']; // نحفظ URL كامل في DB
            }
        }

        // fallback إذا فشل Cloudinary
        \Log::error('Cloudinary upload failed', ['code' => $httpCode, 'response' => $response]);
        return $this->storeInTmp($file, $folder);
    }

    /**
     * حذف صورة من Cloudinary (إذا كانت URL من Cloudinary)
     */
    public function delete(?string $imagePathOrUrl): void
    {
        if (!$imagePathOrUrl || !str_contains($imagePathOrUrl, 'cloudinary.com')) {
            return; // ليست صورة Cloudinary، تجاهل
        }

        // استخراج public_id من URL
        // مثال: https://res.cloudinary.com/xxx/image/upload/v123/tasweeq/products/abc.jpg
        if (preg_match('/\/upload\/(?:v\d+\/)?(.+?)(?:\.\w+)?$/', $imagePathOrUrl, $matches)) {
            $publicId = $matches[1];
            $timestamp = time();
            $signature = sha1("public_id={$publicId}&timestamp={$timestamp}{$this->apiSecret}");

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query([
                    'public_id' => $publicId,
                    'api_key'   => $this->apiKey,
                    'timestamp' => $timestamp,
                    'signature' => $signature,
                ]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
            ]);
            curl_exec($ch);
            curl_close($ch);
        }
    }

    /**
     * Fallback: حفظ في /tmp على Vercel (مؤقت، لا يستمر)
     */
    private function storeInTmp(UploadedFile $file, string $folder): string
    {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move('/tmp/' . $folder, $filename);
        return $folder . '/' . $filename;
    }
}
