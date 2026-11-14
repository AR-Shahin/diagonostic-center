<?php

namespace App\Services\Reports;

use App\Models\Branch;
use App\Models\CommissionEntry;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Every report comes back in one shape so the screen, the print view and the Excel (CSV) export share it:
 *   ['title', 'note', 'cards' => [[label, value, hint?, tone?]], 'columns' => [key => [label, type]],
 *    'rows' => [[key => value]], 'totals' => [key => value], 'chart' => [labels, series]]
 * Column types: text | int | money | pct | hours.
 */
class ReportService
{
    public const TYPES = [
        'summary' => ['Summary', 'fas fa-chart-pie', 'report-view'],
        'departments' => ['Income by department', 'fas fa-layer-group', 'report-view'],
        'tests' => ['Income by test', 'fas fa-vial', 'report-view'],
        'collection' => ['Collection by staff', 'fas fa-cash-register', 'report-view'],
        'discounts' => ['Discounts by staff', 'fas fa-tags', 'report-view'],
        'referrers' => ['Referrer business', 'fas fa-user-md', 'report-view'],
        'turnaround' => ['Report turnaround', 'fas fa-stopwatch', 'report-view'],
        'dues' => ['Dues by age', 'fas fa-hourglass-half', 'report-view'],
        'profit' => ['Profit & loss', 'fas fa-balance-scale', 'report-profit'],
    ];

    private Carbon $from;

    private Carbon $to;

    private ?int $branchId;

    public function for(Carbon $from, Carbon $to, ?int $branchId): static
    {
        $this->from = $from->copy()->startOfDay();
        $this->to = $to->copy()->endOfDay();
        $this->branchId = $branchId;

        return $this;
    }

    public function build(string $type): array
    {
        $report = $this->{$type}();

        return $report + ['title' => self::TYPES[$type][0], 'note' => null, 'cards' => [], 'totals' => [], 'chart' => null];
    }

    // ------------------------------------------------------------------ helpers

    private function invoices(): Builder
    {
        return Invoice::query()->where('invoices.status', '!=', 'cancelled')
            ->whereBetween('invoices.created_at', [$this->from, $this->to])
            ->when($this->branchId, fn ($q, $id) => $q->where('invoices.branch_id', $id));
    }

    private function payments(): Builder
    {
        return Payment::query()->whereBetween('payments.created_at', [$this->from, $this->to])
            ->when($this->branchId, fn ($q, $id) => $q->where('payments.branch_id', $id));
    }

    private function labItems()
    {
        return DB::table('invoice_items')->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->whereIn('invoice_items.item_type', ['test', 'package_test'])
            ->where('invoices.status', '!=', 'cancelled')
            ->whereBetween('invoices.created_at', [$this->from, $this->to])
            ->when($this->branchId, fn ($q, $id) => $q->where('invoices.branch_id', $id));
    }

    private static function money(float|int|string|null $v): float
    {
        return round((float) $v, 2);
    }

    // ------------------------------------------------------------------ reports

    private function summary(): array
    {
        $bills = $this->invoices()->selectRaw('COUNT(*) c, SUM(subtotal) gross, SUM(discount_amount) disc, SUM(vat_amount) vat, SUM(home_collection_charge) home, SUM(net_amount) net, SUM(due_amount) due')->first();
        $pay = $this->payments()->selectRaw("SUM(CASE WHEN type = 'payment' THEN amount ELSE 0 END) inn, SUM(CASE WHEN type = 'refund' THEN amount ELSE 0 END) outt")->first();
        $oldDue = (float) $this->payments()->where('payments.type', 'payment')
            ->whereHas('invoice', fn ($q) => $q->where('created_at', '<', $this->from))->sum('payments.amount');
        $cancelled = Invoice::query()->where('status', 'cancelled')->whereBetween('cancelled_at', [$this->from, $this->to])
            ->when($this->branchId, fn ($q, $id) => $q->where('branch_id', $id))->count();
        $patients = (int) $this->invoices()->distinct()->count('patient_id');

        $byMethod = $this->payments()->groupBy('method')->selectRaw("method, SUM(CASE WHEN type = 'payment' THEN amount ELSE -amount END) net")->pluck('net', 'method');

        // Day by day
        $billed = $this->invoices()->selectRaw('DATE(created_at) d, COUNT(*) c, SUM(net_amount) net, SUM(discount_amount) disc')->groupBy('d')->get()->keyBy('d');
        $collected = $this->payments()->selectRaw("DATE(created_at) d, SUM(CASE WHEN type = 'payment' THEN amount ELSE -amount END) net")->groupBy('d')->pluck('net', 'd');
        $rows = [];
        for ($day = $this->from->copy(); $day->lte($this->to) && count($rows) < 400; $day->addDay()) {
            $k = $day->toDateString();
            $rows[] = ['date' => $day->format('d M Y, D'), 'bills' => (int) ($billed[$k]->c ?? 0), 'net' => self::money($billed[$k]->net ?? 0),
                'discount' => self::money($billed[$k]->disc ?? 0), 'collected' => self::money($collected[$k] ?? 0)];
        }

        $collectedNet = self::money($pay->inn) - self::money($pay->outt);

        return [
            'cards' => [
                ['Bills', number_format((int) $bills->c), $patients.' patients'],
                ['Net billed', self::taka($bills->net), 'after discount, with VAT & home charge'],
                ['Discount given', self::taka($bills->disc), $bills->gross > 0 ? round($bills->disc / $bills->gross * 100, 1).'% of gross ৳'.number_format((float) $bills->gross) : null],
                ['Collected', self::taka($collectedNet), 'refunds ৳'.number_format((float) $pay->outt).' taken off', 'success'],
                ['Still due on these bills', self::taka($bills->due), null, (float) $bills->due > 0 ? 'danger' : null],
                ['Old dues collected', self::taka($oldDue), 'payments on bills from before this period'],
                ['Cancelled bills', (string) $cancelled, null],
                ...collect($byMethod)->map(fn ($v, $m) => ['By '.(Payment::METHODS[$m] ?? $m), self::taka($v), null])->values()->all(),
            ],
            'columns' => ['date' => ['Day', 'text'], 'bills' => ['Bills', 'int'], 'net' => ['Net billed', 'money'], 'discount' => ['Discount', 'money'], 'collected' => ['Collected', 'money']],
            'rows' => $rows,
            'totals' => ['date' => 'Total', 'bills' => array_sum(array_column($rows, 'bills')), 'net' => array_sum(array_column($rows, 'net')),
                'discount' => array_sum(array_column($rows, 'discount')), 'collected' => array_sum(array_column($rows, 'collected'))],
            'chart' => count($rows) <= 92 ? [
                'labels' => array_map(fn ($r) => substr($r['date'], 0, 6), $rows),
                'series' => ['Net billed' => array_column($rows, 'net'), 'Collected' => array_column($rows, 'collected')],
            ] : null,
        ];
    }

    private function departments(): array
    {
        $rows = $this->labItems()->leftJoin('departments', 'departments.id', '=', 'invoice_items.department_id')
            ->groupBy('invoice_items.department_id', 'departments.name_en')
            ->selectRaw('departments.name_en name, COUNT(*) tests, SUM(invoice_items.price) gross, SUM(invoice_items.discount_amount) disc, SUM(invoice_items.net_amount) net')
            ->orderByDesc('net')->get();
        $total = (float) $rows->sum('net');

        return [
            'note' => 'Package tests count under their own department, at their share of the package price.',
            'columns' => ['name' => ['Department', 'text'], 'tests' => ['Tests done', 'int'], 'gross' => ['Price', 'money'], 'disc' => ['Discount', 'money'], 'net' => ['Income', 'money'], 'share' => ['Share', 'pct']],
            'rows' => $rows->map(fn ($r) => ['name' => $r->name ?? 'No department', 'tests' => (int) $r->tests, 'gross' => self::money($r->gross),
                'disc' => self::money($r->disc), 'net' => self::money($r->net), 'share' => $total > 0 ? round($r->net / $total * 100, 1) : 0])->all(),
            'totals' => ['name' => 'Total', 'tests' => (int) $rows->sum('tests'), 'gross' => self::money($rows->sum('gross')), 'disc' => self::money($rows->sum('disc')), 'net' => self::money($total), 'share' => 100],
            'chart' => ['type' => 'doughnut', 'labels' => $rows->pluck('name')->map(fn ($n) => $n ?? 'No department')->all(), 'series' => ['Income' => $rows->pluck('net')->map(fn ($v) => self::money($v))->all()]],
        ];
    }

    private function tests(): array
    {
        $rows = $this->labItems()->leftJoin('departments', 'departments.id', '=', 'invoice_items.department_id')
            ->groupBy('invoice_items.code', 'invoice_items.name', 'departments.name_en')
            ->selectRaw('invoice_items.code, invoice_items.name, departments.name_en dept, COUNT(*) cnt, SUM(CASE WHEN invoice_items.item_type = \'package_test\' THEN 1 ELSE 0 END) in_pkg, SUM(invoice_items.net_amount) net')
            ->orderByDesc('net')->get();

        return [
            'columns' => ['code' => ['Code', 'text'], 'name' => ['Test', 'text'], 'dept' => ['Department', 'text'], 'cnt' => ['Times done', 'int'], 'in_pkg' => ['In a package', 'int'], 'net' => ['Income', 'money']],
            'rows' => $rows->map(fn ($r) => ['code' => $r->code, 'name' => $r->name, 'dept' => $r->dept, 'cnt' => (int) $r->cnt, 'in_pkg' => (int) $r->in_pkg, 'net' => self::money($r->net)])->all(),
            'totals' => ['code' => 'Total', 'cnt' => (int) $rows->sum('cnt'), 'in_pkg' => (int) $rows->sum('in_pkg'), 'net' => self::money($rows->sum('net'))],
        ];
    }

    private function collection(): array
    {
        $rows = $this->payments()->leftJoin('admins', 'admins.id', '=', 'payments.received_by')
            ->groupBy('payments.received_by', 'admins.name', 'payments.method')
            ->selectRaw("admins.name staff, payments.method, SUM(CASE WHEN payments.type = 'payment' THEN 1 ELSE 0 END) cnt,
                SUM(CASE WHEN payments.type = 'payment' THEN payments.amount ELSE 0 END) inn, SUM(CASE WHEN payments.type = 'refund' THEN payments.amount ELSE 0 END) outt")
            ->orderBy('admins.name')->get();

        return [
            'note' => 'Money received at the counter, by who received it. Compare with each person’s cash closing.',
            'columns' => ['staff' => ['Staff', 'text'], 'method' => ['Method', 'text'], 'cnt' => ['Payments', 'int'], 'inn' => ['Received', 'money'], 'outt' => ['Refunded', 'money'], 'net' => ['Net', 'money']],
            'rows' => $rows->map(fn ($r) => ['staff' => $r->staff ?? '—', 'method' => Payment::METHODS[$r->method] ?? $r->method, 'cnt' => (int) $r->cnt,
                'inn' => self::money($r->inn), 'outt' => self::money($r->outt), 'net' => self::money($r->inn - $r->outt)])->all(),
            'totals' => ['staff' => 'Total', 'cnt' => (int) $rows->sum('cnt'), 'inn' => self::money($rows->sum('inn')), 'outt' => self::money($rows->sum('outt')), 'net' => self::money($rows->sum('inn') - $rows->sum('outt'))],
        ];
    }

    private function discounts(): array
    {
        $rows = $this->invoices()->leftJoin('admins', 'admins.id', '=', 'invoices.created_by')
            ->groupBy('invoices.created_by', 'admins.name')
            ->selectRaw('admins.name staff, COUNT(*) bills, SUM(CASE WHEN invoices.discount_amount > 0 THEN 1 ELSE 0 END) disc_bills,
                SUM(invoices.subtotal) gross, SUM(invoices.discount_amount) disc,
                SUM(CASE WHEN invoices.discount_approved_by IS NOT NULL AND invoices.discount_approved_by <> invoices.created_by THEN 1 ELSE 0 END) approved')
            ->orderByDesc('disc')->get();

        return [
            'note' => '“Needed approval” = bills where the discount was above this person’s own limit and a manager approved it.',
            'columns' => ['staff' => ['Billed by', 'text'], 'bills' => ['Bills', 'int'], 'disc_bills' => ['With discount', 'int'], 'gross' => ['Gross', 'money'], 'disc' => ['Discount', 'money'], 'pct' => ['Discount %', 'pct'], 'approved' => ['Needed approval', 'int']],
            'rows' => $rows->map(fn ($r) => ['staff' => $r->staff ?? '—', 'bills' => (int) $r->bills, 'disc_bills' => (int) $r->disc_bills, 'gross' => self::money($r->gross),
                'disc' => self::money($r->disc), 'pct' => $r->gross > 0 ? round($r->disc / $r->gross * 100, 1) : 0, 'approved' => (int) $r->approved])->all(),
            'totals' => ['staff' => 'Total', 'bills' => (int) $rows->sum('bills'), 'disc_bills' => (int) $rows->sum('disc_bills'), 'gross' => self::money($rows->sum('gross')),
                'disc' => self::money($rows->sum('disc')), 'pct' => $rows->sum('gross') > 0 ? round($rows->sum('disc') / $rows->sum('gross') * 100, 1) : 0, 'approved' => (int) $rows->sum('approved')],
        ];
    }

    private function referrers(): array
    {
        $rows = $this->invoices()->leftJoin('referrers', 'referrers.id', '=', 'invoices.referrer_id')
            ->groupBy('invoices.referrer_id', 'referrers.name')
            ->selectRaw('invoices.referrer_id rid, referrers.name, COUNT(*) bills, SUM(invoices.net_amount) net')
            ->orderByDesc('net')->get();
        $commission = CommissionEntry::query()->join('invoices', 'invoices.id', '=', 'commission_entries.invoice_id')
            ->whereBetween('invoices.created_at', [$this->from, $this->to])
            ->when($this->branchId, fn ($q, $id) => $q->where('invoices.branch_id', $id))
            ->groupBy('commission_entries.referrer_id')->selectRaw('commission_entries.referrer_id rid, SUM(commission_entries.amount) amt')->pluck('amt', 'rid');
        $total = (float) $rows->sum('net');

        return [
            'note' => 'Commission is what these bills have earned so far (only fully paid bills earn).',
            'columns' => ['name' => ['Referred by', 'text'], 'bills' => ['Bills', 'int'], 'net' => ['Business', 'money'], 'share' => ['Share', 'pct'], 'commission' => ['Commission', 'money'], 'avg' => ['Per bill', 'money']],
            'rows' => $rows->map(fn ($r) => ['name' => $r->rid ? $r->name : 'Self (no referrer)', 'bills' => (int) $r->bills, 'net' => self::money($r->net),
                'share' => $total > 0 ? round($r->net / $total * 100, 1) : 0, 'commission' => self::money($commission[$r->rid] ?? 0),
                'avg' => $r->bills ? self::money($r->net / $r->bills) : 0])->all(),
            'totals' => ['name' => 'Total', 'bills' => (int) $rows->sum('bills'), 'net' => self::money($total), 'share' => 100, 'commission' => self::money($commission->sum())],
        ];
    }

    private function turnaround(): array
    {
        $items = $this->labItems()
            ->leftJoin('test_results', 'test_results.invoice_item_id', '=', 'invoice_items.id')
            ->leftJoin('departments', 'departments.id', '=', 'invoice_items.department_id')
            ->get(['departments.name_en as dept', 'invoice_items.created_at', 'invoice_items.delivery_at', 'invoice_items.lab_status', 'test_results.verified_at']);

        $now = now();
        $rows = $items->groupBy(fn ($i) => $i->dept ?? 'No department')->map(function ($group, $dept) use ($now) {
            $done = $group->filter(fn ($i) => $i->verified_at);
            $hours = $done->map(fn ($i) => Carbon::parse($i->created_at)->diffInMinutes(Carbon::parse($i->verified_at)) / 60);
            $onTime = $done->filter(fn ($i) => ! $i->delivery_at || Carbon::parse($i->verified_at)->lte(Carbon::parse($i->delivery_at)))->count();
            $lateOpen = $group->filter(fn ($i) => ! $i->verified_at && in_array($i->lab_status, ['pending', 'collected', 'processing'], true)
                && $i->delivery_at && Carbon::parse($i->delivery_at)->lt($now))->count();

            return ['dept' => $dept, 'tests' => $group->count(), 'done' => $done->count(), 'avg' => $hours->count() ? round($hours->avg(), 1) : null,
                'max' => $hours->count() ? round($hours->max(), 1) : null, 'ontime' => $done->count() ? round($onTime / $done->count() * 100, 1) : null, 'late_open' => $lateOpen];
        })->sortBy('dept')->values();

        $all = $items->filter(fn ($i) => $i->verified_at);
        $onTimeAll = $all->filter(fn ($i) => ! $i->delivery_at || Carbon::parse($i->verified_at)->lte(Carbon::parse($i->delivery_at)))->count();

        return [
            'note' => 'Time from billing to verified report. “On time” = verified before the delivery time promised on the bill.',
            'cards' => [
                ['Reports verified', number_format($all->count()), 'of '.number_format($items->count()).' tests billed'],
                ['On time', $all->count() ? round($onTimeAll / $all->count() * 100, 1).'%' : '—', null, 'success'],
                ['Late and still open', (string) $rows->sum('late_open'), null, $rows->sum('late_open') ? 'danger' : null],
            ],
            'columns' => ['dept' => ['Department', 'text'], 'tests' => ['Tests', 'int'], 'done' => ['Verified', 'int'], 'avg' => ['Average', 'hours'], 'max' => ['Slowest', 'hours'], 'ontime' => ['On time', 'pct'], 'late_open' => ['Late, not ready', 'int']],
            'rows' => $rows->all(),
            'totals' => ['dept' => 'Total', 'tests' => $rows->sum('tests'), 'done' => $rows->sum('done'), 'late_open' => $rows->sum('late_open')],
        ];
    }

    /** All open dues as of the end date, grouped by how old the bill is (period start is ignored). */
    private function dues(): array
    {
        $open = Invoice::query()->whereIn('status', ['unpaid', 'partial'])->where('due_amount', '>', 0)
            ->where('created_at', '<=', $this->to)
            ->when($this->branchId, fn ($q, $id) => $q->where('branch_id', $id))
            ->get(['created_at', 'due_amount']);
        $buckets = ['0–7 days' => [0, 7], '8–30 days' => [8, 30], '31–90 days' => [31, 90], 'Over 90 days' => [91, PHP_INT_MAX]];
        $rows = collect($buckets)->map(function ($range, $label) use ($open) {
            $set = $open->filter(fn ($i) => ($age = (int) $i->created_at->copy()->startOfDay()->diffInDays($this->to->copy()->startOfDay())) >= $range[0] && $age <= $range[1]);

            return ['age' => $label, 'bills' => $set->count(), 'due' => self::money($set->sum('due_amount'))];
        })->values();
        $total = (float) $rows->sum('due');

        return [
            'note' => 'Every unpaid bill up to the end date, whatever the start date. Collect from the Due list.',
            'columns' => ['age' => ['Bill age', 'text'], 'bills' => ['Bills', 'int'], 'due' => ['Due', 'money'], 'share' => ['Share', 'pct']],
            'rows' => $rows->map(fn ($r) => $r + ['share' => $total > 0 ? round($r['due'] / $total * 100, 1) : 0])->all(),
            'totals' => ['age' => 'Total', 'bills' => $rows->sum('bills'), 'due' => self::money($total), 'share' => 100],
        ];
    }

    private function profit(): array
    {
        $income = (float) $this->invoices()->sum('net_amount');
        $commissionQ = fn () => CommissionEntry::query()->join('invoices', 'invoices.id', '=', 'commission_entries.invoice_id')
            ->when($this->branchId, fn ($q, $id) => $q->where('invoices.branch_id', $id));
        $commissionEarned = (float) $commissionQ()->whereBetween('commission_entries.created_at', [$this->from, $this->to])->sum('commission_entries.amount');
        $expenses = Expense::query()->join('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->whereDate('expenses.expense_date', '>=', $this->from)->whereDate('expenses.expense_date', '<=', $this->to)
            ->when($this->branchId, fn ($q, $id) => $q->where('expenses.branch_id', $id))
            ->groupBy('expense_categories.name')->selectRaw('expense_categories.name, SUM(expenses.amount) amt')->orderByDesc('amt')->pluck('amt', 'name');
        $expenseTotal = (float) $expenses->sum();
        $profit = $income - $commissionEarned - $expenseTotal;

        $collected = (float) $this->payments()->sum(DB::raw("CASE WHEN payments.type = 'payment' THEN payments.amount ELSE -payments.amount END"));
        $commissionPaid = (float) $commissionQ()->join('commission_payouts', 'commission_payouts.id', '=', 'commission_entries.payout_id')
            ->whereBetween('commission_payouts.created_at', [$this->from, $this->to])->sum('commission_entries.amount');
        $cash = $collected - $expenseTotal - $commissionPaid;

        $rows = [['line' => 'Income (net billed, cancelled bills left out)', 'amount' => self::money($income)],
            ['line' => 'Less: referral commission earned', 'amount' => -self::money($commissionEarned)]];
        foreach ($expenses as $name => $amt) {
            $rows[] = ['line' => 'Less: '.$name, 'amount' => -self::money($amt)];
        }

        return [
            'note' => 'Billed basis: income when billed. Cash basis: money actually received and paid out in the period. Reagent stock is not tracked, so reagents count when the expense is entered.',
            'cards' => [
                ['Income', self::taka($income), 'net billed'],
                ['Commission', self::taka($commissionEarned), 'earned in period'],
                ['Expenses', self::taka($expenseTotal), $expenses->count().' categories'],
                ['Profit', self::taka($profit), $income > 0 ? round($profit / $income * 100, 1).'% of income' : null, $profit >= 0 ? 'success' : 'danger'],
                ['Cash surplus', self::taka($cash), 'collected ৳'.number_format($collected).' − expenses − commission paid ৳'.number_format($commissionPaid), $cash >= 0 ? 'success' : 'danger'],
            ],
            'columns' => ['line' => ['', 'text'], 'amount' => ['Amount', 'money']],
            'rows' => $rows,
            'totals' => ['line' => 'Profit', 'amount' => self::money($profit)],
        ];
    }

    private static function taka(float|string|null $v): string
    {
        return '৳ '.number_format((float) $v);
    }

    // ------------------------------------------------------------------ dashboard

    public function dashboard(?int $branchId): array
    {
        $today = now();
        $this->for($today, $today, $branchId);
        $todayBills = $this->invoices()->selectRaw('COUNT(*) c, SUM(net_amount) net, SUM(due_amount) due, SUM(discount_amount) disc')->first();
        $todayCollected = (float) $this->payments()->sum(DB::raw("CASE WHEN payments.type = 'payment' THEN payments.amount ELSE -payments.amount END"));
        $todayPatients = (int) $this->invoices()->distinct()->count('patient_id');

        $this->for($today->copy()->startOfMonth(), $today, $branchId);
        $month = (float) $this->invoices()->sum('net_amount');
        $this->for($today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow(), $branchId);
        $lastMonthSameDays = (float) $this->invoices()->sum('net_amount');

        $this->for($today->copy()->subDays(13), $today, $branchId);
        $trend = $this->summary();

        $this->for($today->copy()->startOfMonth(), $today, $branchId);
        $topDepts = array_slice($this->departments()['rows'], 0, 6);

        $branches = $branchId ? [] : Branch::query()->where('status', true)->orderByDesc('is_main')->orderBy('sort')->get(['id', 'code', 'name_en'])
            ->map(function (Branch $b) use ($today) {
                $this->for($today, $today, $b->id);

                return ['name' => $b->name_en, 'bills' => $this->invoices()->count(), 'net' => (float) $this->invoices()->sum('net_amount'),
                    'collected' => (float) $this->payments()->sum(DB::raw("CASE WHEN payments.type = 'payment' THEN payments.amount ELSE -payments.amount END"))];
            })->all();

        $openDues = Invoice::query()->whereIn('status', ['unpaid', 'partial'])->when($branchId, fn ($q, $id) => $q->where('branch_id', $id));

        return [
            'today' => ['bills' => (int) $todayBills->c, 'net' => (float) $todayBills->net, 'due' => (float) $todayBills->due, 'discount' => (float) $todayBills->disc,
                'collected' => $todayCollected, 'patients' => $todayPatients],
            'month' => $month, 'lastMonth' => $lastMonthSameDays,
            'trend' => $trend['chart'],
            'topDepts' => $topDepts,
            'branches' => count($branches) > 1 ? $branches : [],
            'openDue' => (float) (clone $openDues)->sum('due_amount'), 'openDueBills' => (clone $openDues)->count(),
        ];
    }
}
