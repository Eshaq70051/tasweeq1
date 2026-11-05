<?php

namespace Tests\Unit;

use App\Services\CloudinaryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class CloudinaryServiceVercelTest extends TestCase
{
    public function test_vercel_never_falls_back_to_local_image_storage(): void
    {
        Storage::fake('public');
        config([
            'cloudinary.cloud_name' => '',
            'cloudinary.api_key' => '',
            'cloudinary.api_secret' => '',
        ]);
        $hadVercelVariable = array_key_exists('VERCEL', $_SERVER);
        $previousVercelValue = $_SERVER['VERCEL'] ?? null;
        $_SERVER['VERCEL'] = '1';

        try {
            (new CloudinaryService())->upload(
                UploadedFile::fake()->create('logo.jpg', 10, 'image/jpeg'),
                'stores/logos'
            );
            $this->fail('An unconfigured Cloudinary upload on Vercel must fail explicitly.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Cloudinary is not configured', $exception->getMessage());
        } finally {
            if ($hadVercelVariable) {
                $_SERVER['VERCEL'] = $previousVercelValue;
            } else {
                unset($_SERVER['VERCEL']);
            }
        }

        $this->assertSame([], Storage::disk('public')->allFiles('stores/logos'));
    }
}
