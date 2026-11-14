<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionPayout extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'period_to' => 'date'];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Referrer::class)->withTrashed();
    }

    public function entries(): HasMany
    {
        return $this->hasMany(CommissionEntry::class, 'payout_id');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'paid_by');
    }
}
