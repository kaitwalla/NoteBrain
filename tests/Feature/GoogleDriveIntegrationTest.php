<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Services\GoogleDriveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GoogleDriveIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the GoogleDriveService
        $this->mockGoogleDriveService = Mockery::mock(GoogleDriveService::class);
        $this->app->instance(GoogleDriveService::class, $this->mockGoogleDriveService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_article_is_saved_to_google_drive_when_created()
    {
        // Create a user
        $user = User::factory()->create();

        // Set up the mock expectations
        $this->mockGoogleDriveService->shouldReceive('saveArticleText')
            ->once()
            ->andReturn('test_file_id_123');

        // Make the request to create an article
        $response = $this->actingAs($user)
            ->post(route('articles.store'), [
                'url' => 'https://example.com/test-article',
                'summarize' => false,
            ]);

        // Assert the response
        $response->assertRedirect();

        // Assert that the article was created with the Google Drive file ID
        $this->assertDatabaseHas('articles', [
            'url' => 'https://example.com/test-article',
            'google_drive_file_id' => 'test_file_id_123',
        ]);
    }

    public function test_api_article_is_saved_to_google_drive_when_created()
    {
        // Create a user
        $user = User::factory()->create();

        // Set up the mock expectations
        $this->mockGoogleDriveService->shouldReceive('saveArticleText')
            ->once()
            ->andReturn('test_file_id_456');

        // Make the API request to create an article
        $response = $this->actingAs($user)
            ->postJson('/api/articles', [
                'url' => 'https://example.com/api-test-article',
                'summarize' => false,
            ]);

        // Assert the response
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Article created successfully',
            ]);

        // Assert that the article was created with the Google Drive file ID
        $this->assertDatabaseHas('articles', [
            'url' => 'https://example.com/api-test-article',
            'google_drive_file_id' => 'test_file_id_456',
        ]);
    }

    public function test_article_is_created_even_if_google_drive_save_fails()
    {
        // Create a user
        $user = User::factory()->create();

        // Set up the mock expectations to simulate a failure
        $this->mockGoogleDriveService->shouldReceive('saveArticleText')
            ->once()
            ->andReturn(null);

        // Make the request to create an article
        $response = $this->actingAs($user)
            ->post(route('articles.store'), [
                'url' => 'https://example.com/test-article-fail',
                'summarize' => false,
            ]);

        // Assert the response
        $response->assertRedirect();

        // Assert that the article was created without a Google Drive file ID
        $this->assertDatabaseHas('articles', [
            'url' => 'https://example.com/test-article-fail',
            'google_drive_file_id' => null,
        ]);
    }
}
