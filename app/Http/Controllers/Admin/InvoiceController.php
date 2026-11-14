<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InvoiceRequest;
use App\Models\Admin;
use App\Models\Invoice;
use App\Models\LabTest;
use App\Models\Package;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Referrer;
use App\Models\Setting;
use App\Services\Billing\InvoiceService;
use App\Support\BranchAccess;
use App\Support\WebsiteIdentity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    use CrudHelpers;

    public function __construct(private InvoiceService $service)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $this->allow('invoice-view');
        $admin = auth('admin')->user();
        $filters = $this->filters($request);

        if ($request->ajax()) {
            return $this->table($this->filteredQuery($filters, $admin)->with(['branch:id,code', 'referrer:id,name']))
                ->editColumn('invoice_no', fn (Invoice $i) => '<a class="fw-semibold text-decoration-none" href="'.route('admin.invoices.show', $i).'">'.e($i->invoice_no).'</a>'
                    .'<div class="small text-muted">'.$i->created_at->format('d M Y, h:i A').'</div>')
                ->editColumn('patient_name', fn (Invoice $i) => e($i->patient_name).'<div class="small text-muted">'.e($i->patient_mobile).'</div>')
                ->addColumn('ref', fn (Invoice $i) => e($i->referrer?->name ?? 'Self'))
                ->editColumn('net_amount', fn (Invoice $i) => '৳ '.number_format((float) $i->net_amount))
                ->editColumn('paid_amount', fn (Invoice $i) => '৳ '.number_format((float) $i->paid_amount))
                ->editColumn('due_amount', fn (Invoice $i) => (float) $i->due_amount > 0 ? '<b class="text-danger">৳ '.number_format((float) $i->due_amount).'</b>' : '—')
                ->editColumn('status', fn (Invoice $i) => $i->statusBadge())
                ->addColumn('actions', fn (Invoice $i) => '<a class="btn btn-sm btn-outline-primary" href="'.route('admin.invoices.show', $i).'" title="Open"><i class="fas fa-eye"></i></a> '
                    .'<a class="btn btn-sm btn-outline-secondary" target="_blank" href="'.route('admin.invoices.print', $i).'" title="Print"><i class="fas fa-print"></i></a>')
                ->filterColumn('invoice_no', fn ($q, $kw) => $q->where(fn ($w) => $w->where('invoice_no', 'like', "%$kw%")->orWhere('patient_name', 'like', "%$kw%")->orWhere('patient_mobile', 'like', "%$kw%")))
                ->rawColumns(['invoice_no', 'patient_name', 'due_amount', 'status', 'actions'])
                ->make(true);
        }

        return view('admin.invoice.index', [
            'filters' => $filters,
            'summary' => $this->summary($filters, $admin),
            'branches' => BranchAccess::branches($admin),
            'restricted' => BranchAccess::isRestricted($admin),
        ]);
    }

    public function create(Request $request)
    {
        $this->allow('invoice-create');
        $admin = auth('admin')->user();
        $branches = BranchAccess::branches($admin);

        if ($branches->isEmpty()) {
            $this->errorAlert('Add a branch first (Branches menu).');

            return redirect()->route('admin.branches.index');
        }

        $patient = old('patient_id', $request->integer('patient')) ? Patient::find(old('patient_id', $request->integer('patient'))) : null;
        $referrer = old('referrer_id') ? Referrer::find(old('referrer_id')) : null;

        return view('admin.invoice.create', [
            'branches' => $branches,
            'defaultBranch' => $admin->branch_id ?? $branches->first()->id,
            'patient' => $patient ? PatientController::summary($patient) : null,
            'referrer' => $referrer ? ['id' => $referrer->id, 'label' => $referrer->displayName()] : null,
            'cart' => $this->cartFromOld(),
            'popular' => LabTest::query()->where('status', true)->where('is_popular', true)->orderBy('sort')->limit(8)->get(),
            'approvers' => Admin::query()->where('id', '!=', $admin->id)->get(['id', 'name', 'max_discount_percent'])
                ->filter(fn (Admin $a) => $a->discountLimit() > $admin->discountLimit())->sortByDesc(fn (Admin $a) => $a->discountLimit())->values(),
            'rules' => [
                'vat' => (float) Setting::get('vat_percent', 0),
                'home' => (float) Setting::get('home_collection_charge', 0),
                'round' => (bool) Setting::get('round_to_whole_taka', 1),
                'limit' => $admin->discountLimit(),
            ],
        ]);
    }

    public function store(InvoiceRequest $request): RedirectResponse
    {
        $this->allow('invoice-create');
        $invoice = $this->service->create($request->validated(), auth('admin')->user());
        $this->successAlert("Invoice {$invoice->invoice_no} saved.");

        return $request->input('after') === 'print'
            ? redirect()->route('admin.invoices.print', [$invoice, 'auto' => 1])
            : redirect()->route('admin.invoices.show', $invoice);
    }

    public function show(Invoice $invoice)
    {
        $this->allow('invoice-view');
        $this->ensureVisible($invoice);
        $invoice->load(['billedItems.children', 'payments.receiver:id,name', 'branch', 'referrer', 'creator:id,name', 'discountApprover:id,name', 'canceller:id,name', 'patient']);

        return view('admin.invoice.show', compact('invoice'));
    }

    public function print(Invoice $invoice)
    {
        $this->allow('invoice-view');
        $this->ensureVisible($invoice);
        $invoice->load(['billedItems.children', 'branch', 'referrer', 'creator:id,name', 'patient']);

        return view('admin.print.invoice', ['invoice' => $invoice, 'site' => WebsiteIdentity::get()]);
    }

    public function receipt(Payment $payment)
    {
        $this->allow('invoice-view');
        $this->ensureVisible($payment->invoice);
        $payment->load(['invoice.branch', 'receiver:id,name']);

        return view('admin.print.receipt', ['payment' => $payment, 'invoice' => $payment->invoice, 'site' => WebsiteIdentity::get()]);
    }

    public function collect(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->allow('invoice-collect');
        $this->ensureVisible($invoice);
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'transaction_ref' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
        $payment = $this->service->collect($invoice, (float) $data['amount'], $data['method'], $data['transaction_ref'] ?? null, auth('admin')->user(), $data['note'] ?? null);
        $this->successAlert('Received ৳'.number_format((float) $payment->amount)." (receipt {$payment->receipt_no}).");

        return redirect()->route('admin.invoices.show', [$invoice, 'receipt' => $payment->id]);
    }

    public function cancel(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->allow('invoice-cancel');
        $this->ensureVisible($invoice);
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'refund_method' => ['required', Rule::in(array_keys(Payment::METHODS))],
        ]);
        $refund = $this->service->cancel($invoice, $data['reason'], $data['refund_method'], auth('admin')->user());
        $this->successAlert('Invoice cancelled.'.($refund ? ' Refund ৳'.number_format((float) $refund->amount).' recorded.' : ''));

        return redirect()->route('admin.invoices.show', $invoice);
    }

    /** Typeahead for tests and packages at the billing counter. */
    public function items(Request $request): JsonResponse
    {
        $this->allow('invoice-create');
        $q = trim((string) $request->input('q'));
        $branchId = $request->integer('branch_id');
        if (! BranchAccess::canUse(auth('admin')->user(), $branchId)) {
            return response()->json([]);
        }

        $like = fn (Builder $w) => $w->where('name_en', 'like', "%$q%")->orWhere('name_bn', 'like', "%$q%")->orWhere('code', 'like', "$q%");

        $tests = LabTest::query()->where('status', true)->availableAt($branchId)->with('department:id,name_en')
            ->when($q !== '', fn ($b) => $b->where($like))->orderBy('sort')->orderBy('name_en')->limit(15)->get()
            ->map(fn (LabTest $t) => self::testRow($t));

        $packages = Package::query()->where('status', true)->with('labTests:id,name_en')
            ->when($q !== '', fn ($b) => $b->where($like))->orderBy('sort')->limit(8)->get()
            ->filter(fn (Package $p) => $p->isCurrentlyValid())
            ->map(fn (Package $p) => self::packageRow($p));

        return response()->json($packages->values()->concat($tests)->values());
    }

    public static function testRow(LabTest $t): array
    {
        return [
            'type' => 'test', 'id' => $t->id, 'code' => $t->code, 'name' => $t->name_en, 'sub' => $t->department?->name_en,
            'price' => $t->sellingPrice(), 'regular' => (float) $t->price, 'tests' => [],
        ];
    }

    public static function packageRow(Package $p): array
    {
        return [
            'type' => 'package', 'id' => $p->id, 'code' => $p->code, 'name' => $p->name_en, 'sub' => 'Package · '.$p->labTests->count().' tests',
            'price' => (float) $p->price, 'regular' => $p->regularPrice(), 'tests' => $p->labTests->pluck('id')->all(),
            'test_names' => $p->labTests->pluck('name_en')->all(),
        ];
    }

    private function cartFromOld(): array
    {
        return collect(old('items', []))->map(function ($item) {
            if (($item['type'] ?? null) === 'package') {
                $p = Package::with('labTests:id,name_en')->find($item['id'] ?? 0);

                return $p ? self::packageRow($p) : null;
            }
            $t = LabTest::with('department:id,name_en')->find($item['id'] ?? 0);

            return $t ? self::testRow($t) : null;
        })->filter()->values()->all();
    }

    private function filters(Request $request): array
    {
        $from = $request->date('from') ?? now();
        $to = $request->date('to') ?? $from;

        return [
            'from' => Carbon::parse($from)->startOfDay(),
            'to' => Carbon::parse($to)->endOfDay(),
            'branch_id' => $request->integer('branch_id') ?: null,
            'status' => $request->input('status', 'all'),
            'referrer_id' => $request->integer('referrer_id') ?: null,
        ];
    }

    private function filteredQuery(array $f, Admin $admin): Builder
    {
        return Invoice::query()->visibleTo($admin)
            ->when($f['status'] !== 'due', fn ($q) => $q->whereBetween('created_at', [$f['from'], $f['to']]))
            ->when($f['branch_id'], fn ($q, $id) => $q->where('branch_id', $id))
            ->when($f['referrer_id'], fn ($q, $id) => $q->where('referrer_id', $id))
            ->when($f['status'] === 'due', fn ($q) => $q->where('status', '!=', 'cancelled')->where('due_amount', '>', 0))
            ->when(in_array($f['status'], ['unpaid', 'partial', 'paid', 'cancelled'], true), fn ($q) => $q->where('status', $f['status']))
            ->latest('id');
    }

    /** Totals for the chosen dates: bills raised and money actually received (incl. old dues collected). */
    private function summary(array $f, Admin $admin): array
    {
        $bills = Invoice::query()->visibleTo($admin)->whereBetween('created_at', [$f['from'], $f['to']])
            ->when($f['branch_id'], fn ($q, $id) => $q->where('branch_id', $id))
            ->where('status', '!=', 'cancelled');

        $payments = Payment::query()->whereBetween('created_at', [$f['from'], $f['to']])
            ->when(BranchAccess::isRestricted($admin), fn ($q) => $q->where('branch_id', $admin->branch_id))
            ->when($f['branch_id'], fn ($q, $id) => $q->where('branch_id', $id));

        $received = (float) (clone $payments)->where('type', 'payment')->sum('amount');
        $refunded = (float) (clone $payments)->where('type', 'refund')->sum('amount');

        return [
            'count' => (clone $bills)->count(),
            'gross' => (float) (clone $bills)->sum('subtotal'),
            'discount' => (float) (clone $bills)->sum('discount_amount'),
            'net' => (float) (clone $bills)->sum('net_amount'),
            'due' => (float) (clone $bills)->sum('due_amount'),
            'received' => $received,
            'refunded' => $refunded,
            'cash_in_hand' => $received - $refunded,
            'all_due' => (float) Invoice::query()->visibleTo($admin)->where('status', '!=', 'cancelled')->sum('due_amount'),
        ];
    }

    private function ensureVisible(Invoice $invoice): void
    {
        abort_unless(BranchAccess::canUse(auth('admin')->user(), $invoice->branch_id), 403);
    }
}
