<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestResult extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['culture' => 'array', 'entered_at' => 'datetime', 'verified_at' => 'datetime'];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'invoice_item_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(ResultValue::class)->orderBy('sort');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'entered_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'verified_by');
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }
}
