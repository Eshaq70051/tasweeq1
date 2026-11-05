<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

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
        $isConfigured = !empty($this->cloudName) && !empty($this->apiKey) && !empty($this->apiSecret);

        if (
            ! $this->isRunningOnVercel()
            && (app()->environment('testing') || (!$isConfigured && app()->environment('local')))
        ) {
            $path = Storage::disk('public')->putFile($folder, $file);
            if ($path !== false) {
                return $path;
            }

            throw new RuntimeException('Unable to store the uploaded image on the public disk.');
        }

        if (!$isConfigured) {
            throw new RuntimeException(
                'Cloudinary is not configured. Set CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, and CLOUDINARY_API_SECRET.'
            );
        }

        $timestamp = time();
        $folderPath = "tasweeq/{$folder}";
        $signature = sha1("folder={$folderPath}&timestamp={$timestamp}{$this->apiSecret}");

        $tempPath = $file->getRealPath();

        $ch = curl_init();
        if ($ch === false) {
            throw new RuntimeException('Unable to initialize the Cloudinary upload request.');
        }

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
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = is_string($response) ? json_decode($response, true) : null;
        if ($httpCode >= 200 && $httpCode < 300 && is_array($data) && !empty($data['secure_url'])) {
            return $data['secure_url'];
        }

        Log::error('Cloudinary upload failed', [
            'code' => $httpCode,
            'curl_error' => $curlError ?: null,
            'response' => $response,
        ]);

        throw new RuntimeException('Cloudinary image upload failed. Check the server log for details.');
    }

    private function isRunningOnVercel(): bool
    {
        foreach (['VERCEL', 'VERCEL_ENV', 'VERCEL_URL'] as $name) {
            $value = $_SERVER[$name] ?? $_ENV[$name] ?? getenv($name);
            if ($value !== false && $value !== null && $value !== '') {
                return true;
            }
        }

        return false;
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
}
