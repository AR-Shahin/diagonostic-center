<?php

namespace App\Support;

use App\Models\WebsiteInfo;

/**
 * Centre name/logo/contact for printed documents (falls back to safe defaults).
 */
class WebsiteIdentity
{
    private const STARTER_DEFAULTS = ['Admin Panel', 'admin@mail.com', '01754100439'];

    public static function get(): array
    {
        $w = WebsiteInfo::first();
        $pick = fn ($v, $fallback = null) => filled($v) && ! in_array($v, self::STARTER_DEFAULTS, true) ? $v : $fallback;

        return [
            'name' => $pick($w?->name, 'Diagnostic Center'),
            'logo' => $pick($w?->logo) ? asset($w->logo) : null,
            'phone' => $pick($w?->phone),
            'email' => $pick($w?->email),
            'address' => $pick($w?->address),
        ];
    }
}
