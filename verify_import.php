<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Store;
use App\Models\StoreMenuSection;
use App\Models\Product;

echo "\n==================== CATEGORIES OVERVIEW ====================\n";
$categories = Category::withCount(['stores', 'products'])->get();
foreach ($categories as $c) {
    $sectionsCount = StoreMenuSection::whereIn('store_id', Store::where('category_id', $c->id)->pluck('id'))->count();
    $imgExists = file_exists(storage_path('app/public/' . $c->image)) ? 'EXISTS' : 'MISSING';
    echo sprintf(
        "ID: %-3d | Name: %-35s | Stores: %-4d | Sections: %-4d | Products: %-5d | Img [%s]: %s\n",
        $c->id,
        $c->name,
        $c->stores_count,
        $sectionsCount,
        $c->products_count,
        $imgExists,
        $c->image
    );
}

echo "\n==================== RANDOM SAMPLES ====================\n";
$targetNames = [
    'مستحضرات التجميل والاكسسوارات',
    'المودمات',
    'متاجر إلكترونية',
    'إسحاق فارما',
    'مستلزمات الهواتف و الالكترونيات'
];

foreach ($targetNames as $tName) {
    $cat = Category::where('name', $tName)->first();
    if (!$cat) continue;
    echo "\nCategory: {$cat->name} (ID: {$cat->id})\n";
    $sampleStore = Store::where('category_id', $cat->id)->with('menuSections')->first();
    if ($sampleStore) {
        $pCount = Product::where('store_id', $sampleStore->id)->count();
        echo "  Sample Store: {$sampleStore->name} (Rating: {$sampleStore->rating}, Delivery: {$sampleStore->delivery_time})\n";
        echo "  Logo: {$sampleStore->logo} | Banner: {$sampleStore->banner}\n";
        echo "  Menu Sections Count: " . $sampleStore->menuSections->count() . " | Products Count: {$pCount}\n";
        $sampleProducts = Product::where('store_id', $sampleStore->id)->take(3)->get();
        foreach ($sampleProducts as $sp) {
            echo "    -> Product: {$sp->name} | Price: {$sp->price} {$sp->currency} | Section ID: {$sp->store_menu_section_id} | Image: {$sp->image}\n";
        }
    }
}
echo "\n============================================================\n";
