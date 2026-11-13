<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sample extends Model
{
    public const STATUSES = ['pending' => 'Not collected', 'collected' => 'Collected', 'received' => 'Received in lab', 'rejected' => 'Rejected'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['collected_at' => 'datetime', 'received_at' => 'datetime'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function sampleType(): BelongsTo
    {
        return $this->belongsTo(SampleType::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'collected_by');
    }

    public function isCollected(): bool
    {
        return in_array($this->status, ['collected', 'received'], true);
    }

    public function statusBadge(): string
    {
        $class = ['pending' => 'warning', 'collected' => 'info', 'received' => 'primary', 'rejected' => 'danger'][$this->status] ?? 'light';

        return '<span class="badge text-bg-'.$class.'">'.e(self::STATUSES[$this->status] ?? $this->status).'</span>';
    }
}
