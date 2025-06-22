<?php

namespace App\Services;

use App\Models\Article;
use OpenAI\Laravel\Facades\OpenAI;

class ArticleSummarizer
{
    public function summarize(Article $article): string
    {
        $response = OpenAI::chat()->create([
            'model' => 'o4-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant that summarizes articles. Your goal is to capture all the important ideas and insights from the article, even if that means the summary is longer than usual. Focus on expressing the complete thoughts and arguments rather than being concise. Include key quotes and examples that illustrate the main points. Just provide the summary, do not provide a preamble explaining what it is.'
                ],
                [
                    'role' => 'user',
                    'content' => "Please provide a comprehensive summary of this article:\n\nTitle: {$article->title}\n\nContent: {$article->content}"
                ]
            ],
            'max_completion_tokens' => 4000,
        ]);

        $plainTextSummary = $response->choices[0]->message->content;

        // Convert plain text to HTML
        return $this->formatTextAsHtml($plainTextSummary);
    }

    /**
     * Convert plain text to HTML format
     *
     * @param string $text The plain text to convert
     * @return string HTML formatted text
     */
    private function formatTextAsHtml(string $text): string
    {
        // Normalize line endings
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Split text into paragraphs (double newlines)
        $paragraphs = preg_split('/\n\s*\n/', $text, -1, PREG_SPLIT_NO_EMPTY);

        $html = '';
        foreach ($paragraphs as $paragraph) {
            // Trim whitespace
            $paragraph = trim($paragraph);

            // Skip empty paragraphs
            if (empty($paragraph)) {
                continue;
            }

            // Check if paragraph is a list
            if (preg_match('/^(\d+\.\s|[-*•]\s)/m', $paragraph)) {
                // Convert numbered lists
                if (preg_match('/^\d+\.\s/m', $paragraph)) {
                    $items = preg_split('/\n/', $paragraph, -1, PREG_SPLIT_NO_EMPTY);
                    $listItems = '';

                    foreach ($items as $item) {
                        $item = preg_replace('/^\d+\.\s/', '', $item);
                        $listItems .= "<li>" . $item . "</li>";
                    }

                    $html .= "<ol>" . $listItems . "</ol>";
                }
                // Convert bullet lists
                else {
                    $items = preg_split('/\n/', $paragraph, -1, PREG_SPLIT_NO_EMPTY);
                    $listItems = '';

                    foreach ($items as $item) {
                        $item = preg_replace('/^[-*•]\s/', '', $item);
                        $listItems .= "<li>" . $item . "</li>";
                    }

                    $html .= "<ul>" . $listItems . "</ul>";
                }
            }
            // Regular paragraph
            else {
                // Convert single newlines to <br> tags
                $paragraph = nl2br($paragraph);
                $html .= "<p>" . $paragraph . "</p>";
            }
        }

        return $html;
    }
}
