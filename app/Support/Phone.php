<?php

namespace App\Support;

/**
 * Bangladeshi mobile numbers in one shape: 01XXXXXXXXX (11 digits).
 * "+880 1712-345678", "8801712345678" and "01712345678" are the same number.
 */
class Phone
{
    public const PATTERN = '/^01[3-9]\d{8}$/';

    public static function normalize(?string $value): string
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        if (str_starts_with($digits, '880') && strlen($digits) === 13) {
            $digits = '0'.substr($digits, 3);
        } elseif (str_starts_with($digits, '88') && strlen($digits) === 13) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            $digits = '0'.$digits;
        }

        return $digits;
    }

    public static function isValid(?string $value): bool
    {
        return (bool) preg_match(self::PATTERN, self::normalize($value));
    }
}
