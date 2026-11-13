<?php

namespace App\Models;

use App\Helper\Attribute\StatusAttribute;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Health checkup package: a fixed price for a bundle of tests.
 */
class Package extends Model
{
    use HasTranslations, SoftDeletes, StatusAttribute;

    public const ICONS = [
        'bi-clipboard2-pulse', 'bi-person-check', 'bi-droplet-half', 'bi-heart-pulse',
        'bi-gender-female', 'bi-gender-male', 'bi-person-heart', 'bi-shield-plus', 'bi-activity', 'bi-capsule',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'is_featured' => 'boolean',
            'show_on_website' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function labTests(): BelongsToMany
    {
        return $this->belongsToMany(LabTest::class, 'package_items')->orderBy('sort')->orderBy('name_en');
    }

    /** What the tests would cost if bought one by one. */
    public function regularPrice(): float
    {
        return (float) $this->labTests->sum(fn (LabTest $t) => (float) $t->price);
    }

    public function savingAmount(): float
    {
        return max(0, $this->regularPrice() - (float) $this->price);
    }

    /**
     * Split the package price across its tests in proportion to their own prices.
     * Used for department income and referral commission. Rounding remainder goes to the last test.
     *
     * @return array<int, float> lab_test_id => share
     */
    public function priceShares(): array
    {
        $tests = $this->labTests;
        $regular = $this->regularPrice();
        $price = (float) $this->price;
        if ($tests->isEmpty() || $regular <= 0) {
            return [];
        }

        $shares = [];
        $allocated = 0.0;
        foreach ($tests->values() as $i => $test) {
            $share = $i === $tests->count() - 1
                ? round($price - $allocated, 2)
                : round((float) $test->price / $regular * $price, 2);
            $shares[$test->id] = $share;
            $allocated += $share;
        }

        return $shares;
    }

    public function isCurrentlyValid(): bool
    {
        $today = now()->startOfDay();

        return (! $this->valid_from || $this->valid_from->lte($today))
            && (! $this->valid_until || $this->valid_until->gte($today));
    }

    public function scopeForWebsite(Builder $query): void
    {
        $today = now()->toDateString();
        $query->where('status', true)->where('show_on_website', true)
            ->where(fn (Builder $q) => $q->whereNull('valid_from')->orWhereDate('valid_from', '<=', $today))
            ->where(fn (Builder $q) => $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', $today));
    }
}
