<?php

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Services\GoogleDriveService;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use League\HTMLToMarkdown\HtmlConverter;
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

        // Mock the HtmlConverter
        $this->mockConverter = Mockery::mock(HtmlConverter::class);

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
            'content' => '<p>This is a <strong>test</strong> article content.</p>',
            'url' => 'https://example.com/test-article',
            'author' => 'Test Author',
            'site_name' => 'Test Site',
        ]);

        // Mock the HTML to Markdown conversion
        $markdownContent = 'This is a **test** article content.';
        $this->instance(HtmlConverter::class, $this->mockConverter);
        $this->mockConverter->shouldReceive('setOptions')
            ->once()
            ->with(['strip_tags' => true])
            ->andReturnSelf();
        $this->mockConverter->shouldReceive('convert')
            ->once()
            ->with($article->content)
            ->andReturn($markdownContent);

        // Set up the mock response
        $mockFile = new DriveFile();
        $mockFile->setId('test_file_id_123');

        // Set up expectations
        $this->mockFiles->shouldReceive('create')
            ->once()
            ->withArgs(function ($fileMetadata, $options) use ($article, $markdownContent) {
                // Verify file metadata
                $isCorrectName = $fileMetadata['name'] === $article->title;
                $isCorrectMimeType = $fileMetadata['mimeType'] === 'application/vnd.google-apps.document';
                $containsArticleUrl = strpos($fileMetadata['description'], $article->url) !== false;
                $hasCorrectParent = isset($fileMetadata['parents']) && $fileMetadata['parents'][0] === config('services.google.folder_id');

                // Verify options
                $hasCorrectData = strpos($options['data'], $article->title) !== false
                    && strpos($options['data'], $markdownContent) !== false;
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
            'content' => '<p>This is a <strong>test</strong> article content.</p>',
        ]);

        // Mock the HTML to Markdown conversion
        $markdownContent = 'This is a **test** article content.';
        $this->instance(HtmlConverter::class, $this->mockConverter);
        $this->mockConverter->shouldReceive('setOptions')
            ->once()
            ->with(['strip_tags' => true])
            ->andReturnSelf();
        $this->mockConverter->shouldReceive('convert')
            ->once()
            ->with($article->content)
            ->andReturn($markdownContent);

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
