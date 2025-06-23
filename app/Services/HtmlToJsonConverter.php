<?php

namespace App\Services;

use DOMDocument;
use DOMNode;

class HtmlToJsonConverter
{
    /**
     * Convert HTML content to a JSON array of text.
     *
     * @param string|null $html The HTML content to convert
     * @return array|null The JSON array of text or null if the input is null
     */
    public function convert(?string $html): ?array
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        // Create a new DOM document
        $dom = new DOMDocument();

        // Suppress errors from malformed HTML
        libxml_use_internal_errors(true);

        // Load the HTML content
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        // Clear any errors
        libxml_clear_errors();

        // Get the body element
        $body = $dom->getElementsByTagName('body')->item(0);

        // If there's no body, return null
        if (!$body) {
            return null;
        }

        // Extract text from the body
        return $this->extractTextFromNode($body);
    }

    /**
     * Extract text from a DOM node and its children.
     *
     * @param DOMNode $node The DOM node to extract text from
     * @return array The array of text
     */
    private function extractTextFromNode(DOMNode $node): array
    {
        $result = [];

        // Process each child node
        foreach ($node->childNodes as $childNode) {
            // If it's a text node, add its content to the result
            if ($childNode->nodeType === XML_TEXT_NODE) {
                $text = trim($childNode->nodeValue);
                if ($text !== '') {
                    $result[] = $text;
                }
            }
            // If it's an element node, process it based on its tag name
            elseif ($childNode->nodeType === XML_ELEMENT_NODE) {
                $tagName = strtolower($childNode->nodeName);

                // For block-level elements, recursively extract text and add to result
                if (in_array($tagName, ['div', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'blockquote', 'pre', 'table', 'tr', 'td', 'th'])) {
                    $childText = $this->extractTextFromNode($childNode);
                    if (!empty($childText)) {
                        $result = array_merge($result, $childText);
                    }
                }
                // For inline elements, extract text and add to result
                else {
                    $text = trim($childNode->textContent);
                    if ($text !== '') {
                        $result[] = $text;
                    }
                }
            }
        }

        return $result;
    }
}
