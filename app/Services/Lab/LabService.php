<?php

namespace App\Services\Lab;

use App\Models\Admin;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabTest;
use App\Models\Sample;
use App\Models\TestParameter;
use App\Models\TestResult;
use App\Services\Billing\NumberGenerator;
use App\Support\FormulaEvaluator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Lab workflow for one bill:
 *   pending (no sample yet) → collected → processing (result entered) → ready (verified) → delivered.
 * Tests without a sample type (X-ray, USG, ECG) skip collection.
 */
class LabService
{
    /** One sample (tube/container) per sample type on the bill. Safe to call again. */
    public function ensureSamples(Invoice $invoice): void
    {
        if ($invoice->isCancelled()) {
            return;
        }

        DB::transaction(function () use ($invoice) {
            $items = $invoice->labItems()->whereNull('sample_id')->with('labTest:id,sample_type_id')->get()
                ->filter(fn (InvoiceItem $i) => $i->labTest?->sample_type_id && $i->lab_status !== 'cancelled');

            foreach ($items->groupBy(fn ($i) => $i->labTest->sample_type_id) as $typeId => $group) {
                $sample = $invoice->samples()->where('sample_type_id', $typeId)->where('status', '!=', 'rejected')->first()
                    ?? $this->newSample($invoice, (int) $typeId);
                InvoiceItem::whereIn('id', $group->pluck('id'))->update(['sample_id' => $sample->id]);
            }
        });
    }

    public function collect(Sample $sample, Admin $by): void
    {
        $this->guardInvoice($sample->invoice);
        if ($sample->status !== 'pending') {
            throw ValidationException::withMessages(['sample' => "Sample {$sample->sample_no} is already {$sample->status}."]);
        }
        DB::transaction(function () use ($sample, $by) {
            $sample->update(['status' => 'collected', 'collected_at' => now(), 'collected_by' => $by->id]);
            $sample->items()->where('lab_status', 'pending')->update(['lab_status' => 'collected']);
        });
    }

    public function receive(Sample $sample, Admin $by): void
    {
        $this->guardInvoice($sample->invoice);
        if (! in_array($sample->status, ['pending', 'collected'], true)) {
            throw ValidationException::withMessages(['sample' => "Sample {$sample->sample_no} cannot be received ({$sample->status})."]);
        }
        DB::transaction(function () use ($sample, $by) {
            $sample->update([
                'status' => 'received', 'received_at' => now(), 'received_by' => $by->id,
                'collected_at' => $sample->collected_at ?? now(), 'collected_by' => $sample->collected_by ?? $by->id,
            ]);
            $sample->items()->where('lab_status', 'pending')->update(['lab_status' => 'collected']);
        });
    }

    /** Bad sample (haemolysed, clotted, too little…): a new barcode is created for re-collection. */
    public function reject(Sample $sample, string $reason, Admin $by): Sample
    {
        $this->guardInvoice($sample->invoice);
        if ($sample->items()->whereIn('lab_status', ['ready', 'delivered'])->exists()) {
            throw ValidationException::withMessages(['sample' => 'Reports from this sample are already verified. Amend them first.']);
        }

        return DB::transaction(function () use ($sample, $reason) {
            $sample->update(['status' => 'rejected', 'rejected_reason' => $reason]);
            $fresh = $this->newSample($sample->invoice, $sample->sample_type_id);
            $sample->items()->update(['sample_id' => $fresh->id, 'lab_status' => 'pending']);

            return $fresh;
        });
    }

    /**
     * Save a result. $input: values[parameter_id], body, culture{...}, comment.
     * Numbers are flagged H/L from the range that fits the patient's sex and age on the bill.
     */
    public function saveResult(InvoiceItem $item, array $input, Admin $by, bool $verify = false): TestResult
    {
        $invoice = $item->invoice;
        $this->guardInvoice($invoice);
        if (! in_array($item->item_type, ['test', 'package_test'], true)) {
            throw ValidationException::withMessages(['item' => 'Results are entered per test.']);
        }
        if (! $item->sample_id && $item->labTest?->sample_type_id) {
            $this->ensureSamples($invoice); // bills made before the lab module
            $item->refresh();
        }
        if ($item->sample_id && ! $item->sample?->isCollected()) {
            throw ValidationException::withMessages(['item' => 'Collect or receive the sample first.']);
        }
        if ($item->result?->isVerified()) {
            throw ValidationException::withMessages(['item' => 'This report is verified. Use “Amend” to change it.']);
        }

        $test = $item->labTest()->with(['parameters' => fn ($q) => $q->where('status', true), 'parameters.unit', 'parameters.referenceRanges'])->firstOrFail();

        return DB::transaction(function () use ($item, $input, $by, $verify, $test, $invoice) {
            $result = TestResult::firstOrNew(['invoice_item_id' => $item->id]);
            $result->fill([
                'report_type' => $test->report_type,
                'comment' => $input['comment'] ?? null,
                'entered_by' => $by->id,
                'entered_at' => now(),
                'status' => 'draft',
            ]);

            if ($test->report_type === 'template') {
                $result->body = ReportHtml::clean((string) ($input['body'] ?? ''));
                if (trim(strip_tags($result->body)) === '') {
                    throw ValidationException::withMessages(['body' => 'Write the report.']);
                }
            }
            if ($test->report_type === 'culture') {
                $result->culture = $this->cleanCulture($input['culture'] ?? []);
            }
            $result->save();

            if ($test->report_type === 'parameter') {
                $rows = $this->parameterRows($test, (array) ($input['values'] ?? []), $invoice);
                if ($verify && $rows->contains(fn ($r) => $r['value'] === null || $r['value'] === '')) {
                    throw ValidationException::withMessages(['values' => 'Fill every value before verifying.']);
                }
                $result->values()->delete();
                $result->values()->createMany($rows->all());
            }

            $item->update(['lab_status' => 'processing']);

            if ($verify) {
                $this->verify($item->fresh(), $by);
            }

            return $result->fresh('values');
        });
    }

    public function verify(InvoiceItem $item, Admin $by): void
    {
        $this->guardInvoice($item->invoice);
        $result = $item->result;
        if (! $result) {
            throw ValidationException::withMessages(['item' => 'Enter the result first.']);
        }
        if ($result->isVerified()) {
            return;
        }
        DB::transaction(function () use ($item, $result, $by) {
            $result->update(['status' => 'verified', 'verified_by' => $by->id, 'verified_at' => now()]);
            $item->update(['lab_status' => 'ready']);
        });
    }

    /** Re-open a verified report for correction. The reason is kept and the count goes up. */
    public function amend(InvoiceItem $item, string $reason, Admin $by): void
    {
        $result = $item->result;
        if (! $result?->isVerified()) {
            throw ValidationException::withMessages(['reason' => 'Only verified reports can be amended.']);
        }
        DB::transaction(function () use ($item, $result, $reason) {
            $result->update(['status' => 'draft', 'verified_by' => null, 'verified_at' => null, 'amendments' => $result->amendments + 1, 'amend_reason' => $reason]);
            $item->update(['lab_status' => 'processing', 'delivered_at' => null, 'delivered_by' => null]);
        });
    }

    /** Hand verified reports to the patient (counter). Respects the "hold report while due" rule. */
    public function deliver(Invoice $invoice, Admin $by): int
    {
        if (! $invoice->canReleaseReport()) {
            throw ValidationException::withMessages(['deliver' => 'Collect the due first. Reports are held while money is owed.']);
        }

        return $invoice->labItems()->where('lab_status', 'ready')->update([
            'lab_status' => 'delivered', 'delivered_at' => now(), 'delivered_by' => $by->id,
        ]);
    }

    /** Build value rows: typed values + calculated formulas, with H/L flags and the range text as printed. */
    public function parameterRows(LabTest $test, array $values, Invoice $invoice): Collection
    {
        $byCode = [];
        foreach ($test->parameters as $p) {
            $raw = $values[$p->id] ?? null;
            $byCode[$p->code] = is_string($raw) ? trim($raw) : $raw;
        }
        foreach ($test->parameters->whereNotNull('formula') as $p) {
            $calc = FormulaEvaluator::evaluate($p->formula, $byCode);
            $byCode[$p->code] = $calc === null ? null : number_format($calc, $p->decimal_places, '.', '');
        }

        return $test->parameters->values()->map(function (TestParameter $p, int $i) use ($byCode, $invoice) {
            $value = $byCode[$p->code];
            $range = $p->rangeFor($invoice->patient_gender, $invoice->patient_age_days);
            $flag = null;
            if ($p->input_type === 'number' && $value !== null && $value !== '' && is_numeric($value) && $range) {
                $flag = $range->flag((float) $value);
            }
            if ($p->input_type === 'number' && $value !== null && $value !== '' && ! is_numeric($value)) {
                throw ValidationException::withMessages(["values.{$p->id}" => "{$p->name} must be a number."]);
            }

            return [
                'test_parameter_id' => $p->id,
                'group_name' => $p->group_name,
                'code' => $p->code,
                'name' => $p->name,
                'unit' => $p->unit?->name,
                'value' => ($value === '' ? null : $value),
                'flag' => $flag,
                'reference_text' => $range?->label(),
                'sort' => $i + 1,
            ];
        });
    }

    private function cleanCulture(array $c): array
    {
        $growth = ($c['growth'] ?? 'no') === 'yes';

        return [
            'growth' => $growth,
            'organism' => $growth ? trim((string) ($c['organism'] ?? '')) : null,
            'colony_count' => $growth ? trim((string) ($c['colony_count'] ?? '')) : null,
            'antibiotics' => $growth ? collect($c['antibiotics'] ?? [])
                ->filter(fn ($a) => filled($a['name'] ?? null) && in_array($a['result'] ?? '', ['S', 'I', 'R'], true))
                ->map(fn ($a) => ['name' => trim($a['name']), 'result' => $a['result']])->values()->all() : [],
        ];
    }

    private function newSample(Invoice $invoice, ?int $typeId): Sample
    {
        $day = now()->format('ymd');

        return $invoice->samples()->create([
            'sample_no' => $day.str_pad((string) NumberGenerator::next('SAMPLE-'.$day), 4, '0', STR_PAD_LEFT),
            'branch_id' => $invoice->branch_id,
            'sample_type_id' => $typeId,
            'status' => 'pending',
        ]);
    }

    private function guardInvoice(Invoice $invoice): void
    {
        if ($invoice->isCancelled()) {
            throw ValidationException::withMessages(['invoice' => 'This bill is cancelled.']);
        }
    }
}
