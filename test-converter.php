<?php

require __DIR__ . '/vendor/autoload.php';

use App\Services\HtmlToJsonConverter;

// Create a new instance of the converter
$converter = new HtmlToJsonConverter();

// Test with simple paragraphs
$html1 = '
    <p>This is the first paragraph.</p>
    <p>This is the second paragraph.</p>
    <p>This is the third paragraph.</p>
';

$result1 = $converter->convert($html1);
echo "Test 1: Simple paragraphs\n";
echo "Expected count: 3\n";
echo "Actual count: " . count($result1) . "\n";
echo "Result: " . json_encode($result1, JSON_PRETTY_PRINT) . "\n\n";

// Test with headings and paragraphs
$html2 = '
    <h1>This is a heading</h1>
    <p>This is a paragraph.</p>
    <h2>This is another heading</h2>
    <p>This is another paragraph.</p>
';

$result2 = $converter->convert($html2);
echo "Test 2: Headings and paragraphs\n";
echo "Expected count: 4\n";
echo "Actual count: " . count($result2) . "\n";
echo "Result: " . json_encode($result2, JSON_PRETTY_PRINT) . "\n\n";

// Test with nested paragraphs
$html3 = '
    <div>
        <p>This is a paragraph inside a div.</p>
        <p>This is another paragraph inside the same div.</p>
    </div>
    <p>This is a paragraph outside the div.</p>
';

$result3 = $converter->convert($html3);
echo "Test 3: Nested paragraphs\n";
echo "Expected count: 3\n";
echo "Actual count: " . count($result3) . "\n";
echo "Result: " . json_encode($result3, JSON_PRETTY_PRINT) . "\n\n";

// Test with mixed content
$html4 = '
    <h1>Article Title</h1>
    <p>First paragraph.</p>
    <div>
        <h2>Section Title</h2>
        <p>Second paragraph.</p>
        <ul>
            <li>List item 1</li>
            <li>List item 2</li>
        </ul>
        <p>Third paragraph.</p>
    </div>
    <p>Fourth paragraph.</p>
';

$result4 = $converter->convert($html4);
echo "Test 4: Mixed content\n";
echo "Expected count: 8\n";
echo "Actual count: " . count($result4) . "\n";
echo "Result: " . json_encode($result4, JSON_PRETTY_PRINT) . "\n\n";
