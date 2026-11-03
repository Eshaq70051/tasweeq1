<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// إنشاء مسارات التخزين المؤقتة داخل /tmp لأن بيئة Vercel للقراءة فقط (Read-only)
$storagePath = '/tmp/storage';
$subDirs = [
    $storagePath . '/app/public',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/framework/views',
    $storagePath . '/logs',
    '/tmp/bootstrap/cache',
];

foreach ($subDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// توجيه ملفات الكاش إلى /tmp
putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');
putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php');
putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');

$_ENV['APP_CONFIG_CACHE'] = '/tmp/bootstrap/cache/config.php';
$_ENV['APP_EVENTS_CACHE'] = '/tmp/bootstrap/cache/events.php';
$_ENV['APP_PACKAGES_CACHE'] = '/tmp/bootstrap/cache/packages.php';
$_ENV['APP_ROUTES_CACHE'] = '/tmp/bootstrap/cache/routes.php';
$_ENV['APP_SERVICES_CACHE'] = '/tmp/bootstrap/cache/services.php';
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';

// تحميل الـ Autoloader
require __DIR__ . '/../vendor/autoload.php';

// تهيئة تطبيق لارافيل
/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

// تعيين مجلد التخزين إلى المسار المؤقت المسموح بالكتابة فيه
$app->useStoragePath($storagePath);

// معالجة الطلب
$app->handleRequest(Request::capture());
