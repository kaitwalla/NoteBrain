<?php

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Services\GoogleDriveService;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GoogleDriveServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the Google_Client and Google_Service_Drive
        $this->mockClient = Mockery::mock('Google\Client');
        $this->mockDrive = Mockery::mock('Google\Service\Drive');
        $this->mockFiles = Mockery::mock('Google\Service\Drive\Resource\Files');

        // Set up the mock chain
        $this->mockDrive->files = $this->mockFiles;

        // Create a partial mock of the GoogleDriveService
        $this->service = Mockery::mock(GoogleDriveService::class)->makePartial();
        $this->service->shouldReceive('__construct')->andReturn(null);

        // Use reflection to set protected properties
        $reflection = new \ReflectionClass($this->service);

        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($this->service, $this->mockClient);

        $serviceProperty = $reflection->getProperty('service');
        $serviceProperty->setAccessible(true);
        $serviceProperty->setValue($this->service, $this->mockDrive);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_save_article_text_returns_file_id_on_success()
    {
        // Create a test article
        $article = Article::factory()->create([
            'title' => 'Test Article',
            'content' => 'This is a test article content.',
            'url' => 'https://example.com/test-article',
            'author' => 'Test Author',
            'site_name' => 'Test Site',
        ]);

        // Set up the mock response
        $mockFile = new DriveFile();
        $mockFile->setId('test_file_id_123');

        // Set up expectations
        $this->mockFiles->shouldReceive('create')
            ->once()
            ->withArgs(function ($fileMetadata, $options) use ($article) {
                // Verify file metadata
                $isCorrectName = $fileMetadata['name'] === $article->title . '.txt';
                $isCorrectMimeType = $fileMetadata['mimeType'] === 'text/plain';
                $containsArticleUrl = strpos($fileMetadata['description'], $article->url) !== false;
                $hasCorrectParent = isset($fileMetadata['parents']) && $fileMetadata['parents'][0] === config('services.google.folder_id');

                // Verify options
                $hasCorrectData = strpos($options['data'], $article->title) !== false
                    && strpos($options['data'], $article->content) !== false;
                $isCorrectMimeType = $options['mimeType'] === 'text/plain';
                $isCorrectUploadType = $options['uploadType'] === 'multipart';

                return $isCorrectName && $isCorrectMimeType && $containsArticleUrl && $hasCorrectParent
                    && $hasCorrectData && $isCorrectMimeType && $isCorrectUploadType;
            })
            ->andReturn($mockFile);

        // Call the method
        $fileId = $this->service->saveArticleText($article);

        // Assert the result
        $this->assertEquals('test_file_id_123', $fileId);
    }

    public function test_save_article_text_returns_null_on_error()
    {
        // Create a test article
        $article = Article::factory()->create([
            'title' => 'Test Article',
            'content' => 'This is a test article content.',
        ]);

        // Set up the mock to throw an exception
        $this->mockFiles->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('Test exception'));

        // Call the method
        $fileId = $this->service->saveArticleText($article);

        // Assert the result
        $this->assertNull($fileId);
    }
}
