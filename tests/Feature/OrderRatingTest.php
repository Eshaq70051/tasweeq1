<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_rate_a_delivered_order(): void
    {
        $customer = User::factory()->create();
        $captain = User::factory()->create(['role' => 'captain']);

        $order = Order::create([
            'user_id' => $customer->id,
            'assigned_captain_id' => $captain->id,
            'customer_name' => 'Test User',
            'customer_phone' => '0500000000',
            'customer_address' => 'Address',
            'total_amount' => 120.00,
            'captain_commission' => 10.00,
            'payment_method' => 'cash',
            'status' => 'delivered',
            'is_paid' => true,
        ]);

        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/orders/' . $order->id . '/rating', [
                'rating' => 5,
                'comment' => 'Excellent delivery',
            ])
            ->assertStatus(201)
            ->assertJsonPath('success', true);
    }
}
