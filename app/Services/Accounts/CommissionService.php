<?php

namespace App\Services\Accounts;

use App\Models\Admin;
use App\Models\CommissionEntry;
use App\Models\CommissionPayout;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Referrer;
use App\Models\ReferrerRate;
use App\Models\Setting;
use App\Services\Billing\NumberGenerator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Referral commission.
 *
 * Base per test = the price charged for that test before the bill discount
 * (for a test inside a package: its share of the package price).
 * Rate = the most specific matching rate (referrer+test > referrer+department > referrer > default+test > default+department > default).
 * Who bears the bill discount (Business rules):
 *   company  → nothing deducted
 *   referrer → the test's share of the discount is deducted from its commission
 *   shared   → half of it is deducted
 * Earned when the bill is fully paid (or when billed, per Business rules). A cancelled bill writes a reversal.
 */
class CommissionService
{
    /** Call after a bill is created or a payment is received. Safe to call many times. */
    public function accrueIfDue(Invoice $invoice): int
    {
        if (! (bool) Setting::get('commission_enabled', 1) || $invoice->isCancelled() || ! $invoice->referrer_id) {
            return 0;
        }
        $trigger = Setting::get('commission_trigger', 'fully_paid');
        if ($trigger === 'fully_paid' && $invoice->status !== 'paid') {
            return 0;
        }

        return $this->accrue($invoice);
    }

    public function accrue(Invoice $invoice): int
    {
        $referrer = $invoice->referrer;
        if (! $referrer || ! $referrer->commission_enabled) {
            return 0;
        }
        if (CommissionEntry::where('invoice_id', $invoice->id)->where('type', 'credit')->exists()) {
            return 0; // already earned
        }

        $rates = $this->ratesFor($referrer);
        $bearer = Setting::get('discount_bearer', 'company');

        $rows = $invoice->labItems()->with('labTest:id,department_id')->get()
            ->map(function (InvoiceItem $item) use ($rates, $bearer, $referrer, $invoice) {
                $rate = $this->pickRate($rates, $item->lab_test_id, $item->department_id ?? $item->labTest?->department_id);
                if (! $rate) {
                    return null;
                }
                $base = (float) $item->price;
                $gross = $rate->type === 'percent' ? round($base * (float) $rate->value / 100, 2) : (float) $rate->value;
                $deduct = match ($bearer) {
                    'referrer' => (float) $item->discount_amount,
                    'shared' => round((float) $item->discount_amount / 2, 2),
                    default => 0.0,
                };
                $amount = max(0, round($gross - $deduct, 2));

                return [
                    'referrer_id' => $referrer->id, 'invoice_id' => $invoice->id, 'invoice_item_id' => $item->id,
                    'type' => 'credit', 'base_amount' => $base, 'rate_type' => $rate->type, 'rate_value' => $rate->value,
                    'discount_deducted' => min($deduct, $gross), 'amount' => $amount,
                    'created_at' => now(), 'updated_at' => now(),
                ];
            })->filter(fn ($r) => $r && $r['amount'] > 0)->values();

        if ($rows->isNotEmpty()) {
            CommissionEntry::insert($rows->all());
        }

        return $rows->count();
    }

    /** Bill cancelled: cancel what it earned (a negative row, so already-paid commission is recovered next time). */
    public function reverse(Invoice $invoice, string $reason = 'Bill cancelled'): void
    {
        $earned = CommissionEntry::where('invoice_id', $invoice->id)->get();
        $net = round((float) $earned->sum('amount'), 2);
        if ($net <= 0) {
            return;
        }

        $unpaid = $earned->whereNull('payout_id')->where('type', 'credit');
        if ($unpaid->count() === $earned->where('type', 'credit')->count()) {
            CommissionEntry::whereIn('id', $unpaid->pluck('id'))->delete(); // never paid: simply remove

            return;
        }

        CommissionEntry::create([
            'referrer_id' => $invoice->referrer_id, 'invoice_id' => $invoice->id, 'type' => 'reversal',
            'amount' => -$net, 'note' => $reason,
        ]);
    }

    /** Pay everything unpaid up to a date (negative rows from reversals reduce the amount). */
    public function pay(Referrer $referrer, Carbon $upTo, string $method, ?string $reference, ?string $note, Admin $by): CommissionPayout
    {
        return DB::transaction(function () use ($referrer, $upTo, $method, $reference, $note, $by) {
            $entries = $referrer->commissionEntries()->whereNull('payout_id')
                ->where('created_at', '<=', $upTo->copy()->endOfDay())->lockForUpdate()->get();
            $amount = round((float) $entries->sum('amount'), 2);
            if ($entries->isEmpty() || $amount <= 0) {
                throw ValidationException::withMessages(['period_to' => 'Nothing to pay up to this date.']);
            }

            $payout = CommissionPayout::create([
                'payout_no' => 'CP-'.now()->format('ym').'-'.str_pad((string) NumberGenerator::next('PAYOUT-'.now()->format('ym')), 4, '0', STR_PAD_LEFT),
                'referrer_id' => $referrer->id, 'period_to' => $upTo->toDateString(), 'amount' => $amount,
                'method' => $method, 'reference' => $reference, 'note' => $note, 'paid_by' => $by->id,
            ]);
            CommissionEntry::whereIn('id', $entries->pluck('id'))->update(['payout_id' => $payout->id]);

            return $payout;
        });
    }

    /** What a test on a bill would earn for this referrer (used on the rates page preview). */
    public function ratesFor(?Referrer $referrer): Collection
    {
        return ReferrerRate::query()
            ->where(fn ($q) => $q->whereNull('referrer_id')->when($referrer, fn ($w) => $w->orWhere('referrer_id', $referrer->id)))
            ->get();
    }

    public function pickRate(Collection $rates, ?int $testId, ?int $departmentId): ?ReferrerRate
    {
        return $rates->filter(fn (ReferrerRate $r) => ($r->lab_test_id === null || $r->lab_test_id === $testId)
                && ($r->department_id === null || $r->department_id === $departmentId)
                && ! ($r->lab_test_id !== null && $r->department_id !== null && $r->department_id !== $departmentId))
            ->sortByDesc(fn (ReferrerRate $r) => $r->specificity())
            ->first();
    }
}
