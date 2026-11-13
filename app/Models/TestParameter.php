<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestParameter extends Model
{
    public const INPUT_TYPES = ['number' => 'Number', 'text' => 'Text', 'option' => 'Option list'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTest::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function referenceRanges(): HasMany
    {
        return $this->hasMany(ReferenceRange::class)->orderBy('sort');
    }

    /** The range that applies to a patient. Gender-specific beats "any"; narrower age band beats wider. */
    public function rangeFor(?string $gender, ?int $ageDays): ?ReferenceRange
    {
        return $this->referenceRanges
            ->filter(fn (ReferenceRange $r) => $r->matches($gender, $ageDays))
            ->sortBy(fn (ReferenceRange $r) => [$r->gender === 'any' ? 1 : 0, $ageDays === null ? -$r->ageSpan() : $r->ageSpan()])
            ->first();
    }
}
