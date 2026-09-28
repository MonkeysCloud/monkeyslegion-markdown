<?php
declare(strict_types=1);

namespace MonkeysLegion\Markdown\Tests\Unit;

use MonkeysLegion\Markdown\MarkdownRenderer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the MarkdownRenderer.
 */
final class MarkdownRendererTest extends TestCase
{
    private MarkdownRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new MarkdownRenderer();
    }

    #[Test]
    public function renders_h1_header(): void
    {
        $html = $this->renderer->render('# Hello World');
        self::assertSame('<h1>Hello World</h1>', $html);
    }

    #[Test]
    public function renders_h2_header(): void
    {
        $html = $this->renderer->render('## Section');
        self::assertSame('<h2>Section</h2>', $html);
    }

    #[Test]
    public function renders_h6_header(): void
    {
        $html = $this->renderer->render('###### Deep');
        self::assertSame('<h6>Deep</h6>', $html);
    }

    #[Test]
    public function renders_setext_h1(): void
    {
        $html = $this->renderer->render("Title\n===");
        self::assertSame('<h1>Title</h1>', $html);
    }

    #[Test]
    public function renders_setext_h2(): void
    {
        $html = $this->renderer->render("Subtitle\n---");
        self::assertSame('<h2>Subtitle</h2>', $html);
    }

    #[Test]
    public function renders_bold(): void
    {
        $html = $this->renderer->render('This is **bold** text.');
        self::assertStringContainsString('<strong>bold</strong>', $html);
    }

    #[Test]
    public function renders_bold_with_underscores(): void
    {
        $html = $this->renderer->render('This is __bold__ text.');
        self::assertStringContainsString('<strong>bold</strong>', $html);
    }

    #[Test]
    public function renders_italic(): void
    {
        $html = $this->renderer->render('This is *italic* text.');
        self::assertStringContainsString('<em>italic</em>', $html);
    }

    #[Test]
    public function renders_inline_code(): void
    {
        $html = $this->renderer->render('Use `var_dump()` for debugging.');
        self::assertStringContainsString('<code>var_dump()</code>', $html);
    }

    #[Test]
    public function renders_code_block(): void
    {
        $html = $this->renderer->render("```php\necho 'hello';\n```");
        self::assertStringContainsString('<pre><code class="language-php">', $html);
        self::assertStringContainsString('echo &#039;hello&#039;;', $html);
    }

    #[Test]
    public function renders_indented_code_block(): void
    {
        $html = $this->renderer->render("    echo 'hello';");
        self::assertStringContainsString('<pre><code>', $html);
        self::assertStringContainsString('echo &#039;hello&#039;;', $html);
    }

    #[Test]
    public function renders_link(): void
    {
        $html = $this->renderer->render('[Click here](https://example.com)');
        self::assertStringContainsString('<a href="https://example.com">Click here</a>', $html);
    }

    #[Test]
    public function renders_image(): void
    {
        $html = $this->renderer->render('![Logo](https://example.com/logo.png)');
        self::assertStringContainsString('<img src="https://example.com/logo.png" alt="Logo">', $html);
    }

    #[Test]
    public function renders_unordered_list(): void
    {
        $html = $this->renderer->render("- Item 1\n- Item 2\n- Item 3");
        self::assertStringContainsString('<ul>', $html);
        self::assertStringContainsString('<li>Item 1</li>', $html);
        self::assertStringContainsString('<li>Item 2</li>', $html);
        self::assertStringContainsString('<li>Item 3</li>', $html);
    }

    #[Test]
    public function renders_ordered_list(): void
    {
        $html = $this->renderer->render("1. First\n2. Second\n3. Third");
        self::assertStringContainsString('<ol>', $html);
        self::assertStringContainsString('<li>First</li>', $html);
        self::assertStringContainsString('<li>Second</li>', $html);
    }

    #[Test]
    public function renders_blockquote(): void
    {
        $html = $this->renderer->render('> This is a quote.');
        self::assertStringContainsString('<blockquote>', $html);
        self::assertStringContainsString('This is a quote.', $html);
    }

    #[Test]
    public function renders_horizontal_rule(): void
    {
        $html = $this->renderer->render('---');
        self::assertStringContainsString('<hr>', $html);
    }

    #[Test]
    public function renders_paragraph(): void
    {
        $html = $this->renderer->render('This is a paragraph.');
        self::assertSame('<p>This is a paragraph.</p>', $html);
    }

    #[Test]
    public function renders_strikethrough(): void
    {
        $html = $this->renderer->render('This is ~~deleted~~ text.');
        self::assertStringContainsString('<del>deleted</del>', $html);
    }

    #[Test]
    public function renders_multiple_blocks(): void
    {
        $markdown = "# Title\n\nSome text here.\n\n- List item";
        $html = $this->renderer->render($markdown);

        self::assertStringContainsString('<h1>Title</h1>', $html);
        self::assertStringContainsString('<p>Some text here.</p>', $html);
        self::assertStringContainsString('<ul>', $html);
    }

    #[Test]
    public function escapes_html_in_code_blocks(): void
    {
        $html = $this->renderer->render("```\n<script>alert('xss')</script>\n```");
        self::assertStringContainsString('&lt;script&gt;', $html);
        self::assertStringNotContainsString('<script>', $html);
    }

    #[Test]
    public function handles_empty_input(): void
    {
        self::assertSame('', $this->renderer->render(''));
    }

    #[Test]
    public function handles_input_with_only_whitespace(): void
    {
        self::assertSame('', $this->renderer->render("\n\n\n"));
    }
}
