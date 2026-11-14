<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\CashClosing;
use App\Services\Accounts\CashClosingService;
use App\Support\BranchAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CashClosingController extends Controller
{
    use CrudHelpers;

    public function __construct(private CashClosingService $closings)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $this->allow('cashclosing-create');
        $admin = auth('admin')->user();
        $date = Carbon::parse($request->input('date', now()->toDateString()));
        $mine = CashClosing::where('admin_id', $admin->id)->whereDate('closing_date', $date)->first();
        $last = CashClosing::where('admin_id', $admin->id)->whereDate('closing_date', '<', $date)->latest('closing_date')->first();

        $list = CashClosing::query()->with(['admin:id,name', 'branch:id,code', 'verifier:id,name'])
            ->when(! $this->can('cashclosing-view'), fn ($q) => $q->where('admin_id', $admin->id))
            ->when($this->can('cashclosing-view'), fn ($q) => BranchAccess::scope($q, $admin))
            ->latest('closing_date')->latest('id')->limit(60)->get();

        return view('admin.cash-closing.index', [
            'date' => $date,
            'summary' => $this->closings->summary($admin, $date),
            'mine' => $mine,
            'suggestedOpening' => $last ? (float) $last->counted_cash : 0,
            'list' => $list,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->allow('cashclosing-create');
        $data = $request->validate([
            'closing_date' => ['required', 'date'],
            'opening_cash' => ['required', 'numeric', 'min:0'],
            'counted_cash' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        $closing = $this->closings->close(auth('admin')->user(), Carbon::parse($data['closing_date']), (float) $data['opening_cash'], (float) $data['counted_cash'], $data['note'] ?? null);

        $diff = (float) $closing->difference;
        $diff == 0.0
            ? $this->successAlert('Day closed. Cash matches.')
            : $this->warningAlert('Day closed with a difference of ৳'.number_format($diff, 2).'. A manager will check it.');

        return redirect()->route('admin.cash-closings.index', ['date' => $data['closing_date']]);
    }

    public function verify(CashClosing $closing): RedirectResponse
    {
        $this->allow('cashclosing-verify');
        abort_unless(BranchAccess::canUse(auth('admin')->user(), $closing->branch_id), 403);
        $this->closings->verify($closing, auth('admin')->user());
        $this->successAlert('Closing checked.');

        return back();
    }
}
