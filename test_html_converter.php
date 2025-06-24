<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\HtmlToJsonConverter;

// Create a new instance of the HtmlToJsonConverter
$converter = new HtmlToJsonConverter();

// Test with simple HTML content
$html = '<p>This is a test paragraph.</p><p>This is another paragraph.</p>';
$json = $converter->convert($html);

echo "Test 1: Simple HTML\n";
echo "HTML: $html\n";
echo "JSON: " . json_encode($json, JSON_PRETTY_PRINT) . "\n\n";

// Test with complex HTML content
$html = '
    <h1>Article Title</h1>
    <p>This is the first paragraph with <strong>bold</strong> and <em>italic</em> text.</p>
    <ul>
        <li>List item 1</li>
        <li>List item 2</li>
    </ul>
    <blockquote>This is a quote.</blockquote>
';
$json = $converter->convert($html);

echo "Test 2: Complex HTML\n";
echo "HTML: $html\n";
echo "JSON: " . json_encode($json, JSON_PRETTY_PRINT) . "\n\n";

// Test with nested HTML content
$html = '
    <div>
        <p>This is a paragraph inside a div.</p>
        <p>This is another paragraph inside the same div.</p>
    </div>
';
$json = $converter->convert($html);

echo "Test 3: Nested HTML\n";
echo "HTML: $html\n";
echo "JSON: " . json_encode($json, JSON_PRETTY_PRINT) . "\n\n";
