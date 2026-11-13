<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InvoiceItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2', 'regular_price' => 'decimal:2',
            'discount_amount' => 'decimal:2', 'net_amount' => 'decimal:2', 'delivery_at' => 'datetime', 'delivered_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTest::class)->withTrashed();
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class)->withTrashed();
    }

    public const LAB_STATUSES = [
        'pending' => 'Waiting for sample',
        'collected' => 'Sample collected',
        'processing' => 'Result entered — awaiting verification',
        'ready' => 'Verified — report ready',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    public function sample(): BelongsTo
    {
        return $this->belongsTo(Sample::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(TestResult::class);
    }

    public function isReportable(): bool
    {
        return in_array($this->lab_status, ['ready', 'delivered'], true);
    }

    /** Overdue: promised time has passed and the report is not verified yet. */
    public function isOverdue(): bool
    {
        return $this->delivery_at && $this->delivery_at->isPast() && in_array($this->lab_status, ['pending', 'collected', 'processing'], true);
    }

    public function labBadge(): string
    {
        $class = ['pending' => 'warning', 'collected' => 'info', 'processing' => 'primary', 'ready' => 'success', 'delivered' => 'secondary', 'cancelled' => 'dark'][$this->lab_status] ?? 'light';
        $label = ['pending' => 'Awaiting sample', 'collected' => 'Sample in', 'processing' => 'To verify', 'ready' => 'Ready', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'][$this->lab_status] ?? $this->lab_status;

        return '<span class="badge text-bg-'.$class.'">'.e($label).'</span>';
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
