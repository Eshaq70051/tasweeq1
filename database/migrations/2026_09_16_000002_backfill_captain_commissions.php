<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rate = (float) env('CAPTAIN_COMMISSION_RATE', 10);

        DB::table('orders')
            ->where('status', 'completed')
            ->where('captain_commission', 0)
            ->update([
                'captain_commission' => DB::raw("ROUND(total_amount * {$rate} / 100, 2)"),
            ]);
    }

    public function down(): void
    {
        DB::table('orders')
            ->where('status', 'completed')
            ->update(['captain_commission' => 0]);
    }
};
