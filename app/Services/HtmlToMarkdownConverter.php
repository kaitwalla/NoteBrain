<?php

namespace App\Services;

use League\HTMLToMarkdown\HtmlConverter;

class HtmlToMarkdownConverter
{
    /**
     * Convert HTML content to Markdown.
     *
     * @param string|null $html The HTML content to convert
     * @return string|null The Markdown content or null if the input is null
     */
    public function convert(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        // Create a new HTML to Markdown converter
        $converter = new HtmlConverter();

        // Set options for the converter
        $converter->setOptions([
            'strip_tags' => true, // Remove any remaining HTML tags
            'header_style' => 'atx', // Use # style headers
            'bold_style' => '**', // Use ** for bold
            'italic_style' => '_', // Use _ for italic
            'hard_break' => true, // Use hard breaks
        ]);

        // Convert HTML to Markdown
        $markdown = $converter->convert($html);

        // Return the Markdown content
        return $markdown;
    }
}
