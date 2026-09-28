<?php
declare(strict_types=1);

namespace MonkeysLegion\Markdown;

/**
 * MonKeysLegion Framework — Markdown Package
 *
 * Service provider for markdown rendering.
 *
 * @copyright 2026 MonKeysCloud Team
 * @license   MIT
 */
final class MarkdownServiceProvider
{
    /**
     * Register the markdown renderer as a singleton.
     *
     * @param callable(string, callable): void $register
     */
    public function register(callable $register): void
    {
        $register(MarkdownRenderer::class, fn(): MarkdownRenderer => new MarkdownRenderer());
    }
}
