<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Referrer extends Model
{
    use SoftDeletes;

    public const TYPES = ['doctor' => 'Doctor', 'rmp' => 'RMP / Village doctor', 'agent' => 'Agent / Marketing', 'corporate' => 'Corporate', 'other' => 'Other'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['commission_enabled' => 'boolean', 'status' => 'boolean'];
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(ReferrerRate::class);
    }

    public function commissionEntries(): HasMany
    {
        return $this->hasMany(CommissionEntry::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(CommissionPayout::class);
    }

    /** Earned but not yet paid out. */
    public function unpaidCommission(): float
    {
        return (float) $this->commissionEntries()->whereNull('payout_id')->sum('amount');
    }

    /** "Dr. Name, MBBS" style label for invoices. */
    public function displayName(): string
    {
        return trim($this->name.($this->degree ? ', '.$this->degree : ''));
    }
}
