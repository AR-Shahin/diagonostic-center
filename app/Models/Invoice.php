<?php

namespace App\Models;

use App\Support\BranchAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    public const STATUSES = ['unpaid' => 'Unpaid', 'partial' => 'Partly paid', 'paid' => 'Paid', 'cancelled' => 'Cancelled'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2', 'discount_value' => 'decimal:2', 'discount_amount' => 'decimal:2',
            'vat_percent' => 'decimal:2', 'vat_amount' => 'decimal:2', 'home_collection' => 'boolean',
            'home_collection_charge' => 'decimal:2', 'round_off' => 'decimal:2', 'net_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2', 'due_amount' => 'decimal:2',
            'delivery_at' => 'datetime', 'cancelled_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Referrer::class)->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function discountApprover(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'discount_approved_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'cancelled_by');
    }

    /** All rows, including tests inside packages. */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort');
    }

    /** What the patient was billed for (tests and package lines, not the tests inside packages). */
    public function billedItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->whereNull('parent_id')->orderBy('sort');
    }

    /** Work for the lab: single tests and tests inside packages. */
    public function labItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->whereIn('item_type', ['test', 'package_test'])->orderBy('sort');
    }

    public function samples(): HasMany
    {
        return $this->hasMany(Sample::class)->orderBy('id');
    }

    /** Lab items whose report is verified (ready or already handed over). */
    public function reportableItems(): HasMany
    {
        return $this->labItems()->whereIn('lab_status', ['ready', 'delivered']);
    }

    public function verifyUrl(): string
    {
        return route('report.verify', $this->report_token);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('id');
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function hasDue(): bool
    {
        return ! $this->isCancelled() && (float) $this->due_amount > 0;
    }

    /** Business rule: the printed report is held while money is owed (Settings → Business rules). */
    public function canReleaseReport(bool $portal = false): bool
    {
        if ($this->isCancelled()) {
            return false;
        }
        $rule = $portal ? 'block_portal_report_if_due' : 'block_report_if_due';

        return ! ((bool) Setting::get($rule) && $this->hasDue());
    }

    /** Re-total paid/due/status from the payment rows. */
    public function refreshPaymentTotals(): void
    {
        $paid = (float) $this->payments()->where('type', 'payment')->sum('amount')
            - (float) $this->payments()->where('type', 'refund')->sum('amount');
        $paid = round($paid, 2);

        $this->paid_amount = $paid;
        if ($this->isCancelled()) {
            $this->due_amount = 0;
        } else {
            $due = round((float) $this->net_amount - $paid, 2);
            $this->due_amount = max(0, $due);
            $this->status = match (true) {
                $paid <= 0 => 'unpaid',
                $due > 0 => 'partial',
                default => 'paid',
            };
        }
        $this->save();
    }

    public function statusBadge(): string
    {
        $class = ['unpaid' => 'danger', 'partial' => 'warning', 'paid' => 'success', 'cancelled' => 'secondary'][$this->status] ?? 'light';

        return '<span class="badge text-bg-'.$class.'">'.e(self::STATUSES[$this->status] ?? $this->status).'</span>';
    }

    /** Limit to the branches the signed-in staff member may see. */
    public function scopeVisibleTo(Builder $query, ?Admin $admin): void
    {
        BranchAccess::scope($query, $admin);
    }
}
