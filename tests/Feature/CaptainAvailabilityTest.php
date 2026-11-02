<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaptainAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private function createCaptain(bool $isAvailable): User
    {
        return User::factory()->create([
            'role' => 'captain',
            'status' => 'active',
            'is_available' => $isAvailable,
        ]);
    }

    private function createOrder(array $attributes = []): Order
    {
        return Order::create(array_merge([
            'customer_name' => 'عميل تجريبي',
            'customer_phone' => '770000000',
            'customer_address' => 'صنعاء',
            'total_amount' => 500,
            'payment_method' => 'cash',
            'status' => 'pending',
        ], $attributes));
    }

    public function test_offline_captain_does_not_receive_available_orders(): void
    {
        $captain = $this->createCaptain(false);
        $order = $this->createOrder();

        $this->actingAs($captain, 'sanctum')
            ->getJson('/api/captain/dashboard')
            ->assertOk()
            ->assertJsonPath('data.available_orders', []);

        $captain->update(['is_available' => true]);

        $this->actingAs($captain, 'sanctum')
            ->getJson('/api/captain/dashboard')
            ->assertOk()
            ->assertJsonPath('data.available_orders.0.id', $order->id);
    }

    public function test_captain_cannot_close_availability_with_an_active_order(): void
    {
        $captain = $this->createCaptain(true);
        $this->createOrder([
            'assigned_captain_id' => $captain->id,
            'status' => 'on_the_way',
        ]);

        $this->actingAs($captain, 'sanctum')
            ->postJson('/api/captain/toggle-status', ['is_available' => false])
            ->assertStatus(409)
            ->assertJsonPath('is_available', true);

        $this->assertTrue($captain->fresh()->is_available);
    }

    public function test_captain_can_close_availability_after_completing_delivery(): void
    {
        $captain = $this->createCaptain(true);
        $this->createOrder([
            'assigned_captain_id' => $captain->id,
            'status' => 'completed',
        ]);

        $this->actingAs($captain, 'sanctum')
            ->postJson('/api/captain/toggle-status', ['is_available' => false])
            ->assertOk()
            ->assertJsonPath('is_available', false);

        $this->assertFalse($captain->fresh()->is_available);
    }

    public function test_offline_captain_cannot_accept_an_unassigned_order(): void
    {
        $captain = $this->createCaptain(false);
        $order = $this->createOrder();

        $this->actingAs($captain, 'sanctum')
            ->postJson("/api/captain/orders/{$order->id}/accept")
            ->assertForbidden();

        $this->assertNull($order->fresh()->assigned_captain_id);
    }

    public function test_admin_cannot_assign_an_order_to_an_offline_captain(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
            'status' => 'active',
        ]);
        $captain = $this->createCaptain(false);
        $order = $this->createOrder();

        $this->actingAs($admin, 'web')
            ->post(route('admin.orders.assignCaptain', $order), [
                'captain_id' => $captain->id,
            ])
            ->assertSessionHasErrors('captain_id');

        $this->assertNull($order->fresh()->assigned_captain_id);
    }

    public function test_admin_cannot_close_a_captain_with_an_active_order(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
            'status' => 'active',
        ]);
        $captain = $this->createCaptain(true);
        $this->createOrder([
            'assigned_captain_id' => $captain->id,
            'status' => 'processing',
        ]);

        $this->actingAs($admin, 'web')
            ->post(route('admin.captains.update', $captain), [
                'name' => $captain->name,
                'email' => $captain->email,
                'status' => 'active',
                'is_available' => false,
            ])
            ->assertSessionHasErrors('is_available');

        $this->assertTrue($captain->fresh()->is_available);
    }
}
