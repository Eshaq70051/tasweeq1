<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'إلكترونيات', 'description' => 'هواتف، أجهزة، سماعات وملحقات إلكترونية'],
            ['name' => 'ملابس', 'description' => 'ملابس رجالية ونسائية وأطفال'],
            ['name' => 'مستحضرات', 'description' => 'العناية بالبشرة والشعر والجسم'],
            ['name' => 'منزل', 'description' => 'مستلزمات وأدوات المنزل'],
            ['name' => 'مواد غذائية', 'description' => 'الأغذية والمشروبات والمواد الأساسية'],
            ['name' => 'عطور', 'description' => 'العطور والبخور ومعطرات الجسم والمنزل'],
            ['name' => 'أحذية وحقائب', 'description' => 'الأحذية والحقائب والمحافظ'],
            ['name' => 'إكسسوارات', 'description' => 'الساعات والمجوهرات والإكسسوارات'],
            ['name' => 'أطفال', 'description' => 'ملابس وألعاب ومستلزمات الأطفال'],
            ['name' => 'رياضة', 'description' => 'الملابس والأدوات والمستلزمات الرياضية'],
            ['name' => 'مكتبية', 'description' => 'القرطاسية والأدوات المكتبية'],
            ['name' => 'سيارات', 'description' => 'إكسسوارات واحتياجات السيارات'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
