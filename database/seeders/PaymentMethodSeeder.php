<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['code' => 'cash', 'name' => 'الدفع عند الاستلام', 'account_number' => null, 'background_color' => '#E8F5E9', 'text_color' => '#1B5E20', 'requires_receipt' => false, 'sort_order' => 1],
            ['code' => 'jaib', 'name' => 'جيب', 'account_number' => '776870051', 'background_color' => '#FFF4D6', 'text_color' => '#5D4037', 'requires_receipt' => true, 'sort_order' => 2],
            ['code' => 'one_cash', 'name' => 'ون كاش', 'account_number' => '776870051', 'background_color' => '#E3F2FD', 'text_color' => '#0D47A1', 'requires_receipt' => false, 'sort_order' => 3],
            ['code' => 'jawali', 'name' => 'جوالي', 'account_number' => '776870051', 'background_color' => '#F3E5F5', 'text_color' => '#4A148C', 'requires_receipt' => false, 'sort_order' => 4],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(['code' => $method['code']], $method);
        }
    }
}
