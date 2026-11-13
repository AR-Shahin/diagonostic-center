<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Patient extends Model
{
    use SoftDeletes;

    public const GENDERS = ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'];

    public const BLOOD_GROUPS = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['date_of_birth' => 'date', 'dob_estimated' => 'boolean'];
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /** Website login account (shared by family members on the same phone). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** Date of birth worked out from an age typed at the counter (e.g. 32 years 4 months). */
    public static function dobFromAge(int $years = 0, int $months = 0, int $days = 0): Carbon
    {
        return now()->startOfDay()->subYears($years)->subMonths($months)->subDays($days);
    }

    public function ageInDays(?CarbonInterface $on = null): ?int
    {
        return $this->date_of_birth ? (int) $this->date_of_birth->diffInDays(($on ?? now())->startOfDay()) : null;
    }

    /** "32 Y", "1 Y 3 M", "15 D" — the way ages are printed on Bangladeshi lab slips. */
    public function ageLabel(?CarbonInterface $on = null): ?string
    {
        if (! $this->date_of_birth) {
            return null;
        }
        $diff = $this->date_of_birth->diff(($on ?? now())->startOfDay());

        return match (true) {
            $diff->y >= 5 => $diff->y.' Y',
            $diff->y >= 1 => $diff->y.' Y'.($diff->m ? ' '.$diff->m.' M' : ''),
            $diff->m >= 1 => $diff->m.' M'.($diff->d ? ' '.$diff->d.' D' : ''),
            default => max(0, $diff->d).' D',
        };
    }

    public function totalDue(): float
    {
        return (float) $this->invoices()->where('status', '!=', 'cancelled')->sum('due_amount');
    }
}
