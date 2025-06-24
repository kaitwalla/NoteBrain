<?php

namespace Tests\Unit\Services;

use App\Services\HtmlToJsonConverter;
use PHPUnit\Framework\TestCase;

class HtmlToJsonConverterTest extends TestCase
{
    /** @test */
    public function it_converts_paragraphs_to_markdown()
    {
        $converter = new HtmlToJsonConverter();

        $html = '
            <p>This is the first paragraph.</p>
            <p>This is the second paragraph.</p>
            <p>This is the third paragraph.</p>
        ';

        $result = $converter->convert($html);

        $this->assertIsString($result);
        $this->assertStringContainsString('This is the first paragraph.', $result);
        $this->assertStringContainsString('This is the second paragraph.', $result);
        $this->assertStringContainsString('This is the third paragraph.', $result);
    }

    /** @test */
    public function it_handles_headings_in_markdown()
    {
        $converter = new HtmlToJsonConverter();

        $html = '
            <h1>This is a heading</h1>
            <p>This is a paragraph.</p>
            <h2>This is another heading</h2>
            <p>This is another paragraph.</p>
        ';

        $result = $converter->convert($html);

        $this->assertIsString($result);
        $this->assertStringContainsString('# This is a heading', $result);
        $this->assertStringContainsString('This is a paragraph.', $result);
        $this->assertStringContainsString('## This is another heading', $result);
        $this->assertStringContainsString('This is another paragraph.', $result);
    }

    /** @test */
    public function it_handles_nested_paragraphs()
    {
        $converter = new HtmlToJsonConverter();

        $html = '
            <div>
                <p>This is a paragraph inside a div.</p>
                <p>This is another paragraph inside the same div.</p>
            </div>
            <p>This is a paragraph outside the div.</p>
        ';

        $result = $converter->convert($html);

        $this->assertIsString($result);
        $this->assertStringContainsString('This is a paragraph inside a div.', $result);
        $this->assertStringContainsString('This is another paragraph inside the same div.', $result);
        $this->assertStringContainsString('This is a paragraph outside the div.', $result);
    }

    /** @test */
    public function it_handles_mixed_content()
    {
        $converter = new HtmlToJsonConverter();

        $html = '
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

        $result = $converter->convert($html);

        $this->assertIsString($result);
        $this->assertStringContainsString('# Article Title', $result);
        $this->assertStringContainsString('First paragraph.', $result);
        $this->assertStringContainsString('## Section Title', $result);
        $this->assertStringContainsString('Second paragraph.', $result);
        $this->assertStringContainsString('* List item 1', $result);
        $this->assertStringContainsString('* List item 2', $result);
        $this->assertStringContainsString('Third paragraph.', $result);
        $this->assertStringContainsString('Fourth paragraph.', $result);
    }
}
