<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
    }

    public function test_user_can_not_pick_same_email(): void
    {
        User::factory()->testUser()->create();

        $response = $this->postJson('/api/register', [
            'name' => 'Joth Doe',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_login(): void
    {
        User::factory()->testUser()->create();

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
    }

    public function test_nonexistent_user_can_not_login(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'fakeuser@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_not_login_with_wrong_password(): void
    {
        User::factory()->testUser()->create();

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->testUser()->create();
        $response = $this->actingAs($user)->postJson('/api/logout');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
