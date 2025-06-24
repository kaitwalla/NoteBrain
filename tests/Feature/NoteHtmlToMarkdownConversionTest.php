<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use App\Services\HtmlToMarkdownConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteHtmlToMarkdownConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_html_to_markdown_conversion_on_note_save()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a note with HTML content
        $note = Note::create([
            'user_id' => $user->id,
            'title' => 'Test Note',
            'content' => '<p>This is a test paragraph.</p><p>This is another paragraph.</p>',
            'status' => Note::STATUS_INBOX,
        ]);

        // Verify that the Markdown field was populated
        $this->assertNotNull($note->content_json);
        $this->assertIsString($note->content_json);
        $this->assertStringContainsString('This is a test paragraph.', $note->content_json);
        $this->assertStringContainsString('This is another paragraph.', $note->content_json);
    }

    public function test_html_to_markdown_conversion_on_note_update()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a note with HTML content
        $note = Note::create([
            'user_id' => $user->id,
            'title' => 'Test Note',
            'content' => '<p>Initial content.</p>',
            'status' => Note::STATUS_INBOX,
        ]);

        // Update the note with new content
        $note->update([
            'content' => '<p>Updated content.</p><p>New paragraph.</p>',
        ]);

        // Refresh the note from the database
        $note->refresh();

        // Verify that the Markdown field was updated
        $this->assertNotNull($note->content_json);
        $this->assertIsString($note->content_json);
        $this->assertStringContainsString('Updated content.', $note->content_json);
        $this->assertStringContainsString('New paragraph.', $note->content_json);
    }

    public function test_html_to_markdown_conversion_with_complex_html()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a note with complex HTML content
        $note = Note::create([
            'user_id' => $user->id,
            'title' => 'Test Note',
            'content' => '
                <h1>Note Title</h1>
                <p>This is the first paragraph with <strong>bold</strong> and <em>italic</em> text.</p>
                <ul>
                    <li>List item 1</li>
                    <li>List item 2</li>
                </ul>
                <blockquote>This is a quote.</blockquote>
            ',
            'status' => Note::STATUS_INBOX,
        ]);

        // Verify that the Markdown field was populated correctly
        $this->assertNotNull($note->content_json);
        $this->assertIsString($note->content_json);

        // Check for expected Markdown content
        $this->assertStringContainsString('# Note Title', $note->content_json);
        $this->assertStringContainsString('This is the first paragraph with', $note->content_json);
        $this->assertStringContainsString('**bold**', $note->content_json);
        $this->assertStringContainsString('*italic*', $note->content_json);
        $this->assertStringContainsString('* List item 1', $note->content_json);
        $this->assertStringContainsString('* List item 2', $note->content_json);
        $this->assertStringContainsString('This is a quote.', $note->content_json);
    }
}
