<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\EshaqFullImportSeeder;
use Database\Seeders\EshaqMeatsFishSeeder;
use Database\Seeders\EshaqBakeriesSeeder;
use Database\Seeders\EshaqGiftsStationerySeeder;
use Database\Seeders\EshaqAdditionalSectionsSeeder;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('eshaq:import', function () {
    $this->info("بدء استيراد مطاعم وقوائم ومنتجات إسحاق...");
    $seeder = new EshaqFullImportSeeder();
    $seeder->setCommand($this);
    $seeder->run();
})->purpose('Import all restaurants, menus, and products from Eshaq dataset');

Artisan::command('eshaq:import-meats', function () {
    $this->info("بدء استيراد اللحوم والأسماك والدواجن من إسحاق...");
    $seeder = new EshaqMeatsFishSeeder();
    $seeder->setCommand($this);
    $seeder->run();
})->purpose('Import all meats and fish stores, menus, and products from Eshaq dataset');

Artisan::command('eshaq:import-bakeries', function () {
    $this->info("بدء استيراد المخابز وقوائمها ومنتجاتها من إسحاق...");
    $seeder = new EshaqBakeriesSeeder();
    $seeder->setCommand($this);
    $seeder->run();
})->purpose('Import all bakeries, menus, and products from Eshaq dataset');

Artisan::command('eshaq:import-gifts-stationery', function () {
    $this->info("بدء استيراد التحف والهدايا والمكتبات وقوائمها ومنتجاتها من إسحاق...");
    $seeder = new EshaqGiftsStationerySeeder();
    $seeder->setCommand($this);
    $seeder->run();
})->purpose('Import all gifts, antiques, and stationery stores, menus, and products from Eshaq dataset');

Artisan::command('eshaq:import-new-sections', function () {
    $this->info("بدء استيراد أقسام إسحاق ماركت، شي إن، والعسل والبن والبهارات والمكسرات...");
    $seeder = new EshaqAdditionalSectionsSeeder();
    $seeder->setCommand($this);
    $seeder->run();
})->purpose('استيراد إسحاق ماركت وشي إن والعسل والبهارات من إسحاق');

Artisan::command('eshaq:download-images', function () {
    $this->info("بدء فحص وتحميل جميع صور المطاعم والمنتجات المستوردة...");
    $script = base_path('download_all_images.py');
    passthru("python {$script}");

    $this->info("تحديث مسارات الصور في قاعدة البيانات...");
    \Illuminate\Support\Facades\DB::update("UPDATE categories SET image = 'categories/' || regexp_replace(image, '^.*/', '') WHERE image LIKE 'http%'");
    \Illuminate\Support\Facades\DB::update("UPDATE stores SET logo = 'stores/logos/' || regexp_replace(logo, '^.*/', '') WHERE logo LIKE 'http%'");
    \Illuminate\Support\Facades\DB::update("UPDATE stores SET banner = 'stores/banners/' || regexp_replace(banner, '^.*/', '') WHERE banner LIKE 'http%'");
    \Illuminate\Support\Facades\DB::update("UPDATE products SET image = 'products/' || regexp_replace(image, '^.*/', '') WHERE image LIKE 'http%'");

    $this->info("تم الانتهاء بنجاح!");
})->purpose('تحميل جميع الصور المستوردة للمطاعم والمنتجات وحفظها محلياً');
