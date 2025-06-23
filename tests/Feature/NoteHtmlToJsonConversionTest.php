<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use App\Services\HtmlToJsonConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteHtmlToJsonConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_html_to_json_conversion_on_note_save()
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

        // Verify that the JSON field was populated
        $this->assertNotNull($note->content_json);
        $this->assertIsArray($note->content_json);
        $this->assertCount(2, $note->content_json);
        $this->assertEquals('This is a test paragraph.', $note->content_json[0]);
        $this->assertEquals('This is another paragraph.', $note->content_json[1]);
    }

    public function test_html_to_json_conversion_on_note_update()
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

        // Verify that the JSON field was updated
        $this->assertNotNull($note->content_json);
        $this->assertIsArray($note->content_json);
        $this->assertCount(2, $note->content_json);
        $this->assertEquals('Updated content.', $note->content_json[0]);
        $this->assertEquals('New paragraph.', $note->content_json[1]);
    }

    public function test_html_to_json_conversion_with_complex_html()
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

        // Verify that the JSON field was populated correctly
        $this->assertNotNull($note->content_json);
        $this->assertIsArray($note->content_json);

        // The exact structure might vary depending on the HTML parser,
        // but we should have at least 5 items (title, paragraph, 2 list items, quote)
        $this->assertGreaterThanOrEqual(5, count($note->content_json));

        // Check for some expected content
        $this->assertTrue(in_array('Note Title', $note->content_json) ||
                         in_array('Note Title', array_map('trim', $note->content_json)));
        $this->assertTrue(in_array('This is a quote.', $note->content_json) ||
                         in_array('This is a quote.', array_map('trim', $note->content_json)));
    }
}
