<?php
declare(strict_types=1);

namespace MonkeysLegion\Markdown;

/**
 * MonKeysLegion Framework — Markdown Package
 *
 * Pure PHP Markdown renderer (CommonMark-like subset).
 *
 * Supports the most common 80% of markdown syntax:
 *   • Headers (H1-H6 with # and ===/---)
 *   • Bold (**text** / __text__)
 *   • Italic (*text* / _text_)
 *   • Inline code (`code`)
 *   • Code blocks (``` and 4-space indent)
 *   • Links [text](url)
 *   • Images ![alt](url)
 *   • Unordered lists (-, *, +)
 *   • Ordered lists (1.)
 *   • Blockquotes (>)
 *   • Horizontal rules (---, ***, ___)
 *   • Paragraphs
 *   • Line breaks
 *
 * No external dependencies — pure PHP implementation.
 *
 * @copyright 2026 MonKeysCloud Team
 * @license   MIT
 */
final class MarkdownRenderer
{
    /**
     * Render markdown to HTML.
     */
    public function render(string $markdown): string
    {
        // Normalize line endings
        $markdown = str_replace("\r\n", "\n", $markdown);
        $markdown = str_replace("\r", "\n", $markdown);

        // Extract code blocks first (to protect them from inline parsing)
        $codeBlocks = [];
        $markdown = preg_replace_callback(
            '/```(\w*)\n(.*?)```/s',
            function($m) use (&$codeBlocks): string {
                $lang = $m[1] !== '' ? ' class="language-' . htmlspecialchars($m[1]) . '"' : '';
                $code = htmlspecialchars($m[2]);
                $placeholder = "\x00CODEBLOCK" . count($codeBlocks) . "\x00";
                $codeBlocks[] = "<pre><code{$lang}>{$code}</code></pre>";
                return $placeholder;
            },
            $markdown,
        ) ?? $markdown;

        // Split into lines for block-level parsing
        $lines = explode("\n", $markdown);
        $html = [];
        $i = 0;
        $lineCount = count($lines);

        while ($i < $lineCount) {
            $line = $lines[$i];

            // Skip empty lines
            if (trim($line) === '') {
                $i++;
                continue;
            }

            // Check for code block placeholder
            if (preg_match('/^\x00CODEBLOCK(\d+)\x00$/', $line, $m)) {
                $html[] = $codeBlocks[(int) $m[1]];
                $i++;
                continue;
            }

            // Headers with # syntax
            if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $m)) {
                $level = strlen($m[1]);
                $content = $this->parseInline($m[2]);
                $html[] = "<h{$level}>{$content}</h{$level}>";
                $i++;
                continue;
            }

            // Headers with === or --- syntax
            if ($i + 1 < $lineCount) {
                $next = trim($lines[$i + 1]);
                if ($next === str_repeat('=', strlen($next)) && strlen($next) > 0) {
                    $content = $this->parseInline(trim($line));
                    $html[] = "<h1>{$content}</h1>";
                    $i += 2;
                    continue;
                }
                if ($next === str_repeat('-', strlen($next)) && strlen($next) > 0 && strlen($next) >= 3) {
                    $content = $this->parseInline(trim($line));
                    $html[] = "<h2>{$content}</h2>";
                    $i += 2;
                    continue;
                }
            }

            // Horizontal rules
            if (preg_match('/^(-{3,}|\*{3,}|_{3,})\s*$/', $line)) {
                $html[] = '<hr>';
                $i++;
                continue;
            }

            // Blockquotes
            if (str_starts_with(trim($line), '>')) {
                $quoteLines = [];
                while ($i < $lineCount && str_starts_with(trim($lines[$i] ?? ''), '>')) {
                    $quoteLines[] = preg_replace('/^>\s?/', '', trim($lines[$i]));
                    $i++;
                }
                $quoteHtml = $this->render(implode("\n", $quoteLines));
                $html[] = "<blockquote>{$quoteHtml}</blockquote>";
                continue;
            }

            // Unordered lists
            if (preg_match('/^(\s*)([-*+])\s+(.+)$/', $line, $m)) {
                $items = [];
                while ($i < $lineCount && preg_match('/^(\s*)([-*+])\s+(.+)$/', $lines[$i] ?? '', $item)) {
                    $items[] = '<li>' . $this->parseInline($item[3]) . '</li>';
                    $i++;
                }
                $html[] = '<ul>' . implode("\n", $items) . '</ul>';
                continue;
            }

            // Ordered lists
            if (preg_match('/^(\s*)(\d+)\.\s+(.+)$/', $line, $m)) {
                $items = [];
                while ($i < $lineCount && preg_match('/^(\s*)(\d+)\.\s+(.+)$/', $lines[$i] ?? '', $item)) {
                    $items[] = '<li>' . $this->parseInline($item[3]) . '</li>';
                    $i++;
                }
                $html[] = '<ol>' . implode("\n", $items) . '</ol>';
                continue;
            }

            // Indented code blocks (4+ spaces)
            if (preg_match('/^    (.+)$/', $line)) {
                $codeLines = [];
                while ($i < $lineCount && preg_match('/^    (.+)$/', $lines[$i] ?? '', $codeMatch)) {
                    $codeLines[] = $codeMatch[1];
                    $i++;
                }
                $code = htmlspecialchars(implode("\n", $codeLines));
                $html[] = "<pre><code>{$code}</code></pre>";
                continue;
            }

            // Paragraph (collect consecutive non-empty, non-special lines)
            $paraLines = [];
            while (
                $i < $lineCount &&
                trim($lines[$i] ?? '') !== '' &&
                !preg_match('/^#{1,6}\s/', $lines[$i]) &&
                !preg_match('/^[-*+]\s/', $lines[$i]) &&
                !preg_match('/^\d+\.\s/', $lines[$i]) &&
                !str_starts_with(trim($lines[$i] ?? ''), '>') &&
                !preg_match('/^(-{3,}|\*{3,}|_{3,})\s*$/', $lines[$i] ?? '') &&
                !preg_match('/^    /', $lines[$i] ?? '') &&
                !preg_match('/^\x00CODEBLOCK/', $lines[$i] ?? '')
            ) {
                $paraLines[] = $lines[$i];
                $i++;
            }

            if (!empty($paraLines)) {
                $content = $this->parseInline(implode(' ', $paraLines));
                $html[] = "<p>{$content}</p>";
            }
        }

        $result = implode("\n", $html);

        // Restore code blocks
        foreach ($codeBlocks as $i => $block) {
            $result = str_replace("\x00CODEBLOCK{$i}\x00", $block, $result);
        }

        return $result;
    }

    /**
     * Parse inline markdown elements.
     */
    private function parseInline(string $text): string
    {
        // Images ![alt](url)
        $text = preg_replace(
            '/!\[([^\]]*)\]\(([^)]+)\)/',
            '<img src="$2" alt="$1">',
            $text,
        ) ?? $text;

        // Links [text](url)
        $text = preg_replace(
            '/\[([^\]]+)\]\(([^)]+)\)/',
            '<a href="$2">$1</a>',
            $text,
        ) ?? $text;

        // Bold **text** or __text__
        $text = preg_replace(
            '/\*\*([^\*]+)\*\*|__([^_]+)__/',
            '<strong>$1$2</strong>',
            $text,
        ) ?? $text;

        // Italic *text* or _text_
        $text = preg_replace(
            '/\*([^\*]+)\*|_([^_]+)_/',
            '<em>$1$2</em>',
            $text,
        ) ?? $text;

        // Inline code `code`
        $text = preg_replace(
            '/`([^`]+)`/',
            '<code>$1</code>',
            $text,
        ) ?? $text;

        // Strikethrough ~~text~~
        $text = preg_replace(
            '/~~([^~]+)~~/',
            '<del>$1</del>',
            $text,
        ) ?? $text;

        return $text;
    }
}
