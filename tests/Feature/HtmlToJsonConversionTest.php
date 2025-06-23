<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Services\HtmlToJsonConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HtmlToJsonConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_html_to_json_conversion_on_article_save()
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

        // Verify that the JSON fields were populated
        $this->assertNotNull($article->content_json);
        $this->assertIsArray($article->content_json);
        $this->assertCount(2, $article->content_json);
        $this->assertEquals('This is a test paragraph.', $article->content_json[0]);
        $this->assertEquals('This is another paragraph.', $article->content_json[1]);

        $this->assertNotNull($article->excerpt_json);
        $this->assertIsArray($article->excerpt_json);
        $this->assertCount(1, $article->excerpt_json);
        $this->assertEquals('This is a test excerpt.', $article->excerpt_json[0]);
    }

    public function test_html_to_json_conversion_on_article_update()
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

        // Verify that the JSON fields were updated
        $this->assertNotNull($article->content_json);
        $this->assertIsArray($article->content_json);
        $this->assertCount(2, $article->content_json);
        $this->assertEquals('Updated content.', $article->content_json[0]);
        $this->assertEquals('New paragraph.', $article->content_json[1]);
    }

    public function test_html_to_json_conversion_with_complex_html()
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

        // Verify that the JSON fields were populated correctly
        $this->assertNotNull($article->content_json);
        $this->assertIsArray($article->content_json);

        // The exact structure might vary depending on the HTML parser,
        // but we should have at least 5 items (title, paragraph, 2 list items, quote)
        $this->assertGreaterThanOrEqual(5, count($article->content_json));

        // Check for some expected content
        $this->assertTrue(in_array('Article Title', $article->content_json) ||
                         in_array('Article Title', array_map('trim', $article->content_json)));
        $this->assertTrue(in_array('This is a quote.', $article->content_json) ||
                         in_array('This is a quote.', array_map('trim', $article->content_json)));
    }
}
