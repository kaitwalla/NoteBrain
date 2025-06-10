<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_articles()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id, 'status' => 'inbox']);

        $response = $this->actingAs($user)->get(route('articles.index'));

        $response->assertStatus(200);
        $response->assertViewHas('articles');
        $response->assertSee($article->title);
    }

    public function test_create_displays_form()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('articles.create'));

        $response->assertStatus(200);
    }

    public function test_store_creates_article()
    {
        $user = User::factory()->create();
        $articleData = [
            'url' => 'https://example.com',
            'summarize' => false,
        ];

        $response = $this->actingAs($user)->post(route('articles.store'), $articleData);

        $response->assertRedirect();
        $this->assertDatabaseHas('articles', [
            'url' => 'https://example.com',
            'user_id' => $user->id,
        ]);
    }

    public function test_show_displays_article()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('articles.show', $article));

        $response->assertStatus(200);
        $response->assertViewHas('article');
        $response->assertSee($article->title);
    }

    public function test_archive_archives_article()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id, 'status' => 'inbox']);

        $response = $this->actingAs($user)->post(route('articles.archive', $article));

        $response->assertRedirect();
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => 'archived',
        ]);
    }

    public function test_inbox_moves_article_to_inbox()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id, 'status' => 'archived']);

        $response = $this->actingAs($user)->post(route('articles.inbox', $article));

        $response->assertRedirect();
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => 'inbox',
        ]);
    }

    public function test_keep_unread_keeps_article_unread()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id, 'status' => 'archived']);

        $response = $this->actingAs($user)->post(route('articles.keep-unread', $article));

        $response->assertJson([
            'message' => 'Article kept unread',
        ]);
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => 'inbox',
            'read_at' => null,
        ]);
    }

    public function test_summarize_summarizes_article()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id, 'status' => 'inbox']);

        // Mock the ArticleSummarizer service
        $this->mock(\App\Services\ArticleSummarizer::class, function ($mock) {
            $mock->shouldReceive('summarize')->andReturn('Test summary');
        });

        $response = $this->actingAs($user)->post(route('articles.summarize', $article));

        $response->assertRedirect();
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'summary' => 'Test summary',
        ]);
    }

    public function test_destroy_deletes_article()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('articles.destroy', $article));

        $response->assertRedirect();
        $this->assertDatabaseMissing('articles', [
            'id' => $article->id,
        ]);
    }
}
