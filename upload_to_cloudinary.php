<?php
/**
 * Cloudinary Image Upload Script
 * رفع الصور إلى Cloudinary وتحديث قاعدة البيانات
 * 
 * Usage: php upload_to_cloudinary.php
 */

$cloudinary = config('cloudinary');
$database = config('database.connections.pgsql');

$CLOUD_NAME = $cloudinary['cloud_name'] ?? '';
$API_KEY = $cloudinary['api_key'] ?? '';
$API_SECRET = $cloudinary['api_secret'] ?? '';
$DB_HOST = $database['host'] ?? '';
$DB_PORT = $database['port'] ?? '';
$DB_NAME = $database['database'] ?? '';
$DB_USER = $database['username'] ?? '';
$DB_PASS = $database['password'] ?? '';
$DB_SSLMODE = $database['sslmode'] ?? 'require';

$STORAGE_PATH = __DIR__ . '/storage/app/public/';

$missingSettings = [];
foreach ([
    'CLOUDINARY_CLOUD_NAME' => $CLOUD_NAME,
    'CLOUDINARY_API_KEY' => $API_KEY,
    'CLOUDINARY_API_SECRET' => $API_SECRET,
    'DB_HOST' => $DB_HOST,
    'DB_PORT' => $DB_PORT,
    'DB_DATABASE' => $DB_NAME,
    'DB_USERNAME' => $DB_USER,
    'DB_PASSWORD' => $DB_PASS,
] as $setting => $value) {
    if ($value === '') {
        $missingSettings[] = $setting;
    }
}

if ($missingSettings !== []) {
    die('❌ Missing environment settings: ' . implode(', ', $missingSettings) . "\n");
}

echo "🚀 بدء رفع الصور إلى Cloudinary...\n\n";

// الاتصال بقاعدة البيانات
try {
    $pdo = new PDO(
        "pgsql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};sslmode={$DB_SSLMODE}",
        $DB_USER,
        $DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ تم الاتصال بقاعدة البيانات\n\n";
} catch (Exception $e) {
    die("❌ فشل الاتصال: " . $e->getMessage() . "\n");
}

// دالة رفع صورة واحدة إلى Cloudinary
function uploadToCloudinary($filePath, $folder, $cloudName, $apiKey, $apiSecret) {
    if (!file_exists($filePath)) {
        return null;
    }
    
    $timestamp = time();
    $signature = sha1("folder={$folder}&timestamp={$timestamp}&upload_preset=ml_default{$apiSecret}");
    // Signature without upload_preset
    $signature = sha1("folder={$folder}&timestamp={$timestamp}{$apiSecret}");
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'file'      => new CURLFile($filePath),
            'folder'    => "tasweeq/{$folder}",
            'api_key'   => $apiKey,
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
        return $data['secure_url'] ?? null;
    }
    
    return null;
}

$stats = ['uploaded' => 0, 'skipped' => 0, 'failed' => 0, 'updated_db' => 0];

// =============================================
// 1. رفع صور المتاجر (logos)
// =============================================
echo "📦 [1/4] رفع لوجوهات المتاجر...\n";
$stores = $pdo->query("SELECT id, logo FROM stores WHERE logo IS NOT NULL AND logo NOT LIKE 'http%'")->fetchAll(PDO::FETCH_ASSOC);
echo "   عدد المتاجر: " . count($stores) . "\n";

foreach ($stores as $store) {
    $localFile = $STORAGE_PATH . $store['logo'];
    if (!file_exists($localFile)) {
        $stats['skipped']++;
        continue;
    }
    
    $url = uploadToCloudinary($localFile, 'stores/logos', $CLOUD_NAME, $API_KEY, $API_SECRET);
    if ($url) {
        $pdo->prepare("UPDATE stores SET logo = ? WHERE id = ?")->execute([$url, $store['id']]);
        $stats['uploaded']++;
        $stats['updated_db']++;
        echo "   ✅ متجر #{$store['id']}: {$url}\n";
    } else {
        $stats['failed']++;
        echo "   ❌ فشل رفع لوجو متجر #{$store['id']}\n";
    }
    usleep(100000); // 0.1s delay to avoid rate limiting
}

// =============================================
// 2. رفع بانرات المتاجر (banners)
// =============================================
echo "\n📦 [2/4] رفع بانرات المتاجر...\n";
$stores_banners = $pdo->query("SELECT id, banner FROM stores WHERE banner IS NOT NULL AND banner NOT LIKE 'http%'")->fetchAll(PDO::FETCH_ASSOC);
echo "   عدد البانرات: " . count($stores_banners) . "\n";

foreach ($stores_banners as $store) {
    $localFile = $STORAGE_PATH . $store['banner'];
    if (!file_exists($localFile)) {
        $stats['skipped']++;
        continue;
    }
    
    $url = uploadToCloudinary($localFile, 'stores/banners', $CLOUD_NAME, $API_KEY, $API_SECRET);
    if ($url) {
        $pdo->prepare("UPDATE stores SET banner = ? WHERE id = ?")->execute([$url, $store['id']]);
        $stats['uploaded']++;
        $stats['updated_db']++;
    } else {
        $stats['failed']++;
    }
    usleep(100000);
}

// =============================================
// 3. رفع صور التصنيفات (categories)
// =============================================
echo "\n📦 [3/4] رفع صور التصنيفات...\n";
$categories = $pdo->query("SELECT id, image FROM categories WHERE image IS NOT NULL AND image NOT LIKE 'http%'")->fetchAll(PDO::FETCH_ASSOC);
echo "   عدد التصنيفات: " . count($categories) . "\n";

foreach ($categories as $cat) {
    // Category model uses 'media/' path
    $localFile = $STORAGE_PATH . ltrim($cat['image'], '/');
    if (!file_exists($localFile)) {
        // Try without prefix
        $localFile = $STORAGE_PATH . 'categories/' . basename($cat['image']);
    }
    if (!file_exists($localFile)) {
        $stats['skipped']++;
        echo "   ⚠️ ملف مفقود: {$cat['image']}\n";
        continue;
    }
    
    $url = uploadToCloudinary($localFile, 'categories', $CLOUD_NAME, $API_KEY, $API_SECRET);
    if ($url) {
        $pdo->prepare("UPDATE categories SET image = ? WHERE id = ?")->execute([$url, $cat['id']]);
        $stats['uploaded']++;
        $stats['updated_db']++;
        echo "   ✅ تصنيف #{$cat['id']}: {$url}\n";
    } else {
        $stats['failed']++;
        echo "   ❌ فشل رفع صورة تصنيف #{$cat['id']}\n";
    }
    usleep(100000);
}

// =============================================
// 4. رفع صور المنتجات (products) - الأكثر
// =============================================
echo "\n📦 [4/4] رفع صور المنتجات ({$stats['uploaded']} حتى الآن)...\n";
echo "   ⚠️ هذا سيستغرق وقتاً طويلاً لـ 15,000+ منتج\n";
echo "   يمكنك إيقافه (Ctrl+C) وإعادة تشغيله لاحقاً - تم تحديث ما رُفع\n\n";

// Process in batches
$offset = 0;
$batch_size = 50;
$total_products = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE image IS NOT NULL AND image NOT LIKE 'http%'")->fetchColumn();
echo "   إجمالي المنتجات: {$total_products}\n\n";

while (true) {
    $stmt = $pdo->prepare("SELECT id, image FROM products WHERE image IS NOT NULL AND image NOT LIKE 'http%' ORDER BY id LIMIT ? OFFSET ?");
    $stmt->execute([$batch_size, $offset]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($products)) break;
    
    foreach ($products as $product) {
        $localFile = $STORAGE_PATH . $product['image'];
        if (!file_exists($localFile)) {
            $stats['skipped']++;
            continue;
        }
        
        $url = uploadToCloudinary($localFile, 'products', $CLOUD_NAME, $API_KEY, $API_SECRET);
        if ($url) {
            $pdo->prepare("UPDATE products SET image = ? WHERE id = ?")->execute([$url, $product['id']]);
            $stats['uploaded']++;
            $stats['updated_db']++;
            
            if ($stats['uploaded'] % 100 === 0) {
                echo "   ✅ تم رفع {$stats['uploaded']} صورة...\n";
            }
        } else {
            $stats['failed']++;
        }
        usleep(50000); // 0.05s
    }
    
    $offset += $batch_size;
    echo "   📊 تقدم: {$offset}/{$total_products} | رُفع: {$stats['uploaded']} | فشل: {$stats['failed']}\n";
}

// =============================================
// النتيجة النهائية
// =============================================
echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ اكتمل!\n";
echo "   📤 رُفع: {$stats['uploaded']} صورة\n";
echo "   🗄️  حُدّث في DB: {$stats['updated_db']} سجل\n";
echo "   ⏭️  تُجاوز (ملف مفقود): {$stats['skipped']}\n";
echo "   ❌ فشل: {$stats['failed']}\n";
echo str_repeat("=", 50) . "\n";
echo "\n🎉 الصور الآن متاحة على Cloudinary CDN!\n";
echo "   تحديث Vercel سيعرض الصور تلقائياً.\n";
