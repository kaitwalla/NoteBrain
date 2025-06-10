<?php

namespace Tests\Feature\Api;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_article()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $articleData = [
            'url' => 'https://example.com',
            'summarize' => false,
        ];

        // Mock the ArticleSummarizer service
        $this->mock(\App\Services\ArticleSummarizer::class, function ($mock) {
            $mock->shouldReceive('summarize')->andReturn('Test summary');
        });

        $response = $this->postJson('/api/articles', $articleData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Article created successfully',
            ]);

        $this->assertDatabaseHas('articles', [
            'url' => 'https://example.com',
            'user_id' => $user->id,
        ]);
    }

    public function test_store_with_summarize_creates_article_with_summary()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $articleData = [
            'url' => 'https://example.com',
            'summarize' => true,
        ];

        // Mock the ArticleSummarizer service
        $this->mock(\App\Services\ArticleSummarizer::class, function ($mock) {
            $mock->shouldReceive('summarize')->andReturn('Test summary');
        });

        $response = $this->postJson('/api/articles', $articleData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Article created successfully',
            ]);

        $this->assertDatabaseHas('articles', [
            'url' => 'https://example.com',
            'user_id' => $user->id,
            'summary' => 'Test summary',
        ]);
    }

    public function test_store_requires_authentication()
    {
        $articleData = [
            'url' => 'https://example.com',
            'summarize' => false,
        ];

        $response = $this->postJson('/api/articles', $articleData);

        $response->assertStatus(401);
    }

    public function test_keep_unread_keeps_article_unread()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id, 'status' => 'archived']);
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/articles/{$article->id}/keep-unread");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Article kept unread',
            ]);

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => 'inbox',
            'read_at' => null,
        ]);
    }

    public function test_read_marks_article_as_read()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id, 'status' => 'inbox']);
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/articles/{$article->id}/read");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Article marked as read',
            ]);

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => 'archived',
        ]);
    }

    public function test_summarize_summarizes_article()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id, 'status' => 'inbox']);
        Sanctum::actingAs($user);

        // Mock the ArticleSummarizer service
        $this->mock(\App\Services\ArticleSummarizer::class, function ($mock) {
            $mock->shouldReceive('summarize')->andReturn('Test summary');
        });

        $response = $this->postJson("/api/articles/{$article->id}/summarize");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Article summarized successfully',
            ]);

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'summary' => 'Test summary',
        ]);
    }
}
