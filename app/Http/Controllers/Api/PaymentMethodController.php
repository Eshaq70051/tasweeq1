<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Cache;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = Cache::remember('payment_methods.active', 600, function () {
            return PaymentMethod::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        });

        return response()->json([
            'success' => true,
            'data' => $methods,
        ])->header('Cache-Control', 'public, max-age=600');
    }
}
