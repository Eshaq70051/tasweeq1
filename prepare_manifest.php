<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Store;
use App\Models\Product;
use App\Models\Category;

$manifest = [];

function sourceStorageUrl($directory, $filename) {
    $baseUrl = rtrim((string) config('services.eshaq.base_url', ''), '/');
    if ($baseUrl === '') {
        throw new RuntimeException('Set ESHAQ_BASE_URL before generating downloads for missing local images.');
    }

    return "{$baseUrl}/storage/{$directory}/{$filename}";
}

function addUrlToManifest(&$manifest, $url, $fallbackType = 'product') {
    $url = trim($url);
    if (empty($url) || !str_starts_with($url, 'http')) return;
    
    $parsedPath = parse_url($url, PHP_URL_PATH);
    $filename = basename($parsedPath);
    if (!$filename) return;

    $type = $fallbackType;
    $relPath = "products/{$filename}";

    if ($fallbackType === 'store_banner') {
        $type = 'store_banner';
        $relPath = "stores/banners/{$filename}";
    } elseif ($fallbackType === 'store_logo' || str_contains($url, '/storage/markets/')) {
        $type = 'store_logo';
        $relPath = "stores/logos/{$filename}";
    } elseif ($fallbackType === 'category' || str_contains($url, '/storage/sections/')) {
        $type = 'category';
        $relPath = "categories/{$filename}";
    } elseif (str_contains($url, '/storage/products/')) {
        $type = 'product';
        $relPath = "products/{$filename}";
    }

    $manifestKey = "{$type}_{$relPath}";
    if (!isset($manifest[$manifestKey])) {
        $manifest[$manifestKey] = [
            'url' => $url,
            'rel_path' => $relPath,
            'full_path' => storage_path("app/public/{$relPath}"),
            'type' => $type,
        ];
    }
}

// 1. Stores with HTTP
foreach (Store::where('logo', 'like', 'http%')->get(['id', 'logo']) as $s) {
    addUrlToManifest($manifest, $s->logo, 'store_logo');
}
foreach (Store::where('banner', 'like', 'http%')->get(['id', 'banner']) as $s) {
    addUrlToManifest($manifest, $s->banner, 'store_banner');
}

// 2. Products with HTTP
foreach (Product::where('image', 'like', 'http%')->get(['id', 'image']) as $p) {
    addUrlToManifest($manifest, $p->image, 'product');
}

// 3. Categories with HTTP
foreach (Category::where('image', 'like', 'http%')->get(['id', 'image']) as $c) {
    addUrlToManifest($manifest, $c->image, 'category');
}

// 4. Missing files on disk from DB references
foreach (Store::where('logo', 'not like', 'http%')->whereNotNull('logo')->where('logo', '!=', '')->pluck('logo')->unique() as $rel) {
    if (!file_exists(storage_path("app/public/{$rel}"))) {
        $fn = basename($rel);
        addUrlToManifest($manifest, sourceStorageUrl('markets', $fn), 'store_logo');
    }
}
foreach (Store::where('banner', 'not like', 'http%')->whereNotNull('banner')->where('banner', '!=', '')->pluck('banner')->unique() as $rel) {
    if (!file_exists(storage_path("app/public/{$rel}"))) {
        $fn = basename($rel);
        addUrlToManifest($manifest, sourceStorageUrl('markets', $fn), 'store_banner');
    }
}
foreach (Product::where('image', 'not like', 'http%')->whereNotNull('image')->where('image', '!=', '')->pluck('image')->unique() as $rel) {
    if (!file_exists(storage_path("app/public/{$rel}"))) {
        $fn = basename($rel);
        addUrlToManifest($manifest, sourceStorageUrl('products', $fn), 'product');
    }
}
foreach (Category::where('image', 'not like', 'http%')->whereNotNull('image')->where('image', '!=', '')->pluck('image')->unique() as $rel) {
    if (!file_exists(storage_path("app/public/{$rel}"))) {
        $fn = basename($rel);
        addUrlToManifest($manifest, sourceStorageUrl('sections', $fn), 'category');
    }
}

// 5. Scan ALL JSON files in storage/
function scanJsonForUrls($data, &$manifest) {
    if (is_array($data)) {
        foreach ($data as $key => $val) {
            if (is_string($val) && str_starts_with($val, 'http')) {
                $type = 'product';
                if ($key === 'logo') $type = 'store_logo';
                elseif ($key === 'banner') $type = 'store_banner';
                elseif ($key === 'image' && str_contains($val, '/sections/')) $type = 'category';
                addUrlToManifest($manifest, $val, $type);
            } else {
                scanJsonForUrls($val, $manifest);
            }
        }
    }
}

$jsonFiles = glob(storage_path('*.json'));
foreach ($jsonFiles as $jf) {
    $content = json_decode(file_get_contents($jf), true);
    if ($content) {
        scanJsonForUrls($content, $manifest);
    }
}

$tasks = array_values($manifest);
file_put_contents('download_manifest.json', json_encode($tasks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "Comprehensive Manifest created with " . count($tasks) . " download tasks.\n";

$byType = [];
$missingCount = 0;
$existingCount = 0;
foreach ($tasks as $t) {
    $byType[$t['type']] = ($byType[$t['type']] ?? 0) + 1;
    if (file_exists($t['full_path']) && filesize($t['full_path']) > 0) {
        $existingCount++;
    } else {
        $missingCount++;
    }
}
print_r($byType);
echo "Already on disk: $existingCount | Remaining to download: $missingCount\n";
