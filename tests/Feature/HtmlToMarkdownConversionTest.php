<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Services\HtmlToMarkdownConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HtmlToMarkdownConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_html_to_markdown_conversion_on_article_save()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an article with HTML content
        $article = Article::create([
            'user_id' => $user->id,
            'url' => 'https://example.com',
            'title' => 'Test Article',
            'content' => '<p>This is a test paragraph.</p><p>This is another paragraph.</p>',
            'excerpt' => '<p>This is a test excerpt.</p>',
            'status' => Article::STATUS_INBOX,
        ]);

        // Verify that the Markdown fields were populated
        $this->assertNotNull($article->content_json);
        $this->assertIsString($article->content_json);
        $this->assertStringContainsString('This is a test paragraph.', $article->content_json);
        $this->assertStringContainsString('This is another paragraph.', $article->content_json);

        $this->assertNotNull($article->excerpt_json);
        $this->assertIsString($article->excerpt_json);
        $this->assertStringContainsString('This is a test excerpt.', $article->excerpt_json);
    }

    public function test_html_to_markdown_conversion_on_article_update()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an article with HTML content
        $article = Article::create([
            'user_id' => $user->id,
            'url' => 'https://example.com',
            'title' => 'Test Article',
            'content' => '<p>Initial content.</p>',
            'status' => Article::STATUS_INBOX,
        ]);

        // Update the article with new content
        $article->update([
            'content' => '<p>Updated content.</p><p>New paragraph.</p>',
        ]);

        // Refresh the article from the database
        $article->refresh();

        // Verify that the Markdown fields were updated
        $this->assertNotNull($article->content_json);
        $this->assertIsString($article->content_json);
        $this->assertStringContainsString('Updated content.', $article->content_json);
        $this->assertStringContainsString('New paragraph.', $article->content_json);
    }

    public function test_html_to_markdown_conversion_with_complex_html()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an article with complex HTML content
        $article = Article::create([
            'user_id' => $user->id,
            'url' => 'https://example.com',
            'title' => 'Test Article',
            'content' => '
                <h1>Article Title</h1>
                <p>This is the first paragraph with <strong>bold</strong> and <em>italic</em> text.</p>
                <ul>
                    <li>List item 1</li>
                    <li>List item 2</li>
                </ul>
                <blockquote>This is a quote.</blockquote>
            ',
            'status' => Article::STATUS_INBOX,
        ]);

        // Verify that the Markdown fields were populated correctly
        $this->assertNotNull($article->content_json);
        $this->assertIsString($article->content_json);

        // Check for expected Markdown content
        $this->assertStringContainsString('# Article Title', $article->content_json);
        $this->assertStringContainsString('This is the first paragraph with', $article->content_json);
        $this->assertStringContainsString('**bold**', $article->content_json);
        $this->assertStringContainsString('*italic*', $article->content_json);
        $this->assertStringContainsString('* List item 1', $article->content_json);
        $this->assertStringContainsString('* List item 2', $article->content_json);
        $this->assertStringContainsString('This is a quote.', $article->content_json);
    }
}
