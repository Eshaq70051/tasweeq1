<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Store;
use App\Models\StoreMenuSection;
use App\Models\Product;

class EshaqAdditionalSectionsSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = storage_path('eshaq_new_sections.json');

        if (!file_exists($jsonPath)) {
            $this->command->error("الملف غير موجود في: {$jsonPath}");
            return;
        }

        $this->command->info("جاري قراءة البيانات من: {$jsonPath} ...");
        $rawJson = file_get_contents($jsonPath);
        $sectionsData = json_decode($rawJson, true);

        if (!$sectionsData || !is_array($sectionsData)) {
            $this->command->error("فشل في تحليل ملف JSON أو الملف فارغ.");
            return;
        }

        $grandTotalStores = 0;
        $grandTotalSections = 0;
        $grandTotalProducts = 0;
        $now = now();

        foreach ($sectionsData as $catItem) {
            $catName = $catItem['category_name'];
            $catDesc = $catItem['category_description'];
            $catImage = $catItem['category_image'];
            $stores = $catItem['stores'] ?? [];

            $this->command->info("\n=======================================================");
            $this->command->info("معالجة التصنيف: {$catName} (عدد المتاجر: " . count($stores) . ")");

            // 1. إنشاء أو تحديث التصنيف
            $category = Category::where('name', $catName)->first();
            if (!$category) {
                // Check without 'إسحاق' or partial match
                if ($catName === 'شي إن إسحاق') {
                    $category = Category::where('name', 'شي إن')->orWhere('name', 'شي ان')->first();
                } elseif ($catName === 'إسحاق ماركت') {
                    $category = Category::where('name', 'ماركت')->orWhere('name', 'إسحاق ماركت')->first();
                }
            }

            $categoryData = [
                'name' => $catName,
                'description' => $catDesc,
                'image' => $catImage,
            ];

            if (!$category) {
                $category = Category::create($categoryData);
            } else {
                $category->update($categoryData);
            }

            $importedStores = 0;
            $importedSections = 0;
            $importedProducts = 0;

            foreach ($stores as $sIndex => $sData) {
                $storeName = trim($sData['name'] ?? '');
                if (empty($storeName)) continue;

                // إنشاء أو تحديث بيانات المتجر
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

                $importedStores++;

                // 2. أقسام المنيو
                $sectionMap = [];
                $defaultSection = null;

                if (!empty($sData['sections'])) {
                    foreach ($sData['sections'] as $secIdx => $sec) {
                        $secName = trim($sec['name'] ?? '');
                        if (empty($secName)) continue;

                        $section = StoreMenuSection::firstOrCreate(
                            ['store_id' => $store->id, 'name' => $secName],
                            ['sort_order' => $secIdx, 'is_active' => true]
                        );

                        $sectionMap[$sec['id']] = $section->id;
                        if ($defaultSection === null) {
                            $defaultSection = $section->id;
                        }
                        $importedSections++;
                    }
                }

                if ($defaultSection === null) {
                    $defaultSectionName = 'المنتجات المميزة';
                    if ($catName === 'شي إن إسحاق') {
                        $defaultSectionName = 'أزياء وموضة شي إن';
                    } elseif (str_contains($catName, 'عسل')) {
                        $defaultSectionName = 'أجود أنواع العسل والمكسرات';
                    } elseif (str_contains($catName, 'ماركت')) {
                        $defaultSectionName = 'أصناف السوبرماركت';
                    }

                    $section = StoreMenuSection::firstOrCreate(
                        ['store_id' => $store->id, 'name' => $defaultSectionName],
                        ['sort_order' => 0, 'is_active' => true]
                    );
                    $defaultSection = $section->id;
                    $importedSections++;
                }

                // 3. المنتجات
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
                        $importedProducts += count($toInsert);
                    }
                }

                $this->command->info("  [{$importedStores}/" . count($stores) . "] استيراد {$storeName} (أقسام: " . count($sData['sections'] ?? []) . "، منتجات: " . count($sData['products'] ?? []) . ")");
            }

            $this->command->info("اكتمل استيراد تصنيف: {$catName} -> متاجر: {$importedStores}، أقسام: {$importedSections}، منتجات: {$importedProducts}");
            $grandTotalStores += $importedStores;
            $grandTotalSections += $importedSections;
            $grandTotalProducts += $importedProducts;
        }

        $this->command->info("\n=======================================================");
        $this->command->info("اكتمل الاستيراد الشامل بنجاح!");
        $this->command->info("إجمالي المتاجر المضافة/المحدثة: {$grandTotalStores}");
        $this->command->info("إجمالي أقسام المنيو: {$grandTotalSections}");
        $this->command->info("إجمالي المنتجات: {$grandTotalProducts}");
        $this->command->info("=======================================================");
    }
}
