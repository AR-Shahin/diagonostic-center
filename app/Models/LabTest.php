<?php

namespace App\Models;

use App\Helper\Attribute\StatusAttribute;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A billable diagnostic test (named LabTest to avoid clashing with PHPUnit's "Test").
 */
class LabTest extends Model
{
    use HasTranslations, SoftDeletes, StatusAttribute;

    public const REPORT_TYPES = [
        'parameter' => 'Parameter based (pathology, biochemistry…)',
        'template' => 'Template / free text (X-ray, USG, ECG…)',
        'culture' => 'Culture & sensitivity',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'offer_price' => 'decimal:2',
            'cost' => 'decimal:2',
            'is_popular' => 'boolean',
            'show_on_website' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function sampleType(): BelongsTo
    {
        return $this->belongsTo(SampleType::class);
    }

    public function parameters(): HasMany
    {
        return $this->hasMany(TestParameter::class)->orderBy('sort');
    }

    public function reportTemplates(): HasMany
    {
        return $this->hasMany(ReportTemplate::class);
    }

    /** Branches that offer this test. Empty = every branch. */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_items');
    }

    /** Price the counter should charge (campaign price wins when set). */
    public function sellingPrice(): float
    {
        return (float) ($this->offer_price ?? $this->price);
    }

    public function scopeForWebsite(Builder $query): void
    {
        $query->where('status', true)->where('show_on_website', true);
    }

    public function scopeAvailableAt(Builder $query, int $branchId): void
    {
        $query->where(fn (Builder $q) => $q->doesntHave('branches')
            ->orWhereHas('branches', fn (Builder $b) => $b->whereKey($branchId)));
    }
}
