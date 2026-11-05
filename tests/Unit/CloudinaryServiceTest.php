<?php

namespace Tests\Unit;

use App\Services\CloudinaryService;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Tests\TestCase;

class CloudinaryServiceTest extends TestCase
{
    public function test_vercel_upload_does_not_fall_back_to_local_storage(): void
    {
        config([
            'cloudinary.cloud_name' => '',
            'cloudinary.api_key' => '',
            'cloudinary.api_secret' => '',
        ]);

        $filePath = tempnam(sys_get_temp_dir(), 'vercel-upload-');
        file_put_contents($filePath, 'test image');

        $previousVercel = getenv('VERCEL');
        putenv('VERCEL=1');
        $_ENV['VERCEL'] = '1';
        $_SERVER['VERCEL'] = '1';

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Cloudinary is not configured.');

            app(CloudinaryService::class)->upload(
                new UploadedFile($filePath, 'logo.jpg', 'image/jpeg', null, true),
                'stores/logos',
            );
        } finally {
            unlink($filePath);

            if ($previousVercel === false) {
                putenv('VERCEL');
                unset($_ENV['VERCEL'], $_SERVER['VERCEL']);
            } else {
                putenv("VERCEL={$previousVercel}");
                $_ENV['VERCEL'] = $previousVercel;
                $_SERVER['VERCEL'] = $previousVercel;
            }
        }
    }
}
