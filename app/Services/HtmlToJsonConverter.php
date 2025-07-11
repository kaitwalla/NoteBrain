<?php

namespace App\Services;

use League\HTMLToMarkdown\HtmlConverter;

class HtmlToJsonConverter
{
    /**
     * Convert HTML content to Markdown.
     *
     * Note: This class has been updated to return Markdown content instead of a JSON array.
     * The method signature remains the same for backward compatibility, but the return type
     * is now a string (Markdown) instead of an array.
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
