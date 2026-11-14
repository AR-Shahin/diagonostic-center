<?php

namespace App\Services\Billing;

use App\Models\Admin;
use App\Models\Branch;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\Accounts\CommissionService;
use App\Services\Lab\LabService;
use App\Services\Patients\PatientAccounts;
use App\Support\BranchAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function __construct(private InvoiceCalculator $calculator, private PatientAccounts $accounts) {}

    /**
     * Create a bill (and the first payment, if any) in one transaction.
     *
     * $data keys: branch_id, patient_id | patient{name,mobile,gender,age_years,age_months,age_days} (+ as_family_member),
     * referrer_id, items[{type,id}], discount_type, discount_value, discount_reason,
     * approver_id, approver_password, home_collection, note, paid_amount, payment_method, transaction_ref
     */
    public function create(array $data, Admin $by): Invoice
    {
        $branch = Branch::where('status', true)->findOrFail($data['branch_id']);
        if (! BranchAccess::canUse($by, $branch->id)) {
            throw ValidationException::withMessages(['branch_id' => 'You can only bill for your own branch.']);
        }

        $calc = $this->calculator->calculate(
            $data['items'] ?? [], $branch->id, $data['discount_type'] ?? null,
            (float) ($data['discount_value'] ?? 0), (bool) ($data['home_collection'] ?? false)
        );

        $approverId = $this->authoriseDiscount($calc, $data, $by);

        $paidNow = round((float) ($data['paid_amount'] ?? 0), 2);
        if ($paidNow < 0 || $paidNow > $calc['net_amount']) {
            throw ValidationException::withMessages(['paid_amount' => 'Paid amount must be between 0 and the net payable (৳'.number_format($calc['net_amount'], 2).').']);
        }

        return DB::transaction(function () use ($data, $by, $branch, $calc, $approverId, $paidNow) {
            $patient = $this->resolvePatient($data, $by, $branch);

            $invoice = Invoice::create([
                'invoice_no' => NumberGenerator::invoiceNo($branch),
                'branch_id' => $branch->id,
                'patient_id' => $patient->id,
                'referrer_id' => $data['referrer_id'] ?? null,
                'created_by' => $by->id,
                'patient_name' => $patient->name,
                'patient_mobile' => $patient->mobile,
                'patient_gender' => $patient->gender,
                'patient_age' => $patient->ageLabel(),
                'patient_age_days' => $patient->ageInDays(),
                'subtotal' => $calc['subtotal'],
                'discount_type' => $calc['discount_type'],
                'discount_value' => $calc['discount_value'],
                'discount_amount' => $calc['discount_amount'],
                'discount_reason' => $calc['discount_amount'] > 0 ? ($data['discount_reason'] ?? null) : null,
                'discount_approved_by' => $approverId,
                'vat_percent' => $calc['vat_percent'],
                'vat_amount' => $calc['vat_amount'],
                'home_collection' => $calc['home_collection'],
                'home_collection_charge' => $calc['home_collection_charge'],
                'round_off' => $calc['round_off'],
                'net_amount' => $calc['net_amount'],
                'paid_amount' => 0,
                'due_amount' => $calc['net_amount'],
                'status' => 'unpaid',
                'delivery_at' => now()->addHours($calc['delivery_hours']),
                'note' => $data['note'] ?? null,
                'report_pin' => (string) random_int(100000, 999999),
                'report_token' => Str::random(32),
            ]);

            $sort = 0;
            foreach ($calc['lines'] as $line) {
                $parent = $invoice->items()->create($this->itemRow($line, ++$sort));
                foreach ($line['children'] as $child) {
                    $invoice->items()->create($this->itemRow($child, ++$sort) + ['parent_id' => $parent->id]);
                }
            }

            app(LabService::class)->ensureSamples($invoice);

            if ($paidNow > 0) {
                $this->addPayment($invoice, $paidNow, $data['payment_method'] ?? 'cash', $data['transaction_ref'] ?? null, $by, 'Paid at billing');
            }
            $invoice->refreshPaymentTotals();
            app(CommissionService::class)->accrueIfDue($invoice);

            return $invoice;
        });
    }

    /** Collect a due (or part of it). */
    public function collect(Invoice $invoice, float $amount, string $method, ?string $reference, Admin $by, ?string $note = null): Payment
    {
        if ($invoice->isCancelled()) {
            throw ValidationException::withMessages(['amount' => 'This invoice is cancelled.']);
        }
        $amount = round($amount, 2);
        if ($amount <= 0 || $amount > (float) $invoice->due_amount) {
            throw ValidationException::withMessages(['amount' => 'Amount must be between 1 and the due (৳'.number_format((float) $invoice->due_amount, 2).').']);
        }

        return DB::transaction(function () use ($invoice, $amount, $method, $reference, $by, $note) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->first();
            $payment = $this->addPayment($invoice, $amount, $method, $reference, $by, $note ?: 'Due collection');
            $invoice->refreshPaymentTotals();
            app(CommissionService::class)->accrueIfDue($invoice);

            return $payment;
        });
    }

    /** Cancel a bill. Everything already paid is refunded (one refund receipt). */
    public function cancel(Invoice $invoice, string $reason, string $refundMethod, Admin $by): ?Payment
    {
        if ($invoice->isCancelled()) {
            throw ValidationException::withMessages(['reason' => 'This invoice is already cancelled.']);
        }

        return DB::transaction(function () use ($invoice, $reason, $refundMethod, $by) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->first();
            $refund = null;
            if ((float) $invoice->paid_amount > 0) {
                $refund = Payment::create([
                    'receipt_no' => NumberGenerator::receiptNo($invoice->branch),
                    'invoice_id' => $invoice->id, 'branch_id' => $invoice->branch_id,
                    'type' => 'refund', 'method' => $refundMethod, 'amount' => $invoice->paid_amount,
                    'note' => 'Refund on cancellation', 'received_by' => $by->id,
                ]);
            }
            $invoice->forceFill([
                'status' => 'cancelled', 'cancelled_at' => now(), 'cancelled_by' => $by->id, 'cancel_reason' => $reason,
            ])->save();
            app(CommissionService::class)->reverse($invoice, 'Bill cancelled: '.$reason);
            $invoice->items()->update(['lab_status' => 'cancelled']);
            $invoice->samples()->where('status', '!=', 'rejected')->update(['status' => 'rejected', 'rejected_reason' => 'Bill cancelled']);
            $invoice->refreshPaymentTotals();

            return $refund;
        });
    }

    /**
     * Discounts above the staff member's own limit need a second person (approver email/password at the counter)
     * whose limit covers it. Returns the approver id, or null when no approval was needed.
     */
    private function authoriseDiscount(array $calc, array $data, Admin $by): ?int
    {
        if ($calc['discount_amount'] <= 0) {
            return null;
        }
        if (blank($data['discount_reason'] ?? null)) {
            throw ValidationException::withMessages(['discount_reason' => 'Write a reason for the discount.']);
        }

        $percent = $calc['discount_percent'];
        if ($percent <= $by->discountLimit() + 0.0001) {
            return null;
        }

        $approver = filled($data['approver_id'] ?? null) ? Admin::find($data['approver_id']) : null;
        if (! $approver || ! Hash::check((string) ($data['approver_password'] ?? ''), $approver->password)) {
            throw ValidationException::withMessages([
                'approver_password' => 'This discount ('.round($percent, 2).'%) is above your limit of '.$by->discountLimit().'%. A manager must approve it with their password.',
            ]);
        }
        if ($approver->id === $by->id || $percent > $approver->discountLimit() + 0.0001) {
            throw ValidationException::withMessages([
                'approver_password' => "{$approver->name} cannot approve a ".round($percent, 2).'% discount (limit '.$approver->discountLimit().'%).',
            ]);
        }

        return $approver->id;
    }

    private function resolvePatient(array $data, Admin $by, Branch $branch): Patient
    {
        if (filled($data['patient_id'] ?? null)) {
            return $this->accounts->ensureAccount(Patient::findOrFail($data['patient_id']));
        }

        return $this->accounts->findOrCreate($data['patient'] ?? [], $branch->id, $by, (bool) ($data['as_family_member'] ?? false));
    }

    private function addPayment(Invoice $invoice, float $amount, string $method, ?string $reference, Admin $by, ?string $note): Payment
    {
        return Payment::create([
            'receipt_no' => NumberGenerator::receiptNo($invoice->branch),
            'invoice_id' => $invoice->id, 'branch_id' => $invoice->branch_id,
            'type' => 'payment', 'method' => $method, 'amount' => $amount,
            'transaction_ref' => $reference, 'note' => $note, 'received_by' => $by->id,
        ]);
    }

    private function itemRow(array $line, int $sort): array
    {
        $hours = $line['delivery_hours'] ?? null;

        return [
            'item_type' => $line['item_type'],
            'lab_test_id' => $line['lab_test_id'],
            'package_id' => $line['package_id'],
            'department_id' => $line['department_id'],
            'code' => $line['code'],
            'name' => $line['name'],
            'price' => $line['price'],
            'regular_price' => $line['regular_price'],
            'discount_amount' => $line['discount_amount'],
            'net_amount' => $line['net_amount'],
            'delivery_at' => $line['item_type'] === 'package' ? null : now()->addHours($hours ?? (int) Setting::get('default_delivery_hours', 24)),
            'lab_status' => 'pending',
            'sort' => $sort,
        ];
    }
}
