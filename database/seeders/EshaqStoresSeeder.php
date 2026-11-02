<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Store;
use App\Models\StoreMenuSection;
use App\Models\Product;

class EshaqStoresSeeder extends Seeder
{
    public function run(): void
    {
        // 1. إنشاء التصنيفات الرئيسية المطابقة لإسحاق
        $categoriesData = [
            ['name' => 'المطاعم', 'description' => 'أشهى المأكولات والمطاعم الشعبية والعالمية'],
            ['name' => 'السدادات', 'description' => 'بطاقات الألعاب وشحن الرصيد والاشتراكات'],
            ['name' => 'حلويات - مشروبات - ايسكريم - كافيهات', 'description' => 'أفضل الكافيهات والحلويات الشرقية والغربية'],
            ['name' => 'اللحوم والاسماك والدجاج', 'description' => 'لحوم وأسماك ودواجن طازجة يومياً'],
            ['name' => 'المخبوزات', 'description' => 'كرواسون ومعجنات ومخبوزات طازجة'],
            ['name' => 'شي إن إسحاق', 'description' => 'أزياء وموضة وملابس شي إن العالمية'],
            ['name' => 'الخضار والفواكه', 'description' => 'خضروات وفواكه طازجة من المزارع'],
            ['name' => 'العطور والبخور', 'description' => 'أرقى العطور والبخور والعود اليمني'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['name']] = Category::firstOrCreate(['name' => $cat['name']], $cat);
        }

        $restaurantCategory = $categories['المطاعم'];

        // 2. إنشاء المطاعم الرئيسية المطابقة لصور التطبيق
        $storesData = [
            [
                'name' => 'مؤسسة الشيباني للمطاعم',
                'name_en' => 'Al-Shaibani Foundation',
                'distance' => '4.3 كيلو',
                'delivery_time' => '40 - 60 دقيقة',
                'rating' => 4.35,
                'is_open' => true,
                'is_featured' => true,
                'phone' => '777000111',
                'address' => 'صنعاء - حدة',
                'sections' => [
                    'العروض' => [
                        [
                            'name' => 'وليمة الشيباني الملكية',
                            'price' => 9950,
                            'old_price' => 11900,
                            'details' => 'مناسب للجمعات - 18 قطعة بروست وأصابع دجاج وشيبس مع التوابل ومشروبات غازية',
                        ],
                        [
                            'name' => 'صفقة البروست الذهبية',
                            'price' => 5400,
                            'old_price' => 7300,
                            'details' => 'الأكثر توفيراً - 4 نفر بروست دجاج مع أصابع وبطاطس ومشروب غازي',
                        ],
                    ],
                    'البروست' => [
                        [
                            'name' => 'وجبة بروست حراق 4 قطع',
                            'price' => 3200,
                            'details' => 'قطع دجاج مقرمشة حارة تقدم مع البطاطس والصلصة الخاصة والخبز',
                        ],
                        [
                            'name' => 'وجبة بروست عادي 4 قطع',
                            'price' => 3000,
                            'details' => 'قطع دجاج مقرمشة متبلة تقدم مع البطاطس وصلصة الثوم',
                        ],
                    ],
                    'اللحم' => [
                        [
                            'name' => 'نفر مضبي لحم بلدي',
                            'price' => 4500,
                            'details' => 'لحم بلدي مشوي على الحجارة مع أرز بشاور وحلبة وسحاوق',
                        ],
                        [
                            'name' => 'نفر مندي لحم فاخر',
                            'price' => 4800,
                            'details' => 'لحم طازج في الميفا مع أرز مندي يمني مدخن ومتبل',
                        ],
                    ],
                    'الدجاج' => [
                        [
                            'name' => 'نصف حبة دجاج شواية مع الأرز',
                            'price' => 2400,
                            'details' => 'دجاج محمر على الشواية مع خلطة الشيباني الخاصة وسلطة',
                        ],
                        [
                            'name' => 'نصف حبة دجاج مضبي',
                            'price' => 2500,
                            'details' => 'مشوي على الحجر مع أرز بشاور ومقبلات',
                        ],
                    ],
                    'المشروبات' => [
                        [
                            'name' => 'مشروب غازي علبة بارد',
                            'price' => 400,
                            'details' => 'كوكاكولا / بيبسي / سفن آب بارد ومثلج',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'مطعم القلعة',
                'name_en' => 'Castle Restaurant',
                'distance' => '4.6 كيلو',
                'delivery_time' => '40 - 60 دقيقة',
                'rating' => 4.14,
                'is_open' => true,
                'is_featured' => true,
                'phone' => '777000222',
                'address' => 'صنعاء - شارع الستين',
                'sections' => [
                    'العروض' => [
                        [
                            'name' => 'عرض القلعة المشكل',
                            'price' => 6500,
                            'old_price' => 7800,
                            'details' => 'مشاوي مشكلة نفرين مع مقبلات وأرز',
                        ],
                    ],
                    'المشاوي' => [
                        [
                            'name' => 'مشاوي مشكل القلعة',
                            'price' => 4200,
                            'details' => 'كباب وشيش طاووق وأوصال لحم مع الحمص والخبز',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'ريو',
                'name_en' => 'Re,u',
                'distance' => '0.8 كيلو',
                'delivery_time' => '40 - 60 دقيقة',
                'rating' => 4.21,
                'is_open' => true,
                'is_featured' => false,
                'phone' => '777000333',
                'address' => 'صنعاء - شارع بغداد',
                'sections' => [
                    'البرجر والوجبات السريعة' => [
                        [
                            'name' => 'ريو برجر دبل تشيز',
                            'price' => 2800,
                            'details' => 'لحم أنجوس مشوي مع جبنة شيدر وصلصة ريو الخاصة',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'تويستر - حده الفندق',
                'name_en' => 'Twister',
                'distance' => '3.8 كيلو',
                'delivery_time' => '40 - 60 دقيقة',
                'rating' => 4.30,
                'is_open' => true,
                'is_featured' => false,
                'phone' => '777000444',
                'address' => 'صنعاء - حده الفندق',
                'sections' => [
                    'سندوتشات تويستر' => [
                        [
                            'name' => 'تويستر سوبر كرانشي',
                            'price' => 1800,
                            'details' => 'رول دجاج مقرمش مع خس وصوص رانش',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'TRUCK BURGER',
                'name_en' => 'Truck Burger',
                'distance' => '2.1 كيلو',
                'delivery_time' => '25 - 40 دقيقة',
                'rating' => 4.70,
                'is_open' => true,
                'is_featured' => true,
                'phone' => '777000555',
                'address' => 'صنعاء - شارع الجزائر',
                'sections' => [
                    'البرجر' => [
                        [
                            'name' => 'كلاسيك تراك برجر',
                            'price' => 2500,
                            'details' => 'لحم بقري مشوي مع المخلل والبصل وصلصة التراك',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'شواية الدخيل سفري',
                'name_en' => 'Al-Dakheel Grill',
                'distance' => '1.5 كيلو',
                'delivery_time' => '30 - 45 دقيقة',
                'rating' => 4.50,
                'is_open' => true,
                'is_featured' => true,
                'phone' => '777000666',
                'address' => 'صنعاء - الدائري',
                'sections' => [
                    'الشواية' => [
                        [
                            'name' => 'دجاج شواية الدخيل مع الرز',
                            'price' => 2300,
                            'details' => 'نصف حبة مع أرز بسمتي وسلطات ومرق',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'مدينة المشاوي',
                'name_en' => 'Madinat Al-Mashawi',
                'distance' => '3.0 كيلو',
                'delivery_time' => '35 - 50 دقيقة',
                'rating' => 4.60,
                'is_open' => true,
                'is_featured' => true,
                'phone' => '777000777',
                'address' => 'صنعاء - الزبيري',
                'sections' => [
                    'المشاوي' => [
                        [
                            'name' => 'صحن كباب لحم غنم',
                            'price' => 3800,
                            'details' => '4 أسياخ كباب طازج مع البقدونس والبصل والمتبل',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'مطابخ التوازي',
                'name_en' => 'Al-Tawazi Kitchens',
                'distance' => '2.8 كيلو',
                'delivery_time' => '30 - 45 دقيقة',
                'rating' => 4.40,
                'is_open' => true,
                'is_featured' => true,
                'phone' => '777000888',
                'address' => 'صنعاء - هايل',
                'sections' => [
                    'الشعبيات' => [
                        [
                            'name' => 'فحسة لحم بلدي مغلية',
                            'price' => 2700,
                            'details' => 'فحسة يمنية في مقلى حار مع حلبة وخضار وملوج حار',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($storesData as $storeInfo) {
            $sections = $storeInfo['sections'];
            unset($storeInfo['sections']);

            $storeInfo['category_id'] = $restaurantCategory->id;

            $store = Store::firstOrCreate(
                ['name' => $storeInfo['name']],
                $storeInfo
            );

            $sectionOrder = 0;
            foreach ($sections as $sectionName => $productsList) {
                $section = StoreMenuSection::firstOrCreate(
                    ['store_id' => $store->id, 'name' => $sectionName],
                    ['sort_order' => $sectionOrder++, 'is_active' => true]
                );

                foreach ($productsList as $productData) {
                    $productData['category_id'] = $restaurantCategory->id;
                    $productData['store_id'] = $store->id;
                    $productData['store_menu_section_id'] = $section->id;
                    $productData['stock'] = 100;
                    $productData['is_available'] = true;

                    Product::firstOrCreate(
                        ['store_id' => $store->id, 'name' => $productData['name']],
                        $productData
                    );
                }
            }
        }
    }
}
