<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashClosing extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'closing_date' => 'date', 'verified_at' => 'datetime', 'non_cash' => 'array',
            'opening_cash' => 'decimal:2', 'cash_received' => 'decimal:2', 'cash_refunded' => 'decimal:2',
            'cash_expenses' => 'decimal:2', 'commission_paid_cash' => 'decimal:2',
            'expected_cash' => 'decimal:2', 'counted_cash' => 'decimal:2', 'difference' => 'decimal:2',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'verified_by');
    }
}
