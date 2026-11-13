<?php

namespace App\Support\Frontend;

use App\Models\Branch;
use App\Models\Department;
use App\Models\LabTest;
use App\Models\Package;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Website data from the admin catalog, shaped like the arrays the Blade views expect.
 * Each method returns null when nothing has been entered yet, so the page keeps its placeholders.
 */
class CatalogContent
{
    private static ?bool $ready = null;

    /** @return array<string, array>|null keyed by department slug */
    public static function departments(): ?array
    {
        if (! self::ready()) {
            return null;
        }

        $departments = Department::query()
            ->where('status', true)->where('show_on_website', true)
            ->with(['labTests' => fn ($q) => $q->forWebsite()->orderBy('sort')->limit(50)])
            ->orderBy('sort')->get();

        if ($departments->isEmpty()) {
            return null;
        }

        return $departments->mapWithKeys(fn (Department $d) => [$d->slug => [
            'bn' => $d->localized('name'),
            'en' => $d->name_en,
            'icon' => $d->icon,
            'desc' => (string) $d->localized('description'),
            'services' => $d->localizedLines('highlights') ?: $d->labTests->take(4)->map(fn (LabTest $t) => $t->localized('name'))->all(),
        ]])->all();
    }

    public static function tests(): ?array
    {
        if (! self::ready()) {
            return null;
        }

        $tests = LabTest::query()->forWebsite()
            ->whereHas('department', fn ($q) => $q->where('status', true)->where('show_on_website', true))
            ->with('department:id,slug')
            ->orderBy('sort')->orderBy('name_en')->get();

        if ($tests->isEmpty()) {
            return null;
        }

        $defaultHours = (int) Setting::get('default_delivery_hours', 24);

        return $tests->map(fn (LabTest $t) => [
            'slug' => $t->slug,
            'name' => $t->name_en,
            'bn' => $t->name_bn ?: '',
            'cat' => $t->department->slug,
            'prep' => (string) $t->localized('preparation'),
            'price' => self::money($t->price),
            'discount' => $t->offer_price !== null ? self::money($t->offer_price) : null,
            'report' => self::hours($t->delivery_hours ?? $defaultHours),
            'popular' => $t->is_popular,
        ])->all();
    }

    public static function packages(): ?array
    {
        if (! self::ready()) {
            return null;
        }

        $packages = Package::query()->forWebsite()->with(['labTests' => fn ($q) => $q->where('status', true)])
            ->orderByDesc('is_featured')->orderBy('sort')->get();

        if ($packages->isEmpty()) {
            return null;
        }

        return $packages->map(function (Package $p) {
            $regular = $p->regularPrice();

            return [
                'slug' => $p->slug,
                'bn' => $p->localized('name'),
                'en' => $p->name_en,
                'icon' => $p->icon,
                'tagline' => (string) $p->localized('tagline'),
                'tests' => $p->labTests->map(fn (LabTest $t) => $t->localized('name'))->all(),
                'price' => self::money($p->price),
                'old_price' => $regular > (float) $p->price ? self::money($regular) : null,
                'saving' => $p->savingAmount() > 0 ? self::money($p->savingAmount()) : null,
                'featured' => $p->is_featured,
            ];
        })->all();
    }

    public static function branches(): ?array
    {
        if (! self::ready()) {
            return null;
        }

        $branches = Branch::query()->where('status', true)->where('show_on_website', true)
            ->orderByDesc('is_main')->orderBy('sort')->get();

        if ($branches->isEmpty()) {
            return null;
        }

        return $branches->map(fn (Branch $b) => [
            'name' => $b->localized('name'),
            'address' => (string) $b->localized('address'),
            'phone' => (string) $b->phone,
            'hours' => (string) $b->localized('opening_hours'),
            'services' => $b->localizedLines('facilities'),
            'image' => $b->image ? asset($b->image) : null,
            'map_url' => $b->map_url,
        ])->all();
    }

    private static function money($value): string
    {
        return number_format((float) $value);
    }

    private static function hours(int $hours): string
    {
        return $hours >= 48 && $hours % 24 === 0
            ? __(':n দিন', ['n' => $hours / 24])
            : __(':n ঘণ্টা', ['n' => $hours]);
    }

    /** The catalog tables exist (migrations ran). */
    private static function ready(): bool
    {
        if (self::$ready === null) {
            try {
                self::$ready = Schema::hasTable('lab_tests');
            } catch (Throwable) {
                self::$ready = false;
            }
        }

        return self::$ready;
    }
}
