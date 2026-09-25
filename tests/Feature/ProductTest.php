<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_user_can_view_products(): void
{
    $user = User::factory()->create();

    Product::factory()->count(3)->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/products');

    $response->assertStatus(200);
    $response->assertJsonCount(3, 'data');
}
    public function test_admin_can_create_product(): void
{
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $category = Category::factory()->create();

    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/products', [
        'name' => 'Test Product',
        'description' => 'Test product description',
        'price' => 150,
        'quantity' => 10,
        'category_id' => $category->id,
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('products', [
        'name' => 'Test Product',
        'price' => 150,
        'quantity' => 10,
        'category_id' => $category->id,
    ]);
}
public function test_user_cannot_create_product(): void
{
    $user = User::factory()->create([
        'role' => 'user',
    ]);

    $category = Category::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/products', [
        'name' => 'Test Product',
        'description' => 'Test product description',
        'price' => 150,
        'quantity' => 10,
        'category_id' => $category->id,
    ]);

    $response->assertStatus(403);

    $this->assertDatabaseMissing('products', [
        'name' => 'Test Product',
    ]);
}
public function test_admin_can_update_product(): void
{
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $category = Category::factory()->create();

    $product = Product::factory()->create([
        'name' => 'Old Product',
        'price' => 100,
        'category_id' => $category->id,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->putJson("/api/products/{$product->id}", [
        'name' => 'Updated Product',
        'description' => 'Updated description',
        'price' => 200,
        'quantity' => 20,
        'category_id' => $category->id,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Product',
        'price' => 200,
        'quantity' => 20,
    ]);
}
public function test_user_cannot_update_product(): void
{
    $user = User::factory()->create([
        'role' => 'user',
    ]);

    $product = Product::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/products/{$product->id}", [
        'name' => 'Hacked Product',
        'price' => 999,
    ]);

    $response->assertStatus(403);

    $this->assertDatabaseMissing('products', [
        'name' => 'Hacked Product',
    ]);
}
public function test_admin_can_delete_product(): void
{
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $product = Product::factory()->create();

    Sanctum::actingAs($admin);

    $response = $this->deleteJson("/api/products/{$product->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);
}
public function test_user_cannot_delete_product(): void
{
    $user = User::factory()->create([
        'role' => 'user',
    ]);

    $product = Product::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/products/{$product->id}");

    $response->assertStatus(403);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
    ]);
}
public function test_product_creation_requires_valid_data(): void
{
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/products', [
        'name' => '',
        'price' => -10,
        'quantity' => -1,
        'category_id' => 999999,
    ]);

    $response->assertStatus(422);

$response->assertJsonValidationErrors([
    'name',
]);
}
}
