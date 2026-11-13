<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ResultValue;
use App\Models\Sample;
use App\Services\Lab\LabService;
use App\Support\BranchAccess;
use App\Support\WebsiteIdentity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LabController extends Controller
{
    use CrudHelpers;

    public const STAGES = [
        'collection' => ['Sample collection', 'fas fa-syringe'],
        'results' => ['Result entry', 'fas fa-keyboard'],
        'verify' => ['Verification', 'fas fa-user-check'],
        'ready' => ['Ready to deliver', 'fas fa-file-medical'],
        'delivered' => ['Delivered (7 days)', 'fas fa-check-double'],
    ];

    public function __construct(private LabService $lab)
    {
        parent::__construct();
    }

    /** Work list: one row per test, grouped into stages. */
    public function index(Request $request)
    {
        $this->allow('lab-view');
        $stage = array_key_exists($request->input('stage'), self::STAGES) ? $request->input('stage') : 'results';
        $departmentId = $request->integer('department_id') ?: null;

        if ($request->ajax()) {
            $query = $this->stageQuery($stage, $departmentId)
                ->with(['invoice:id,invoice_no,patient_name,patient_age,patient_gender,created_at,branch_id', 'invoice.branch:id,code', 'sample:id,sample_no,status', 'department:id,name_en'])
                ->orderBy('delivery_at');

            return $this->table($query)
                ->addColumn('patient', fn (InvoiceItem $i) => '<b>'.e($i->invoice->patient_name).'</b><div class="small text-muted">'.e($i->invoice->patient_age).' / '.e(ucfirst($i->invoice->patient_gender)).'</div>')
                ->addColumn('bill', fn (InvoiceItem $i) => '<a class="text-decoration-none" href="'.route('admin.lab.invoice', $i->invoice_id).'">'.e($i->invoice->invoice_no).'</a><div class="small text-muted">'.$i->invoice->created_at->format('d M, h:i A').' · '.e($i->invoice->branch?->code).'</div>')
                ->editColumn('name', fn (InvoiceItem $i) => '<b>'.e($i->name).'</b><div class="small text-muted">'.e($i->department?->name_en).'</div>')
                ->addColumn('sample_no', fn (InvoiceItem $i) => $i->sample ? '<code>'.e($i->sample->sample_no).'</code>' : '<span class="text-muted small">No sample</span>')
                ->editColumn('delivery_at', fn (InvoiceItem $i) => $i->delivery_at
                    ? '<span class="'.($i->isOverdue() ? 'text-danger fw-semibold' : '').'">'.$i->delivery_at->format('d M, h:i A').($i->isOverdue() ? ' <i class="fas fa-exclamation-circle" title="Overdue"></i>' : '').'</span>'
                    : '')
                ->addColumn('status', fn (InvoiceItem $i) => $i->labBadge())
                ->addColumn('actions', fn (InvoiceItem $i) => $this->rowButtons($i))
                ->filterColumn('name', fn ($q, $kw) => $q->where(fn ($w) => $w->where('invoice_items.name', 'like', "%$kw%")
                    ->orWhereHas('invoice', fn ($x) => $x->where('invoice_no', 'like', "%$kw%")->orWhere('patient_name', 'like', "%$kw%")->orWhere('patient_mobile', 'like', "%$kw%"))
                    ->orWhereHas('sample', fn ($x) => $x->where('sample_no', 'like', "$kw%"))))
                ->rawColumns(['patient', 'bill', 'name', 'sample_no', 'delivery_at', 'status', 'actions'])
                ->make(true);
        }

        $counts = collect(self::STAGES)->keys()->mapWithKeys(fn ($s) => [$s => $this->stageQuery($s, $departmentId)->count()]);
        $overdue = $this->baseQuery()->whereIn('lab_status', ['pending', 'collected', 'processing'])->where('delivery_at', '<', now())->count();

        return view('admin.lab.index', [
            'stage' => $stage,
            'counts' => $counts,
            'overdue' => $overdue,
            'departmentId' => $departmentId,
            'departments' => Department::orderBy('sort')->pluck('name_en', 'id'),
        ]);
    }

    /** Barcode scanner / quick search: sample number or invoice number. */
    public function scan(Request $request): RedirectResponse
    {
        $this->allow('lab-view');
        $code = trim((string) $request->input('code'));
        $invoiceId = Sample::where('sample_no', $code)->value('invoice_id')
            ?? Invoice::where('invoice_no', $code)->value('id');

        if (! $invoiceId) {
            $this->errorAlert("Nothing found for “{$code}”.");

            return back();
        }

        return redirect()->route('admin.lab.invoice', $invoiceId);
    }

    /** Everything the lab does for one bill: samples, tests, report. */
    public function invoice(Invoice $invoice)
    {
        $this->allow('lab-view');
        $this->ensureVisible($invoice);
        $this->lab->ensureSamples($invoice);
        $invoice->load(['samples.sampleType', 'samples.items:id,sample_id,name,code', 'labItems.result.verifiedBy:id,name', 'labItems.sample:id,sample_no,status', 'labItems.department:id,name_en', 'branch', 'referrer']);

        return view('admin.lab.invoice', compact('invoice'));
    }

    public function collect(Sample $sample): RedirectResponse
    {
        $this->allow('lab-collect');
        $this->ensureVisible($sample->invoice);
        $this->lab->collect($sample, auth('admin')->user());
        $this->successAlert("Sample {$sample->sample_no} collected.");

        return back();
    }

    public function receive(Sample $sample): RedirectResponse
    {
        $this->allow('lab-collect');
        $this->ensureVisible($sample->invoice);
        $this->lab->receive($sample, auth('admin')->user());
        $this->successAlert("Sample {$sample->sample_no} received in the lab.");

        return back();
    }

    public function reject(Request $request, Sample $sample): RedirectResponse
    {
        $this->allow('lab-collect');
        $this->ensureVisible($sample->invoice);
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);
        $fresh = $this->lab->reject($sample, $data['reason'], auth('admin')->user());
        $this->warningAlert("Sample rejected. Re-collect with the new barcode {$fresh->sample_no}.");

        return back();
    }

    public function labels(Invoice $invoice)
    {
        $this->allow('lab-view');
        $this->ensureVisible($invoice);
        $this->lab->ensureSamples($invoice);
        $invoice->load(['samples' => fn ($q) => $q->where('status', '!=', 'rejected'), 'samples.sampleType', 'samples.items:id,sample_id,code']);

        return view('admin.lab.labels', compact('invoice'));
    }

    public function edit(InvoiceItem $item)
    {
        $this->allow('lab-view');
        abort_unless(in_array($item->item_type, ['test', 'package_test'], true), 404);
        $invoice = $item->invoice;
        $this->ensureVisible($invoice);

        $item->load(['result.values', 'result.verifiedBy:id,name', 'result.enteredBy:id,name', 'sample', 'labTest.parameters' => fn ($q) => $q->where('status', true), 'labTest.parameters.unit', 'labTest.parameters.referenceRanges', 'labTest.reportTemplates']);
        $test = $item->labTest;

        $values = $item->result?->values->keyBy('test_parameter_id') ?? collect();
        $ranges = $test->parameters->mapWithKeys(function ($p) use ($invoice) {
            $r = $p->rangeFor($invoice->patient_gender, $invoice->patient_age_days);

            return [$p->id => ['low' => $r?->low, 'high' => $r?->high, 'label' => $r?->label()]];
        });

        // Last verified values of the same test for this patient (shown as "previous")
        $previous = ResultValue::query()
            ->whereHas('result', fn (Builder $q) => $q->where('status', 'verified')
                ->whereHas('item', fn (Builder $i) => $i->where('lab_test_id', $item->lab_test_id)->where('id', '!=', $item->id)
                    ->whereHas('invoice', fn (Builder $inv) => $inv->where('patient_id', $invoice->patient_id))))
            ->with('result:id,verified_at')->latest('id')->get()->unique('test_parameter_id')->keyBy('test_parameter_id');

        return view('admin.lab.result', compact('item', 'invoice', 'test', 'values', 'ranges', 'previous'));
    }

    public function save(Request $request, InvoiceItem $item): RedirectResponse
    {
        $verify = $request->input('action') === 'verify';
        $this->allow($verify ? 'lab-verify' : 'lab-result');
        $this->ensureVisible($item->invoice);

        $request->validate([
            'values' => ['nullable', 'array'],
            'values.*' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:100000'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'culture' => ['nullable', 'array'],
        ]);

        $this->lab->saveResult($item, $request->only(['values', 'body', 'comment', 'culture']), auth('admin')->user(), $verify);
        $this->successAlert($verify ? "{$item->name}: verified." : "{$item->name}: saved. Waiting for verification.");

        return $request->input('next') === 'list'
            ? redirect()->route('admin.lab.invoice', $item->invoice_id)
            : redirect()->route('admin.lab.result', $item);
    }

    public function verify(InvoiceItem $item): RedirectResponse
    {
        $this->allow('lab-verify');
        $this->ensureVisible($item->invoice);
        $this->lab->verify($item, auth('admin')->user());
        $this->successAlert("{$item->name}: verified.");

        return back();
    }

    public function verifyAll(Invoice $invoice): RedirectResponse
    {
        $this->allow('lab-verify');
        $this->ensureVisible($invoice);
        $items = $invoice->labItems()->where('lab_status', 'processing')->get();
        foreach ($items as $item) {
            $this->lab->verify($item, auth('admin')->user());
        }
        $this->successAlert($items->count().' test(s) verified.');

        return back();
    }

    public function amend(Request $request, InvoiceItem $item): RedirectResponse
    {
        $this->allow('lab-verify');
        $this->ensureVisible($item->invoice);
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);
        $this->lab->amend($item, $data['reason'], auth('admin')->user());
        $this->warningAlert("{$item->name} is open for correction. Verify it again after editing.");

        return redirect()->route('admin.lab.result', $item);
    }

    public function deliver(Invoice $invoice): RedirectResponse
    {
        $this->allow('lab-deliver');
        $this->ensureVisible($invoice);
        $count = $this->lab->deliver($invoice, auth('admin')->user());
        $this->successAlert($count ? "$count report(s) marked as delivered." : 'Nothing ready to deliver.');

        return back();
    }

    /** Printable report: all verified tests of the bill, or the ones ticked. */
    public function report(Request $request, Invoice $invoice)
    {
        $this->allow('lab-view');
        $this->ensureVisible($invoice);

        if (! $invoice->canReleaseReport() && ! $request->boolean('preview')) {
            $this->errorAlert('The report is held until the due is paid. (You can still open a preview for checking.)');

            return redirect()->route('admin.lab.invoice', $invoice);
        }

        return view('reports.document', $this->reportData($invoice, $request->input('items', []), [
            'letterhead' => ! $request->boolean('pad'),
            'preview' => $request->boolean('preview'),
            'backUrl' => route('admin.lab.invoice', $invoice),
        ]));
    }

    public static function reportData(Invoice $invoice, array $itemIds = [], array $extra = []): array
    {
        $invoice->loadMissing(['branch', 'referrer', 'patient']);
        $items = $invoice->labItems()->whereIn('lab_status', ['ready', 'delivered'])
            ->when($itemIds, fn ($q) => $q->whereIn('id', $itemIds))
            ->with(['result.values', 'result.verifiedBy', 'department:id,name_en,sort', 'sample:id,sample_no,collected_at,received_at'])
            ->get()->sortBy(fn ($i) => [$i->department?->sort ?? 999, $i->sort]);

        return $extra + [
            'invoice' => $invoice,
            'items' => $items,
            'site' => WebsiteIdentity::get(),
            'signers' => $items->pluck('result.verifiedBy')->filter()->unique('id')->values(),
            'letterhead' => true,
            'preview' => false,
            'backUrl' => null,
        ];
    }

    private function rowButtons(InvoiceItem $i): string
    {
        $html = '<a class="btn btn-sm btn-outline-secondary me-1" href="'.route('admin.lab.invoice', $i->invoice_id).'" title="Open bill in lab"><i class="fas fa-folder-open"></i></a>';
        if (in_array($i->lab_status, ['collected', 'processing'], true) || ($i->lab_status === 'pending' && ! $i->sample_id)) {
            if ($this->can('lab-result')) {
                $html .= '<a class="btn btn-sm btn-primary" href="'.route('admin.lab.result', $i).'"><i class="fas fa-keyboard me-1"></i>'.($i->lab_status === 'processing' ? 'Review' : 'Enter').'</a>';
            }
        } elseif (in_array($i->lab_status, ['ready', 'delivered'], true)) {
            $html .= '<a class="btn btn-sm btn-outline-primary" href="'.route('admin.lab.result', $i).'">View</a>';
        }

        return $html;
    }

    private function baseQuery(): Builder
    {
        return InvoiceItem::query()->whereIn('item_type', ['test', 'package_test'])
            ->whereHas('invoice', fn (Builder $q) => BranchAccess::scope($q->where('status', '!=', 'cancelled'), auth('admin')->user()));
    }

    private function stageQuery(string $stage, ?int $departmentId): Builder
    {
        return $this->baseQuery()
            ->when($departmentId, fn ($q, $id) => $q->where('department_id', $id))
            ->when($stage === 'collection', fn ($q) => $q->where('lab_status', 'pending')->whereNotNull('sample_id'))
            ->when($stage === 'results', fn ($q) => $q->where(fn ($w) => $w->where('lab_status', 'collected')
                ->orWhere(fn ($x) => $x->where('lab_status', 'pending')->whereNull('sample_id'))))
            ->when($stage === 'verify', fn ($q) => $q->where('lab_status', 'processing'))
            ->when($stage === 'ready', fn ($q) => $q->where('lab_status', 'ready'))
            ->when($stage === 'delivered', fn ($q) => $q->where('lab_status', 'delivered')->where('delivered_at', '>=', now()->subDays(7)));
    }

    private function ensureVisible(Invoice $invoice): void
    {
        abort_unless(BranchAccess::canUse(auth('admin')->user(), $invoice->branch_id), 403);
    }
}
