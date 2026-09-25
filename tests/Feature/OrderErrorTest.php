<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderErrorTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_fails_when_product_does_not_exist(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => 999999,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'items.0.product_id',
        ]);
    }

    public function test_order_fails_when_quantity_is_invalid(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'quantity' => 10,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 0,
                ],
            ],
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'items.0.quantity',
        ]);
    }

    public function test_order_requires_items(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders', []);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'items',
        ]);
    }

    public function test_order_cannot_be_created_with_empty_items(): void
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

    public function test_order_fails_when_stock_is_insufficient(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'quantity' => 2,
            'price' => 100,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                ],
            ],
        ]);

        $response->assertStatus(422);

        $response->assertJson([
            'success' => false,
            'message' => "Insufficient stock for {$product->name}",
        ]);

        $this->assertDatabaseMissing('orders', [
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_unauthenticated_user_cannot_create_order(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_user_cannot_cancel_completed_order(): void
    {
        $user = User::factory()->create();

        $order = \App\Models\Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(403);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);
    }

    public function test_user_cannot_cancel_cancelled_order(): void
    {
        $user = User::factory()->create();

        $order = \App\Models\Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'cancelled',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(403);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_regular_user_cannot_complete_order(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $order = \App\Models\Order::factory()->create([
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

    public function test_user_cannot_view_another_users_order(): void
    {
        $user = User::factory()->create();

        $anotherUser = User::factory()->create();

        $order = \App\Models\Order::factory()->create([
            'user_id' => $anotherUser->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(403);
    }

    public function test_user_cannot_cancel_another_users_order(): void
    {
        $user = User::factory()->create();

        $anotherUser = User::factory()->create();

        $order = \App\Models\Order::factory()->create([
            'user_id' => $anotherUser->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(403);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending',
        ]);
    }
}