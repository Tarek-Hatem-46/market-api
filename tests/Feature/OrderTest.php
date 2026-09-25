<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_order(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'quantity' => 10,
            'price' => 100,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
            'total_price' => 200,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 100,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'quantity' => 8,
        ]);
    }
    public function test_order_fails_when_stock_is_insufficient(): void
{
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'quantity' => 10,
        'price' => 100,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/orders', [
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 15,
            ],
        ],
    ]);

    $response->assertStatus(422);

    $this->assertDatabaseCount('orders', 0);

    $this->assertDatabaseCount('order_items', 0);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'quantity' => 10,
    ]);
}
public function test_regular_user_cannot_complete_order(): void
{
    $user = User::factory()->create([
        'role' => 'user',
    ]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/orders/{$order->id}/complete");

    $response->assertStatus(403);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'pending',
    ]);
}
public function test_admin_can_complete_order(): void
{
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $user = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    Sanctum::actingAs($admin);

    $response = $this->postJson("/api/orders/{$order->id}/complete");

    $response->assertStatus(200);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'completed',
    ]);
}
public function test_user_can_cancel_order(): void
{
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'quantity' => 8,
        'price' => 100,
    ]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => "pending",
        'total_price' => 200,
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 100,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson(
        "/api/orders/{$order->id}/cancel"
    );

    $response->assertStatus(200);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => "cancelled",
    ]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'quantity' => 10,
    ]);
}
public function test_user_cannot_cancel_another_users_order(): void
{
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $otherUser->id,
        'status' => 'pending',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson(
        "/api/orders/{$order->id}/cancel"
    );

    $response->assertStatus(403);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'pending',
    ]);
}
public function test_user_cannot_cancel_completed_order(): void
{
    $user = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'completed',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson(
        "/api/orders/{$order->id}/cancel"
    );

    $response->assertStatus(403);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'completed',
    ]);
}
public function test_user_cannot_cancel_cancelled_order(): void
{
    $user = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'cancelled',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson(
        "/api/orders/{$order->id}/cancel"
    );

    $response->assertStatus(403);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'cancelled',
    ]);
}
public function test_admin_can_view_any_order(): void
{
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $user = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->getJson(
        "/api/orders/{$order->id}"
    );

    $response->assertStatus(200);
}
public function test_user_can_view_own_order(): void
{
    $user = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson(
        "/api/orders/{$order->id}"
    );

    $response->assertStatus(200);
}
public function test_user_cannot_view_another_users_order(): void
{
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson(
        "/api/orders/{$order->id}"
    );

    $response->assertStatus(403);
}
public function test_user_can_view_own_orders(): void
{
    $user = User::factory()->create();

    Order::factory()->create([
        'user_id' => $user->id,
    ]);

    Order::factory()->create([
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/orders');

    $response->assertStatus(200);

    $response->assertJsonCount(2, 'data');
}
public function test_admin_can_view_all_orders(): void
{
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $user = User::factory()->create();

    Order::factory()->create([
        'user_id' => $user->id,
    ]);

    Order::factory()->create([
        'user_id' => $admin->id,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/orders');

    $response->assertStatus(200);

    $response->assertJsonCount(2, 'data');
}
public function test_user_can_filter_orders_by_status(): void
{
    $user = User::factory()->create();

    Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'completed',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/orders?status=completed');

    $response->assertStatus(200);

    $response->assertJsonCount(1, 'data');

    $response->assertJsonPath('data.0.status', 'completed');
}
public function test_orders_status_filter_rejects_invalid_status(): void
{
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/orders?status=invalid');

    $response->assertStatus(422);
}
public function test_user_can_set_orders_per_page(): void
{
    $user = User::factory()->create();

    Order::factory()->count(5)->create([
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/orders?per_page=2');

    $response->assertStatus(200);

    $response->assertJsonCount(2, 'data');
    $response->assertJsonPath('meta.per_page', 2);
}
public function test_unauthenticated_user_cannot_access_orders(): void
{
    $response = $this->getJson('/api/orders');

    $response->assertStatus(401);
}
public function test_user_cannot_create_order_with_invalid_data(): void
{
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/orders', [
        'items' => [],
    ]);

    $response->assertStatus(422);

    $response->assertJsonValidationErrors([
        'items',
    ]);
}
}