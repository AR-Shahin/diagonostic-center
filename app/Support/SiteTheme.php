<?php

namespace App\Support;

/**
 * Turns a predefined theme preset into CSS custom properties for the public website.
 */
class SiteTheme
{
    public static function presets(): array
    {
        return config('site_themes.presets', []);
    }

    public static function resolve(?string $key): array
    {
        $presets = self::presets();

        return $presets[$key] ?? $presets[config('site_themes.default', 'ocean')];
    }

    public static function css(?string $key): string
    {
        $t = self::resolve($key);

        $vars = [
            '--primary' => $t['primary'],
            '--primary-rgb' => self::rgb($t['primary']),
            '--primary-hover' => $t['primary_hover'],
            '--primary-deep' => $t['primary_deep'],
            '--secondary' => $t['secondary'],
            '--secondary-rgb' => self::rgb($t['secondary']),
            '--secondary-ink' => $t['secondary_ink'],
            '--heading' => $t['heading'],
            '--heading-rgb' => self::rgb($t['heading']),
            '--alt' => $t['alt'],
        ];

        $body = collect($vars)->map(fn ($v, $k) => "$k:$v")->implode(';');

        return ":root{{$body}}";
    }

    public static function rgb(string $hex): string
    {
        $hex = ltrim($hex, '#');

        return implode(', ', array_map('hexdec', str_split($hex, 2)));
    }
}
