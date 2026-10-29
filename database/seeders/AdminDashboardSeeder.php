<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminDashboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'eshaq7700@gmail.com'],
            [
                'name' => 'Eshaq Admin',
                'password' => Hash::make('2003'),
                'role' => 'admin',
                'is_admin' => true,
                'status' => 'active',
                'phone' => '966500000000',
                'avatar' => null,
            ]
        );

        $categories = [
            'إلكترونيات',
            'ملابس',
            'مستحضرات',
            'منزل',
        ];

        foreach ($categories as $categoryName) {
            Category::firstOrCreate(['name' => $categoryName], [
                'description' => 'فئة تجريبية للمتجر',
            ]);
        }

        $productsData = [
            ['name' => 'ساعة ذكية Pro', 'category' => 'إلكترونيات', 'price' => 899.00, 'stock' => 15, 'details' => 'ساعة ذكية بقياس النشاط ومراقبة القلب ومقاومة الماء.'],
            ['name' => 'سماعات لاسلكية X9', 'category' => 'إلكترونيات', 'price' => 349.00, 'stock' => 22, 'details' => 'سماعات ممتازة للصوت العالي مع بطارية تدوم طوال اليوم.'],
            ['name' => 'قميص كاجوال', 'category' => 'ملابس', 'price' => 159.00, 'stock' => 40, 'details' => 'قميص واسع ومريح مناسب للاستخدام اليومي.'],
            ['name' => 'حقيبة سفر Elite', 'category' => 'منزل', 'price' => 420.00, 'stock' => 12, 'details' => 'حقيبة سفر متينة مع تصميم أنيق وتحمل كبير.'],
            ['name' => 'مجفف شعر فائق', 'category' => 'منزل', 'price' => 280.00, 'stock' => 18, 'details' => 'مجفف شعر قوي وسريع مع تقنية حماية الشعر.'],
            ['name' => 'مستحضر العناية', 'category' => 'مستحضرات', 'price' => 120.00, 'stock' => 30, 'details' => 'مستحضر للعناية بالبشرة مع مكونات طبيعية.'],
            ['name' => 'كاميرا 4K Mini', 'category' => 'إلكترونيات', 'price' => 1249.00, 'stock' => 8, 'details' => 'كاميرا صغيرة ولكنها قوية مع تصوير 4K ووضوح عالي.'],
            ['name' => 'جاكيت شتوي', 'category' => 'ملابس', 'price' => 540.00, 'stock' => 10, 'details' => 'جاكيت دافئ ومريح مناسب للأجواء الباردة.'],
        ];

        $productMap = [];
        foreach ($productsData as $item) {
            $category = Category::where('name', $item['category'])->first();

            $product = Product::updateOrCreate(
                ['name' => $item['name']],
                [
                    'category_id' => $category?->id,
                    'price' => $item['price'],
                    'currency' => 'SAR',
                    'details' => $item['details'],
                    'stock' => $item['stock'],
                    'old_price' => round($item['price'] * 1.2, 2),
                    'image' => null,
                ]
            );

            $productMap[] = $product;
        }

        $customerNames = [
            'عبدالله السالم',
            'سارة المالكي',
            'حسن القحطاني',
            'ريم العتيبي',
            'أحمد الزهراني',
        ];

        foreach ($customerNames as $index => $customerName) {
            $order = Order::create([
                'user_id' => $admin->id,
                'customer_name' => $customerName,
                'customer_phone' => '966500000' . (100 + $index),
                'customer_email' => strtolower(str_replace(' ', '', $customerName)) . '@mail.com',
                'customer_address' => 'الرياض، حي النخيل، الشارع الرئيسي',
                'total_amount' => 0,
                'payment_method' => ['cash', 'card', 'bank_transfer'][$index % 3],
                'notes' => 'طلب تجريبي للنظام',
                'status' => ['pending', 'completed', 'processing', 'shipped'][$index % 4],
                'is_paid' => $index % 2 === 0,
            ]);

            $subtotal = 0;
            for ($i = 0; $i < 2; $i++) {
                $product = $productMap[($index + $i) % count($productMap)];
                $quantity = $i + 1;
                $itemSubtotal = $product->price * $quantity;
                $subtotal += $itemSubtotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'subtotal' => $itemSubtotal,
                ]);
            }

            $order->update(['total_amount' => $subtotal]);
        }
    }
}
