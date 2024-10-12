<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_posts(): void
    {
        $user = User::factory()->testUser()->hasPosts(5)->create();
        $response = $this->actingAs($user)->getJson('/api/posts');

        $response->assertStatus(200);
        $response->assertJsonCount(5);
    }

    public function test_guest_can_not_see_posts(): void
    {
        $response = $this->getJson('/api/posts');

        $response->assertStatus(401);
    }

    public function test_user_can_see_post(): void
    {
        $user = User::factory()->testUser()->hasPosts(1)->create();
        $post = $user->posts->first();
        $response = $this->actingAs($user)->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $post->id,
            'title' => $post->title,
            'text' => $post->text,
        ]);
    }

    public function test_guest_can_not_see_post(): void
    {
        $user = User::factory()->testUser()->create();
        $post = Post::factory()->for($user)->create();
        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertStatus(401);
    }

    public function test_user_can_create_post(): void
    {
        $user = User::factory()->testUser()->create();
        $response = $this->actingAs($user)->postJson('/api/posts', [
            'title' => 'Test title',
            'text' => 'Test text',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('posts', [
            'title' => 'Test title',
            'text' => 'Test text',
        ]);
    }

    public function test_user_can_not_create_post_without_title(): void
    {
        $user = User::factory()->testUser()->create();
        $response = $this->actingAs($user)->postJson('/api/posts', [
            'text' => 'Test text',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_not_create_post_without_text(): void
    {
        $user = User::factory()->testUser()->create();
        $response = $this->actingAs($user)->postJson('/api/posts', [
            'title' => 'Test title',
        ]);

        $response->assertStatus(422);
    }

    public function test_guest_can_not_create_post(): void
    {
        $response = $this->postJson('/api/posts', [
            'title' => 'Test title',
            'text' => 'Test text',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_update_post(): void
    {
        $user = User::factory()->testUser()->create();
        $post = Post::factory()->for($user)->create();
        $response = $this->actingAs($user)->putJson("/api/posts/{$post->id}", [
            'title' => 'Updated title',
            'text' => 'Updated text',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated title',
            'text' => 'Updated text',
        ]);
    }

    public function test_user_can_not_update_other_users_post(): void
    {
        $user = User::factory()->testUser()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->for($otherUser)->create();
        $response = $this->actingAs($user)->putJson("/api/posts/{$post->id}", [
            'title' => 'Updated title',
            'text' => 'Updated text',
        ]);

        $response->assertStatus(403);
    }

    public function test_guest_can_not_update_post(): void
    {
        $user = User::factory()->testUser()->create();
        $post = Post::factory()->for($user)->create();
        $response = $this->putJson("/api/posts/{$post->id}", [
            'title' => 'Updated title',
            'text' => 'Updated text',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_delete_post(): void
    {
        $user = User::factory()->testUser()->create();
        $post = Post::factory()->for($user)->create();
        $response = $this->actingAs($user)->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    public function test_user_can_not_delete_other_users_post(): void
    {
        $user = User::factory()->testUser()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->for($otherUser)->create();
        $response = $this->actingAs($user)->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(403);
    }

    public function test_guest_can_not_delete_post(): void
    {
        $user = User::factory()->testUser()->create();
        $post = Post::factory()->for($user)->create();
        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(401);
    }
    
}
