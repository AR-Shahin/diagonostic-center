<?php

namespace App\Models\Concerns;

/**
 * Bilingual columns follow the "{field}_bn" / "{field}_en" convention.
 * localized('name') returns the value for the current locale and falls back to the other language.
 */
trait HasTranslations
{
    public function localized(string $field): ?string
    {
        $locale = app()->getLocale() === 'en' ? 'en' : 'bn';
        $fallback = $locale === 'en' ? 'bn' : 'en';

        return $this->getAttribute("{$field}_{$locale}") ?: $this->getAttribute("{$field}_{$fallback}");
    }

    /** Multi-line text column as a clean list (one item per line). */
    public function localizedLines(string $field): array
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) $this->localized($field));

        return array_values(array_filter(array_map('trim', $lines)));
    }
}
