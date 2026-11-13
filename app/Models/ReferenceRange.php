<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferenceRange extends Model
{
    public const GENDERS = ['any' => 'Any', 'male' => 'Male', 'female' => 'Female'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'low' => 'float',
            'high' => 'float',
            'age_min_days' => 'integer',
            'age_max_days' => 'integer',
        ];
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(TestParameter::class, 'test_parameter_id');
    }

    public function matches(?string $gender, ?int $ageDays): bool
    {
        if ($this->gender !== 'any' && $this->gender !== $gender) {
            return false;
        }
        if ($ageDays === null) {
            return true; // age unknown: any band may apply, the most general one wins in rangeFor()
        }

        return ($this->age_min_days === null || $ageDays >= $this->age_min_days)
            && ($this->age_max_days === null || $ageDays <= $this->age_max_days);
    }

    public function ageSpan(): int
    {
        return ($this->age_max_days ?? 54750) - ($this->age_min_days ?? 0);
    }

    /** Text printed on the report, e.g. "13 - 17" or the custom normal text. */
    public function label(): string
    {
        if (filled($this->normal_text)) {
            return $this->normal_text;
        }
        $fmt = fn (?float $v) => $v === null ? null : rtrim(rtrim(number_format($v, 4, '.', ''), '0'), '.');

        return match (true) {
            $this->low !== null && $this->high !== null => $fmt($this->low).' - '.$fmt($this->high),
            $this->low !== null => '≥ '.$fmt($this->low),
            $this->high !== null => '≤ '.$fmt($this->high),
            default => '',
        };
    }

    /** H / L / null for a numeric result. */
    public function flag(float $value): ?string
    {
        return match (true) {
            $this->low !== null && $value < $this->low => 'L',
            $this->high !== null && $value > $this->high => 'H',
            default => null,
        };
    }
}
