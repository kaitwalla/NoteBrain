<?php

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Services\ArticleSummarizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Chat\CreateResponseChoice;
use OpenAI\Responses\Chat\CreateResponseMessage;
use Tests\TestCase;

class ArticleSummarizerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the summarize method formats text as HTML correctly.
     *
     * @return void
     */
    public function test_summarize_formats_text_as_html()
    {
        // Create a mock article
        $article = new Article([
            'title' => 'Test Article',
            'content' => 'Test content',
        ]);

        // Create a mock OpenAI response with plain text
        $plainTextSummary = "This is the first paragraph.\n\nThis is the second paragraph with a\nline break.";

        // Mock the OpenAI facade
        OpenAI::shouldReceive('chat->create')
            ->once()
            ->andReturn($this->createMockResponse($plainTextSummary));

        // Call the summarize method
        $summarizer = new ArticleSummarizer();
        $htmlSummary = $summarizer->summarize($article);

        // Assert that the summary has been formatted as HTML
        $this->assertStringContainsString('<p>This is the first paragraph.</p>', $htmlSummary);
        $this->assertStringContainsString('<p>This is the second paragraph with a<br>', $htmlSummary);
    }

    /**
     * Test that the summarize method handles lists correctly.
     *
     * @return void
     */
    public function test_summarize_formats_lists_as_html()
    {
        // Create a mock article
        $article = new Article([
            'title' => 'Test Article',
            'content' => 'Test content',
        ]);

        // Create a mock OpenAI response with a list
        $plainTextSummary = "Key points:\n\n1. First item\n2. Second item\n\n- Bullet one\n- Bullet two";

        // Mock the OpenAI facade
        OpenAI::shouldReceive('chat->create')
            ->once()
            ->andReturn($this->createMockResponse($plainTextSummary));

        // Call the summarize method
        $summarizer = new ArticleSummarizer();
        $htmlSummary = $summarizer->summarize($article);

        // Assert that the summary has been formatted as HTML with proper list tags
        $this->assertStringContainsString('<p>Key points:</p>', $htmlSummary);
        $this->assertStringContainsString('<ol><li>First item</li><li>Second item</li></ol>', $htmlSummary);
        $this->assertStringContainsString('<ul><li>Bullet one</li><li>Bullet two</li></ul>', $htmlSummary);
    }

    /**
     * Create a mock OpenAI response.
     *
     * @param string $content
     * @return \OpenAI\Responses\Chat\CreateResponse
     */
    private function createMockResponse(string $content)
    {
        $message = \Mockery::mock(CreateResponseMessage::class);
        $message->shouldReceive('__get')->with('content')->andReturn($content);

        $choice = \Mockery::mock(CreateResponseChoice::class);
        $choice->shouldReceive('__get')->with('message')->andReturn($message);

        $response = \Mockery::mock(CreateResponse::class);
        $response->shouldReceive('__get')->with('choices')->andReturn([$choice]);

        return $response;
    }
}
