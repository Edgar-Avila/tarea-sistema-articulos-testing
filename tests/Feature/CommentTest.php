<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_comments(): void
    {
        $user = User::factory()->testUser()->create();
        $post = Post::factory()->for($user)->create();
        Comment::factory()->count(5)->for($user)->for($post)->create();

        $response = $this->actingAs($user)->getJson("/api/comments");

        $response->assertStatus(200);
        $response->assertJsonCount(5);
    }

    public function test_guest_can_not_see_comments(): void
    {
        $response = $this->getJson("/api/comments");

        $response->assertStatus(401);
    }

    public function test_user_can_see_comment(): void
    {
        $user = User::factory()->testUser()->create();
        $post = Post::factory()->for($user)->create();
        $comment = Comment::factory()->for($user)->for($post)->create();

        $response = $this->actingAs($user)->getJson("/api/comments/{$comment->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $comment->id,
            'text' => $comment->text,
        ]);
    }

    public function test_guest_can_not_see_comment(): void
    {
        $user = User::factory()->testUser()->create();
        $post = Post::factory()->for($user)->create();
        $comment = Comment::factory()->for($user)->for($post)->create();

        $response = $this->getJson("/api/comments/{$comment->id}");

        $response->assertStatus(401);
    }

    public function test_user_can_create_comment(): void
    {
        $user = User::factory()->testUser()->create();
        $post = Post::factory()->for($user)->create();

        $response = $this->actingAs($user)->postJson("/api/comments", [
            'text' => 'Test text',
            'post_id' => $post->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('comments', [
            'text' => 'Test text',
            'post_id' => $post->id,
        ]);
    }

    public function test_user_can_not_create_comment_without_text(): void
    {
        $user = User::factory()->testUser()->create();
        $post = Post::factory()->for($user)->create();

        $response = $this->actingAs($user)->postJson("/api/comments", [
            'post_id' => $post->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_not_create_comment_without_post_id(): void
    {
        $user = User::factory()->testUser()->create();

        $response = $this->actingAs($user)->postJson("/api/comments", [
            'text' => 'Test text',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_not_comment_nonexistent_post(): void
    {
        $user = User::factory()->testUser()->create();

        $response = $this->actingAs($user)->postJson("/api/comments", [
            'text' => 'Test text',
            'post_id' => 1,
        ]);

        $response->assertStatus(422);
    }

    public function test_guest_can_not_create_comment(): void
    {
        $response = $this->postJson("/api/comments", [
            'text' => 'Test text',
            'post_id' => 1,
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_update_comment(): void
    {
        $user = User::factory()->testUser()->create();
        $post = Post::factory()->for($user)->create();
        $comment = Comment::factory()->for($user)->for($post)->create();

        $response = $this->actingAs($user)->putJson("/api/comments/{$comment->id}", [
            'text' => 'Updated text',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'text' => 'Updated text',
        ]);
    }

    public function test_user_can_not_update_other_user_comment(): void
    {
        $user = User::factory()->testUser()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->for($otherUser)->create();
        $comment = Comment::factory()->for($otherUser)->for($post)->create();

        $response = $this->actingAs($user)->putJson("/api/comments/{$comment->id}", [
            'text' => 'Updated text',
        ]);

        $response->assertStatus(403);
    }

    public function test_guest_can_not_update_comment(): void
    {
        $user = User::factory()->testUser()->create();
        $post = Post::factory()->for($user)->create();
        $comment = Comment::factory()->for($user)->for($post)->create();

        $response = $this->putJson("/api/comments/{$comment->id}", [
            'text' => 'Updated text',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_delete_comment(): void
    {
        $user = User::factory()->testUser()->create();
        $post = Post::factory()->for($user)->create();
        $comment = Comment::factory()->for($user)->for($post)->create();

        $response = $this->actingAs($user)->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }

    public function test_user_can_not_delete_other_user_comment(): void
    {
        $user = User::factory()->testUser()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->for($otherUser)->create();
        $comment = Comment::factory()->for($otherUser)->for($post)->create();

        $response = $this->actingAs($user)->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(403);
    }

    public function test_guest_can_not_delete_comment(): void
    {
        $user = User::factory()->testUser()->create();
        $post = Post::factory()->for($user)->create();
        $comment = Comment::factory()->for($user)->for($post)->create();

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(401);
    }

}
