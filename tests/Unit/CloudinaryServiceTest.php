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

        $previousVercel = getenv('VERCEL');
        putenv('VERCEL=1');
        $_ENV['VERCEL'] = '1';
        $_SERVER['VERCEL'] = '1';

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Cloudinary is not configured.');

            app(CloudinaryService::class)->upload(
                new UploadedFile(
                    base_path('storage/app/public/stores/logos/1792822564.jpg'),
                    'logo.jpg',
                    'image/jpeg',
                    null,
                    true,
                ),
                'stores/logos',
            );
        } finally {
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
