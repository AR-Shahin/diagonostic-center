<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\CommissionPayout;
use App\Models\Department;
use App\Models\LabTest;
use App\Models\Payment;
use App\Models\Referrer;
use App\Models\ReferrerRate;
use App\Models\Setting;
use App\Services\Accounts\CommissionService;
use App\Support\WebsiteIdentity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class CommissionController extends Controller
{
    use CrudHelpers;

    public function __construct(private CommissionService $commission)
    {
        parent::__construct();
    }

    /** All referrers: earned in the period, paid, and still owed. */
    public function index(Request $request)
    {
        $this->allow('commission-view');
        [$from, $to] = $this->period($request);

        $referrers = Referrer::query()
            ->withSum(['commissionEntries as earned' => fn ($q) => $q->whereBetween('created_at', [$from, $to])], 'amount')
            ->withSum(['commissionEntries as unpaid' => fn ($q) => $q->whereNull('payout_id')], 'amount')
            ->withSum(['payouts as paid' => fn ($q) => $q->whereBetween('created_at', [$from, $to])], 'amount')
            ->withCount(['invoices as bills' => fn ($q) => $q->where('status', '!=', 'cancelled')->whereBetween('created_at', [$from, $to])])
            ->withSum(['invoices as business' => fn ($q) => $q->where('status', '!=', 'cancelled')->whereBetween('created_at', [$from, $to])], 'net_amount')
            ->orderBy('name')->get()
            ->filter(fn ($r) => $r->bills || (float) $r->unpaid != 0 || (float) $r->earned != 0);

        return view('admin.commission.index', [
            'referrers' => $referrers, 'from' => $from, 'to' => $to,
            'enabled' => (bool) Setting::get('commission_enabled', 1),
            'bearer' => config('diagnostic.discount_bearers')[Setting::get('discount_bearer', 'company')] ?? '',
        ]);
    }

    public function rates(Request $request)
    {
        $this->allow('commission-view');
        $referrer = $request->integer('referrer') ? Referrer::findOrFail($request->integer('referrer')) : null;

        return view('admin.commission.rates', [
            'referrer' => $referrer,
            'rates' => ReferrerRate::query()->with(['department:id,name_en', 'labTest:id,name_en,code'])
                ->where('referrer_id', $referrer?->id)->get()->sortByDesc(fn ($r) => $r->specificity()),
            'defaults' => $referrer ? ReferrerRate::query()->whereNull('referrer_id')->with(['department:id,name_en', 'labTest:id,name_en'])->get() : collect(),
            'departments' => Department::orderBy('sort')->pluck('name_en', 'id'),
            'tests' => LabTest::where('status', true)->orderBy('name_en')->get(['id', 'code', 'name_en'])->mapWithKeys(fn ($t) => [$t->id => $t->name_en.' ('.$t->code.')']),
            'referrers' => Referrer::where('status', true)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function saveRate(Request $request): RedirectResponse
    {
        $this->allow('commission-rates');
        $data = $request->validate([
            'referrer_id' => ['nullable', 'exists:referrers,id'],
            'scope' => ['required', Rule::in(['all', 'department', 'test'])],
            'department_id' => ['required_if:scope,department', 'nullable', 'exists:departments,id'],
            'lab_test_id' => ['required_if:scope,test', 'nullable', 'exists:lab_tests,id'],
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0', $request->input('type') === 'percent' ? 'max:100' : 'max:1000000'],
        ]);

        ReferrerRate::updateOrCreate(
            [
                'referrer_id' => $data['referrer_id'] ?? null,
                'department_id' => $data['scope'] === 'department' ? $data['department_id'] : null,
                'lab_test_id' => $data['scope'] === 'test' ? $data['lab_test_id'] : null,
            ],
            ['type' => $data['type'], 'value' => $data['value']]
        );
        $this->successAlert('Rate saved. It applies to bills paid from now on.');

        return back();
    }

    public function deleteRate(ReferrerRate $rate): RedirectResponse
    {
        $this->allow('commission-rates');
        $rate->delete();
        $this->deletedAlert();

        return back();
    }

    public function statement(Request $request, Referrer $referrer)
    {
        $this->allow('commission-view');
        [$from, $to] = $this->period($request);

        $entries = $referrer->commissionEntries()->whereBetween('created_at', [$from, $to])
            ->with(['invoice:id,invoice_no,patient_name,created_at', 'item:id,name', 'payout:id,payout_no'])->latest('id')->get();

        return view('admin.commission.statement', [
            'referrer' => $referrer, 'entries' => $entries, 'from' => $from, 'to' => $to,
            'unpaid' => $referrer->unpaidCommission(),
            'payouts' => $referrer->payouts()->with('payer:id,name')->latest('id')->limit(24)->get(),
            'print' => $request->boolean('print'),
            'site' => WebsiteIdentity::get(),
        ]);
    }

    public function pay(Request $request, Referrer $referrer): RedirectResponse
    {
        $this->allow('commission-pay');
        $data = $request->validate([
            'period_to' => ['required', 'date', 'before_or_equal:today'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'reference' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
        $payout = $this->commission->pay($referrer, Carbon::parse($data['period_to']), $data['method'], $data['reference'] ?? null, $data['note'] ?? null, auth('admin')->user());
        $this->successAlert('Paid ৳'.number_format((float) $payout->amount)." ({$payout->payout_no}).");

        return redirect()->route('admin.commission.voucher', $payout);
    }

    public function voucher(CommissionPayout $payout)
    {
        $this->allow('commission-view');
        $payout->load(['referrer', 'payer:id,name', 'entries.invoice:id,invoice_no,patient_name,created_at', 'entries.item:id,name']);

        return view('admin.commission.voucher', ['payout' => $payout, 'site' => WebsiteIdentity::get()]);
    }

    private function period(Request $request): array
    {
        $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->input('to', now()->toDateString()))->endOfDay();

        return [$from, $to];
    }
}
