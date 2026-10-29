<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_are_accessible_for_admin_user(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'admin',
            'is_admin' => true,
            'status' => 'active',
        ]);

        foreach (['/admin/dashboard', '/admin/products', '/admin/orders', '/admin/settings', '/admin/users', '/admin/captains'] as $route) {
            $response = $this->actingAs($admin)->get($route);
            $response->assertOk();
        }
    }

    public function test_admin_can_create_a_category_with_image(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin-category-image@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'admin',
            'is_admin' => true,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'أجهزة منزلية',
            'description' => 'أجهزة منزلية حديثة',
            'image' => UploadedFile::fake()->image('category.jpg', 600, 400),
        ]);

        $response->assertRedirect(route('admin.categories'));
        $category = Category::where('name', 'أجهزة منزلية')->first();
        $this->assertNotNull($category);
        $this->assertNotNull($category->image);
    }

    public function test_admin_can_manage_users_and_captains(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin-users@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'admin',
            'is_admin' => true,
            'status' => 'active',
        ]);

        $deliveryUser = User::create([
            'name' => 'Delivery User',
            'email' => 'delivery@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'user',
            'is_admin' => false,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.updateRole', $deliveryUser), [
            'role' => 'captain',
        ]);

        $response->assertRedirect(route('admin.users'));
        $this->assertDatabaseHas('users', ['id' => $deliveryUser->id, 'role' => 'captain']);

        $captainResponse = $this->actingAs($admin)->get(route('admin.captains'));
        $captainResponse->assertOk();
    }

    public function test_admin_can_create_a_product(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin2@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'admin',
            'is_admin' => true,
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'إلكترونيات',
            'description' => 'منتجات إلكترونية',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'منتج جديد',
            'price' => '150',
            'currency' => 'SAR',
            'details' => 'وصف المنتج الجديد',
            'category_id' => $category->id,
            'stock' => '20',
        ]);

        $response->assertRedirect(route('admin.products'));
        $this->assertDatabaseHas('products', ['name' => 'منتج جديد']);
    }

    public function test_admin_can_update_and_delete_a_product(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin3@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'admin',
            'is_admin' => true,
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'ملابس',
            'description' => 'ملابس رجالية ونسائية',
        ]);

        $product = \App\Models\Product::create([
            'name' => 'قميص قديم',
            'price' => 100,
            'currency' => 'SAR',
            'details' => 'وصف قديم',
            'category_id' => $category->id,
            'stock' => 5,
        ]);

        $updateResponse = $this->actingAs($admin)->post(route('admin.products.update', $product), [
            'name' => 'قميص جديد',
            'price' => '120',
            'currency' => 'SAR',
            'details' => 'وصف جديد',
            'category_id' => $category->id,
            'stock' => '10',
        ]);

        $updateResponse->assertRedirect(route('admin.products'));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'قميص جديد']);

        $deleteResponse = $this->actingAs($admin)->post(route('admin.products.delete', $product));
        $deleteResponse->assertRedirect(route('admin.products'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_admin_can_manage_order_status_and_payment(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin4@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'admin',
            'is_admin' => true,
            'status' => 'active',
        ]);

        $order = \App\Models\Order::create([
            'user_id' => $admin->id,
            'customer_name' => 'مستخدم تجريبي',
            'customer_phone' => '966500055555',
            'customer_address' => 'الرياض',
            'total_amount' => 150,
            'payment_method' => 'cash',
            'status' => 'pending',
            'is_paid' => false,
        ]);

        $statusResponse = $this->actingAs($admin)->post(route('admin.orders.updateStatus', $order), [
            'status' => 'processing',
        ]);
        $statusResponse->assertRedirect(route('admin.orders'));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'processing']);

        $paidResponse = $this->actingAs($admin)->post(route('admin.orders.markPaid', $order));
        $paidResponse->assertRedirect(route('admin.orders'));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'is_paid' => 1]);
    }

    public function test_admin_can_view_order_details(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin5@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'admin',
            'is_admin' => true,
            'status' => 'active',
        ]);

        $order = \App\Models\Order::create([
            'user_id' => $admin->id,
            'customer_name' => 'عميل التفاصيل',
            'customer_phone' => '966500077777',
            'customer_address' => 'جدة',
            'total_amount' => 320,
            'payment_method' => 'card',
            'status' => 'pending',
            'is_paid' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.orders.show', $order));
        $response->assertOk();
        $response->assertSee('عميل التفاصيل');
    }
}
