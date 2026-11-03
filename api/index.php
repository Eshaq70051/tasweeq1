<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (isset($_GET['diag'])) {
    phpinfo();
    exit;
}

try {
    define('LARAVEL_START', microtime(true));

    // إنشاء مسارات التخزين المؤقتة داخل /tmp
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

    require __DIR__ . '/../vendor/autoload.php';

    /** @var \Illuminate\Foundation\Application $app */
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    $app->useStoragePath($storagePath);

    $app->handleRequest(\Illuminate\Http\Request::capture());
} catch (\Throwable $e) {
    http_response_code(500);
    echo "<h1>Laravel Execution Error</h1>";
    echo "<h3>" . htmlspecialchars($e->getMessage()) . "</h3>";
    echo "<p>File: " . htmlspecialchars($e->getFile()) . " (Line: " . $e->getLine() . ")</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
