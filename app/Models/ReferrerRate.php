<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferrerRate extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['value' => 'decimal:2'];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Referrer::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTest::class)->withTrashed();
    }

    /** Higher = more specific. */
    public function specificity(): int
    {
        return ($this->referrer_id ? 10 : 0) + ($this->lab_test_id ? 2 : ($this->department_id ? 1 : 0));
    }

    public function label(): string
    {
        return $this->type === 'percent' ? rtrim(rtrim((string) $this->value, '0'), '.').'%' : '৳ '.number_format((float) $this->value);
    }

    public function scopeLabel(): string
    {
        return $this->labTest?->name_en ?? ($this->department?->name_en ? 'All '.$this->department->name_en.' tests' : 'All tests');
    }
}
