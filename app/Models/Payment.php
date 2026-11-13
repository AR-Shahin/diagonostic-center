<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const METHODS = ['cash' => 'Cash', 'bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket', 'card' => 'Card', 'bank' => 'Bank'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'received_by');
    }

    public function signedAmount(): float
    {
        return $this->type === 'refund' ? -(float) $this->amount : (float) $this->amount;
    }
}
