<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_categories(): void
    {
        $user = User::factory()->create();

        Category::factory()->count(3)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200);

        $response->assertJsonCount(3, 'data');
    }

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/categories', [
            'name' => 'Electronics',
            'description' => 'Electronic products',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('categories', [
            'name' => 'Electronics',
            'description' => 'Electronic products',
        ]);
    }

    public function test_user_cannot_create_category(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/categories', [
            'name' => 'Electronics',
            'description' => 'Electronic products',
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('categories', [
            'name' => 'Electronics',
        ]);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $category = Category::factory()->create([
            'name' => 'Old Category',
            'description' => 'Old description',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->putJson("/api/categories/{$category->id}", [
            'name' => 'Updated Category',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'description' => 'Updated description',
        ]);
    }

    public function test_user_cannot_update_category(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $category = Category::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/categories/{$category->id}", [
            'name' => 'Hacked Category',
            'description' => 'Hacked description',
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('categories', [
            'name' => 'Hacked Category',
        ]);
    }

    public function test_admin_can_delete_category(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $category = Category::factory()->create();

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_user_cannot_delete_category(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $category = Category::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_category_creation_requires_valid_data(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/categories', [
            'name' => '',
            'description' => '',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'name',
        ]);
    }

    public function test_user_can_view_single_category(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200);

        $response->assertJsonPath(
            'data.id',
            $category->id
        );
    }

    public function test_unauthenticated_user_cannot_access_categories(): void
    {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(401);
    }
}