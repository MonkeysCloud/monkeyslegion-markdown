# MonKeysLegion Markdown

Pure PHP CommonMark-like Markdown renderer for the MonKeysLegion framework.

## Features

- **No external dependencies** — pure PHP implementation
- **CommonMark-compatible** — headers, emphasis, code, lists, links, images
- **Extensions** — strikethrough, task lists, tables
- **XSS-safe** — all code blocks are HTML-escaped
- **Blade integration** — use `@markdown($content)` in templates
- **Fast** — single-pass block parser + inline parser

## Supported Syntax

| Element | Syntax |
|---------|--------|
| Headers (ATX) | `# H1`, `## H2`, ... |
| Headers (Setext) | `===` / `---` |
| Bold | `**bold**` |
| Italic | `*italic*` |
| Strikethrough | `~~text~~` |
| Inline code | `` `code` `` |
| Code blocks | ` ``` ` fenced or 4-space indent |
| Links | `[text](url)` |
| Images | `![alt](url)` |
| Lists | `- item` / `1. item` |
| Blockquotes | `> quote` |
| Horizontal rules | `---` / `***` |

## Installation

```bash
composer require monkeyscloud/monkeyslegion-markdown
```

## Usage

```php
use MonkeysLegion\Markdown\MarkdownRenderer;

$renderer = new MarkdownRenderer();
$html = $renderer->render('# Hello World

This is **bold** and *italic*.

- Item 1
- Item 2
');

echo $html;
```

## In Templates

```php
@markdown($post->body)
```

## License

MIT © MonKeysCloud
