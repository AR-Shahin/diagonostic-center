<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\Referrer;
use App\Services\Billing\NumberGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReferrerController extends Controller
{
    use CrudHelpers;

    public function index(Request $request)
    {
        $this->allow('referrer-view');

        if ($request->ajax()) {
            return $this->table(Referrer::query()->withCount('invoices')->latest('id'))
                ->editColumn('name', fn (Referrer $r) => e($r->name).'<div class="small text-muted">'.e(trim($r->degree.' '.($r->organisation ? '· '.$r->organisation : ''))).'</div>')
                ->editColumn('type', fn (Referrer $r) => e(Referrer::TYPES[$r->type] ?? $r->type))
                ->addColumn('commission', fn (Referrer $r) => $this->statusBadge($r->commission_enabled, 'Yes', 'No'))
                ->addColumn('state', fn (Referrer $r) => $this->statusBadge($r->status))
                ->addColumn('actions', fn (Referrer $r) => $this->commissionLinks($r).$this->rowActions(
                    'referrer', route('admin.referrers.destroy', $r), route('admin.referrers.update', $r),
                    $r->only(['type', 'name', 'degree', 'organisation', 'mobile', 'address', 'commission_enabled', 'status']), $r->name
                ))
                ->rawColumns(['name', 'commission', 'state', 'actions'])
                ->make(true);
        }

        return view('admin.referrer.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->allow('referrer-create');
        $data = $this->validated($request);
        DB::transaction(fn () => Referrer::create($data + ['code' => NumberGenerator::referrerCode()]));
        $this->createdAlert();

        return back();
    }

    public function update(Request $request, Referrer $referrer): RedirectResponse
    {
        $this->allow('referrer-edit');
        $referrer->update($this->validated($request));
        $this->updatedAlert();

        return back();
    }

    public function destroy(Referrer $referrer): RedirectResponse
    {
        $this->allow('referrer-delete');

        return $this->safeDelete($referrer, 'This referrer is in use.');
    }

    /** Typeahead for the billing counter. */
    public function search(Request $request): JsonResponse
    {
        $this->allow('invoice-create');
        $q = trim((string) $request->input('q'));

        $rows = Referrer::query()->where('status', true)
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', "%$q%")->orWhere('mobile', 'like', "%$q%")
                ->orWhere('code', 'like', "%$q%")->orWhere('organisation', 'like', "%$q%")))
            ->orderBy('name')->limit(15)->get();

        return response()->json($rows->map(fn (Referrer $r) => [
            'id' => $r->id, 'label' => $r->displayName(), 'meta' => trim(($r->organisation ?? '').' '.($r->mobile ?? '')),
        ]));
    }

    private function commissionLinks(Referrer $r): string
    {
        $links = '';
        if ($this->can('commission-view')) {
            $links .= '<a class="btn btn-sm btn-outline-primary me-1" href="'.route('admin.commission.statement', $r).'" title="Commission statement"><i class="fas fa-file-invoice-dollar"></i></a>';
        }
        if ($this->can('commission-rates')) {
            $links .= '<a class="btn btn-sm btn-outline-secondary me-1" href="'.route('admin.commission.rates', ['referrer' => $r->id]).'" title="Commission rates"><i class="fas fa-percent"></i></a>';
        }

        return $links;
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(array_keys(Referrer::TYPES))],
            'name' => ['required', 'string', 'max:255'],
            'degree' => ['nullable', 'string', 'max:255'],
            'organisation' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]) + ['commission_enabled' => $request->boolean('commission_enabled'), 'status' => $request->boolean('status')];
    }
}
