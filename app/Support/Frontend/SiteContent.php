<?php

namespace App\Support\Frontend;

use App\Models\Article;
use App\Models\Branch;
use App\Models\Doctor;
use App\Models\Faq;
use App\Models\LabTest;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Website content edited in Admin → Website (doctors, articles, FAQ, reviews, home page text).
 * Unlike the catalog placeholders, these sections simply hide while nothing has been entered,
 * so the live site never shows a made-up doctor or review.
 */
class SiteContent
{
    private static ?bool $ready = null;

    public static function setting(string $key): mixed
    {
        return self::ready() ? Setting::get($key, config("diagnostic.website.$key")) : config("diagnostic.website.$key");
    }

    /** Bilingual setting: current language first, then the other one, then null. */
    public static function text(string $key): ?string
    {
        $en = app()->getLocale() === 'en';

        return self::setting($key.($en ? '_en' : '_bn')) ?: (self::setting($key.($en ? '_bn' : '_en')) ?: null);
    }

    public static function lines(string $key): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) self::text($key)))));
    }

    public static function doctors(): array
    {
        if (! self::ready()) {
            return [];
        }

        return Doctor::query()->forWebsite()->orderBy('sort')->orderBy('name_en')->get()->map(fn (Doctor $d) => [
            'id' => $d->id,
            'slug' => $d->slug,
            'name' => $d->localized('name'),
            'qualification' => $d->qualification,
            'designation' => $d->localized('designation'),
            'speciality' => $d->localized('speciality') ?: __('চিকিৎসক'),
            'schedule' => $d->scheduleText(),
            'fee' => $d->fee !== null ? number_format((float) $d->fee) : null,
            'image' => $d->photoUrl(),
            'bio' => $d->localized('bio'),
            'bookable' => $d->accepts_appointments,
        ])->all();
    }

    public static function articles(?int $limit = null): array
    {
        if (! self::ready()) {
            return [];
        }

        return Article::query()->published()->latest('published_at')->when($limit, fn ($q, $n) => $q->limit($n))->get()
            ->map(fn (Article $a) => self::article($a))->all();
    }

    public static function article(Article $a): array
    {
        return [
            'slug' => $a->slug,
            'title' => $a->localized('title'),
            'category' => $a->localized('category'),
            'excerpt' => $a->localized('excerpt'),
            'body' => $a->localized('body'),
            'date' => $a->published_at,
            'updated' => $a->updated_at,
            'image' => $a->imageUrl(),
            'url' => route('site.article', $a->slug),
        ];
    }

    public static function faqs(): array
    {
        if (! self::ready()) {
            return [];
        }

        return Faq::query()->where('status', true)->orderBy('sort')->orderBy('id')->get()
            ->map(fn (Faq $f) => ['q' => $f->localized('question'), 'a' => $f->localized('answer')])
            ->filter(fn ($f) => $f['q'] && $f['a'])->values()->all();
    }

    public static function testimonials(): array
    {
        if (! self::ready()) {
            return [];
        }

        return Testimonial::query()->forWebsite()->orderBy('sort')->latest('id')->limit(6)->get()
            ->map(fn (Testimonial $t) => ['text' => $t->localized('text'), 'name' => $t->name, 'meta' => $t->localized('meta'), 'rating' => $t->rating])
            ->filter(fn ($t) => $t['text'])->values()->all();
    }

    /** Only real numbers: counted from the database or entered by the centre. Zero hides a tile. */
    public static function stats(): array
    {
        $count = fn (callable $q) => self::ready() ? (int) $q() : 0;
        $tests = $count(fn () => LabTest::query()->forWebsite()->count());
        $doctors = $count(fn () => Doctor::query()->forWebsite()->count());
        $branches = $count(fn () => Branch::query()->where('status', true)->count());
        $value = fn (int $n) => $n > 0 ? $n : null;

        return [
            ['icon' => 'bi-award', 'value' => $value((int) self::setting('stat_years')), 'suffix' => '+', 'label' => __('বছরের অভিজ্ঞতা')],
            ['icon' => 'bi-clipboard2-pulse', 'value' => $value($tests), 'suffix' => '+', 'label' => __('ডায়াগনস্টিক টেস্ট')],
            ['icon' => 'bi-person-badge', 'value' => $value($doctors), 'suffix' => '', 'label' => __('বিশেষজ্ঞ চিকিৎসক')],
            ['icon' => 'bi-emoji-smile', 'value' => $value((int) self::setting('stat_patients')), 'suffix' => '+', 'label' => __('সন্তুষ্ট রোগী')],
            ['icon' => 'bi-buildings', 'value' => $value($branches), 'suffix' => '', 'label' => __('শাখা')],
        ];
    }

    public static function homeCollection(): array
    {
        $charge = self::ready() ? (float) Setting::get('home_collection_charge', 0) : 0;

        return [
            'enabled' => (bool) self::setting('home_collection_enabled'),
            'areas' => self::lines('home_collection_areas'),
            'charge' => $charge > 0 ? number_format($charge) : null,
        ];
    }

    public static function heroImage(): ?string
    {
        $path = self::setting('hero_image');

        return $path ? asset($path) : null;
    }

    private static function ready(): bool
    {
        if (self::$ready === null) {
            try {
                self::$ready = Schema::hasTable('doctors') && Schema::hasTable('settings');
            } catch (Throwable) {
                self::$ready = false;
            }
        }

        return self::$ready;
    }
}
