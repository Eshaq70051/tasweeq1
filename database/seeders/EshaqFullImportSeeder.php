<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Store;
use App\Models\StoreMenuSection;
use App\Models\Product;

class EshaqFullImportSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = storage_path('eshaq_restaurants.json');

        if (!file_exists($jsonPath)) {
            $this->command->error("الملف غير موجود في: {$jsonPath}");
            return;
        }

        $this->command->info("جاري قراءة بيانات المطاعم من: {$jsonPath} ...");
        $rawJson = file_get_contents($jsonPath);
        $restaurants = json_decode($rawJson, true);

        if (!$restaurants || !is_array($restaurants)) {
            $this->command->error("فشل في تحليل ملف JSON أو الملف فارغ.");
            return;
        }

        $totalStores = count($restaurants);
        $this->command->info("تم تحميل {$totalStores} مطعم بنجاح. جاري الاستيراد لقاعدة البيانات...");

        // 1. التأكد من وجود تصنيف "المطاعم"
        $category = Category::where('name', 'المطاعم')
            ->orWhere('name', 'مطاعم')
            ->first();

        if (!$category) {
            $category = Category::create([
                'name' => 'المطاعم',
                'description' => 'أشهى المأكولات والمطاعم الشعبية والعالمية',
            ]);
        } else {
            if ($category->name !== 'المطاعم') {
                $category->update(['name' => 'المطاعم']);
            }
        }

        $importedStoresCount = 0;
        $importedSectionsCount = 0;
        $importedProductsCount = 0;

        $now = now();

        foreach ($restaurants as $index => $rData) {
            $storeName = trim($rData['name'] ?? '');
            if (empty($storeName)) {
                continue;
            }

            // إنشاء أو تحديث بيانات المطعم
            $store = Store::firstOrNew(['name' => $storeName]);
            $store->category_id = $category->id;
            $store->name_en = $rData['name_en'] ?? null;
            $store->logo = $rData['logo'] ?? null;
            $store->banner = $rData['banner'] ?? null;
            $store->rating = $rData['rating'] ?? 4.5;
            $store->distance = $rData['distance'] ?? '1.5 كم';
            $store->delivery_time = $rData['delivery_time'] ?? '30 - 45 دقيقة';
            $store->is_open = $rData['is_open'] ?? true;
            $store->is_featured = $rData['is_featured'] ?? false;
            $store->address = $rData['address'] ?? 'صنعاء';
            $store->phone = $rData['phone'] ?? null;
            $store->save();

            $importedStoresCount++;

            // 2. إنشاء أقسام المنيو
            $sectionMap = []; // Eshaq ID -> DB section ID
            $defaultSection = null;

            if (!empty($rData['sections'])) {
                foreach ($rData['sections'] as $sIndex => $sec) {
                    $secName = trim($sec['name'] ?? '');
                    if (empty($secName)) continue;

                    $section = StoreMenuSection::firstOrCreate(
                        ['store_id' => $store->id, 'name' => $secName],
                        ['sort_order' => $sIndex, 'is_active' => true]
                    );

                    $sectionMap[$sec['id']] = $section->id;
                    if ($defaultSection === null) {
                        $defaultSection = $section->id;
                    }
                    $importedSectionsCount++;
                }
            }

            if ($defaultSection === null) {
                $section = StoreMenuSection::firstOrCreate(
                    ['store_id' => $store->id, 'name' => 'القائمة الرئيسية'],
                    ['sort_order' => 0, 'is_active' => true]
                );
                $defaultSection = $section->id;
                $importedSectionsCount++;
            }

            // 3. استيراد المنتجات والوجبات
            if (!empty($rData['products'])) {
                $existingProducts = Product::where('store_id', $store->id)
                    ->pluck('id', 'name')
                    ->toArray();

                $toInsert = [];

                foreach ($rData['products'] as $prod) {
                    $prodName = trim($prod['name'] ?? '');
                    if (empty($prodName)) continue;

                    $catId = $prod['category_id'] ?? null;
                    $targetSectionId = ($catId && isset($sectionMap[$catId])) ? $sectionMap[$catId] : $defaultSection;

                    $price = floatval($prod['price'] ?? 0);
                    $oldPrice = !empty($prod['old_price']) ? floatval($prod['old_price']) : null;
                    $details = $prod['details'] ?? null;
                    $image = $prod['image'] ?? null;
                    $isAvailable = $prod['is_available'] ?? true;

                    if (isset($existingProducts[$prodName])) {
                        // تحديث المنتج الحالي
                        Product::where('id', $existingProducts[$prodName])->update([
                            'price' => $price,
                            'old_price' => $oldPrice,
                            'details' => $details,
                            'image' => $image,
                            'is_available' => $isAvailable,
                            'store_menu_section_id' => $targetSectionId,
                            'updated_at' => $now,
                        ]);
                    } else {
                        $toInsert[] = [
                            'store_id' => $store->id,
                            'category_id' => $category->id,
                            'store_menu_section_id' => $targetSectionId,
                            'name' => $prodName,
                            'price' => $price,
                            'old_price' => $oldPrice,
                            'currency' => 'ريال',
                            'details' => $details,
                            'image' => $image,
                            'stock' => 100,
                            'is_available' => $isAvailable,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if (!empty($toInsert)) {
                    // إدراج على دفعات سريعة
                    foreach (array_chunk($toInsert, 100) as $chunk) {
                        Product::insert($chunk);
                    }
                    $importedProductsCount += count($toInsert);
                }
            }

            if (($index + 1) % 25 === 0 || ($index + 1) === $totalStores) {
                $this->command->info("  تم استيراد " . ($index + 1) . "/{$totalStores} مطعم بنجاح...");
            }
        }

        $this->command->info("==========================================");
        $this->command->info("اكتمل الاستيراد بنجاح!");
        $this->command->info("إجمالي المطاعم: {$importedStoresCount}");
        $this->command->info("إجمالي أقسام المنيو: {$importedSectionsCount}");
        $this->command->info("إجمالي المنتجات والوجبات: {$importedProductsCount}");
        $this->command->info("==========================================");
    }
}
