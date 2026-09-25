<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Register
    |--------------------------------------------------------------------------
    */

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Tarek',
            'email' => 'tarek@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'name' => 'Tarek',
            'email' => 'tarek@example.com',
        ]);

        $response->assertJsonStructure([
            'message',
            'data'=>[
            'user',
            'token',
            ]
        ]);
    }

    public function test_user_cannot_register_with_existing_email(): void
    {
        User::factory()->create([
            'email' => 'tarek@example.com',
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'Another User',
            'email' => 'tarek@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'email',
        ]);
    }

    public function test_user_cannot_register_with_invalid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'name',
            'email',
            'password',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'tarek@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'tarek@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'message',
            'data'=>[
            'user',
            'token',
            ]
        ]);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'tarek@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'tarek@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_cannot_login_with_nonexistent_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'notfound@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'logout successfully',
        ]);
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    /*
    |--------------------------------------------------------------------------
    | Protected Route
    |--------------------------------------------------------------------------
    */

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_access_protected_routes(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
    }
}

