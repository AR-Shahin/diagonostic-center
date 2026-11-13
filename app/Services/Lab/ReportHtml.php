<?php

namespace App\Services\Lab;

/**
 * Keeps template reports to simple, safe formatting (no scripts, styles or links).
 */
class ReportHtml
{
    private const ALLOWED = '<p><br><b><strong><i><em><u><ul><ol><li><h4><h5><table><thead><tbody><tr><th><td>';

    public static function clean(string $html): string
    {
        $html = preg_replace('#<(script|style|iframe|object)[^>]*>.*?</\1>#is', '', $html);
        $html = strip_tags($html, self::ALLOWED);
        // drop every attribute (on*, style, href…) from the allowed tags
        $html = preg_replace('/<(\w+)(?:\s+[^>]*)?>/', '<$1>', $html);

        return trim($html);
    }
}
