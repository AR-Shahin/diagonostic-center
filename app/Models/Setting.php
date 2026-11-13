<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Key/value business rules. Read with Setting::get('vat_percent'); defaults live in config/diagnostic.php.
 */
class Setting extends Model
{
    private const CACHE_KEY = 'settings.all';

    protected $guarded = ['id'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());

        return $all[$key] ?? config("diagnostic.defaults.$key", $default);
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => is_bool($value) ? (int) $value : $value]);
        }
        Cache::forget(self::CACHE_KEY);
    }
}
