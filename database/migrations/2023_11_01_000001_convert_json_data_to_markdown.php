<?php

use App\Models\Article;
use App\Models\Note;
use App\Services\HtmlToMarkdownConverter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create a new HTML to Markdown converter
        $markdownConverter = new HtmlToMarkdownConverter();

        // Convert existing articles' content from JSON arrays to Markdown
        $articles = Article::all();
        foreach ($articles as $article) {
            // Only convert if the content is in JSON format (array)
            if (is_array($article->content_json)) {
                // Convert the original HTML content to Markdown
                $article->content_json = $markdownConverter->convert($article->content);
            }

            // Only convert if the excerpt is in JSON format (array)
            if (is_array($article->excerpt_json)) {
                // Convert the original HTML excerpt to Markdown
                $article->excerpt_json = $markdownConverter->convert($article->excerpt);
            }

            // Only convert if the summary is in JSON format (array)
            if (is_array($article->summary_json)) {
                // Convert the original HTML summary to Markdown
                $article->summary_json = $markdownConverter->convert($article->summary);
            }

            $article->save();
        }

        // Convert existing notes' content from JSON arrays to Markdown
        $notes = Note::all();
        foreach ($notes as $note) {
            // Only convert if the content is in JSON format (array)
            if (is_array($note->content_json)) {
                // Convert the original HTML content to Markdown
                $note->content_json = $markdownConverter->convert($note->content);
            }

            $note->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be reversed as it would require converting Markdown back to JSON arrays
        // which is not a straightforward process. If needed, a backup of the database should be made
        // before running this migration.
    }
};
