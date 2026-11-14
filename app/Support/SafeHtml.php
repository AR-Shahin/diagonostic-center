<?php

namespace App\Support;

/**
 * Article bodies: simple formatting only. Plain text is accepted too: a blank line starts a new paragraph.
 */
class SafeHtml
{
    private const ALLOWED = '<p><br><b><strong><i><em><u><ul><ol><li><h2><h3><h4><blockquote><table><thead><tbody><tr><th><td>';

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }
        if (! str_contains($html, '<')) {
            return collect(preg_split('/\R{2,}/', $html))
                ->map(fn ($para) => '<p>'.nl2br(e(trim($para)), false).'</p>')->implode("\n");
        }
        $html = preg_replace('#<(script|style|iframe|object|embed)[^>]*>.*?</\1>#is', '', $html);
        $html = strip_tags($html, self::ALLOWED);

        return trim(preg_replace('/<(\w+)(?:\s+[^>]*)?>/', '<$1>', $html));
    }
}
