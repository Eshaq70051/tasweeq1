<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Store;
use App\Models\StoreMenuSection;
use App\Models\Product;

class EshaqMeatsFishSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = storage_path('eshaq_meats_and_fish.json');

        if (!file_exists($jsonPath)) {
            $this->command->error("الملف غير موجود في: {$jsonPath}");
            return;
        }

        $this->command->info("جاري قراءة بيانات اللحوم والأسماك والدواجن من: {$jsonPath} ...");
        $rawJson = file_get_contents($jsonPath);
        $storesData = json_decode($rawJson, true);

        if (!$storesData || !is_array($storesData)) {
            $this->command->error("فشل في تحليل ملف JSON أو الملف فارغ.");
            return;
        }

        $totalStores = count($storesData);
        $this->command->info("تم تحميل {$totalStores} متجر بنجاح. جاري الاستيراد لقاعدة البيانات...");

        // 1. إنشاء أو تحديث تصنيف "اللحوم والاسماك والدجاج"
        $category = Category::where('name', 'اللحوم والاسماك والدجاج')
            ->orWhere('name', 'اللحوم والأسماك والدجاج')
            ->orWhere('name', 'لحوم واسماك ودجاج')
            ->first();

        $categoryData = [
            'name' => 'اللحوم والاسماك والدجاج',
            'description' => 'أجود أنواع اللحوم البلدية الطازجة، والأسماك البحرية، والدواجن اليومية من ملاحم ومراكز صنعاء',
            'image' => 'categories/1791195622.jpg',
        ];

        if (!$category) {
            $category = Category::create($categoryData);
        } else {
            $category->update($categoryData);
        }

        $importedStoresCount = 0;
        $importedSectionsCount = 0;
        $importedProductsCount = 0;

        $now = now();

        foreach ($storesData as $index => $sData) {
            $storeName = trim($sData['name'] ?? '');
            if (empty($storeName)) {
                continue;
            }

            // إنشاء أو تحديث بيانات المتجر/الملحمة/مركز الأسماك
            $store = Store::firstOrNew(['name' => $storeName]);
            $store->category_id = $category->id;
            $store->name_en = $sData['name_en'] ?? null;
            $store->logo = $sData['logo'] ?? null;
            $store->banner = $sData['banner'] ?? null;
            $store->rating = $sData['rating'] ?? 4.5;
            $store->distance = $sData['distance'] ?? '1.5 كم';
            $store->delivery_time = $sData['delivery_time'] ?? '30 - 45 دقيقة';
            $store->is_open = $sData['is_open'] ?? true;
            $store->is_featured = $sData['is_featured'] ?? false;
            $store->address = $sData['address'] ?? 'صنعاء';
            $store->phone = $sData['phone'] ?? null;
            $store->save();

            $importedStoresCount++;

            // 2. إنشاء أقسام المنيو للمتجر
            $sectionMap = []; // Eshaq Section ID -> DB section ID
            $defaultSection = null;

            if (!empty($sData['sections'])) {
                foreach ($sData['sections'] as $sIndex => $sec) {
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
                    ['store_id' => $store->id, 'name' => 'المنتجات الطازجة'],
                    ['sort_order' => 0, 'is_active' => true]
                );
                $defaultSection = $section->id;
                $importedSectionsCount++;
            }

            // 3. استيراد المنتجات
            if (!empty($sData['products'])) {
                $existingProducts = Product::where('store_id', $store->id)
                    ->pluck('id', 'name')
                    ->toArray();

                $toInsert = [];

                foreach ($sData['products'] as $prod) {
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
                    foreach (array_chunk($toInsert, 100) as $chunk) {
                        Product::insert($chunk);
                    }
                    $importedProductsCount += count($toInsert);
                }
            }

            if (($index + 1) % 10 === 0 || ($index + 1) === $totalStores) {
                $this->command->info("  تم استيراد " . ($index + 1) . "/{$totalStores} متجر بنجاح...");
            }
        }

        $this->command->info("==========================================");
        $this->command->info("اكتمل استيراد اللحوم والأسماك والدواجن بنجاح!");
        $this->command->info("إجمالي المتاجر: {$importedStoresCount}");
        $this->command->info("إجمالي أقسام المنيو: {$importedSectionsCount}");
        $this->command->info("إجمالي المنتجات: {$importedProductsCount}");
        $this->command->info("==========================================");
    }
}
