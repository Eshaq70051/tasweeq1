<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CategorySeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(PaymentMethodSeeder::class);
        $this->call(AdminDashboardSeeder::class);
        $this->call(EshaqFullImportSeeder::class);
        $this->call(EshaqMeatsFishSeeder::class);
        $this->call(EshaqBakeriesSeeder::class);
        $this->call(EshaqGiftsStationerySeeder::class);
        $this->call(EshaqCosmeticsSeeder::class);
        $this->call(EshaqModemsSeeder::class);
        $this->call(EshaqOnlineStoresSeeder::class);
        $this->call(EshaqPharmaSeeder::class);
        $this->call(EshaqPhonesElectronicsSeeder::class);
    }
}
