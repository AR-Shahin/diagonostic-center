<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CrudHelpers;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payment;
use App\Support\BranchAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    use CrudHelpers;

    public function index(Request $request)
    {
        $this->allow('expense-view');
        $admin = auth('admin')->user();
        $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()));
        $to = Carbon::parse($request->input('to', now()->toDateString()));
        $categoryId = $request->integer('category_id') ?: null;
        $branchId = $request->integer('branch_id') ?: null;

        $query = Expense::query()->visibleTo($admin)->with(['category:id,name', 'branch:id,code', 'creator:id,name'])
            ->whereDate('expense_date', '>=', $from)->whereDate('expense_date', '<=', $to)
            ->when($categoryId, fn ($q, $id) => $q->where('expense_category_id', $id))
            ->when($branchId, fn ($q, $id) => $q->where('branch_id', $id));

        if ($request->ajax()) {
            return $this->table($query->latest('expense_date')->latest('id'))
                ->editColumn('expense_date', fn (Expense $e) => $e->expense_date->format('d M Y'))
                ->addColumn('category', fn (Expense $e) => e($e->category?->name))
                ->addColumn('branch', fn (Expense $e) => e($e->branch?->code))
                ->editColumn('amount', fn (Expense $e) => '<b>৳ '.number_format((float) $e->amount, 2).'</b>')
                ->editColumn('method', fn (Expense $e) => e(Payment::METHODS[$e->method] ?? $e->method).($e->reference ? ' <span class="text-muted small">('.e($e->reference).')</span>' : ''))
                ->addColumn('by', fn (Expense $e) => e($e->creator?->name))
                ->addColumn('actions', fn (Expense $e) => $this->rowActions('expense', route('admin.expenses.destroy', $e), route('admin.expenses.update', $e),
                    $e->only(['branch_id', 'expense_category_id', 'amount', 'method', 'reference', 'paid_to', 'note']) + ['expense_date' => $e->expense_date->toDateString()], $e->category?->name.' ৳'.$e->amount))
                ->rawColumns(['amount', 'method', 'actions'])
                ->make(true);
        }

        $byCategory = (clone $query)->selectRaw('expense_category_id, SUM(amount) as total')->groupBy('expense_category_id')->with('category:id,name')->get()->sortByDesc('total');

        return view('admin.expense.index', [
            'from' => $from, 'to' => $to, 'categoryId' => $categoryId, 'branchId' => $branchId,
            'total' => (float) (clone $query)->sum('amount'),
            'byCategory' => $byCategory,
            'categories' => ExpenseCategory::where('status', true)->orderBy('name')->pluck('name', 'id'),
            'allCategories' => ExpenseCategory::withCount('expenses')->orderBy('name')->get(),
            'branches' => BranchAccess::branches($admin)->pluck('name_en', 'id'),
            'defaultBranch' => $admin->branch_id ?? BranchAccess::branches($admin)->first()?->id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->allow('expense-create');
        Expense::create($this->validated($request) + ['created_by' => auth('admin')->id()]);
        $this->createdAlert();

        return back();
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $this->allow('expense-edit');
        abort_unless(BranchAccess::canUse(auth('admin')->user(), $expense->branch_id), 403);
        $expense->update($this->validated($request));
        $this->updatedAlert();

        return back();
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->allow('expense-delete');
        abort_unless(BranchAccess::canUse(auth('admin')->user(), $expense->branch_id), 403);
        $expense->delete();
        $this->deletedAlert();

        return back();
    }

    /** Categories are managed on the same page. */
    public function storeCategory(Request $request): RedirectResponse
    {
        $this->allow('expense-edit');
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:expense_categories,name']]);
        ExpenseCategory::create($data);
        $this->createdAlert();

        return back();
    }

    public function toggleCategory(ExpenseCategory $category): RedirectResponse
    {
        $this->allow('expense-edit');
        $category->update(['status' => ! $category->status]);
        $this->updatedAlert();

        return back();
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'reference' => ['nullable', 'string', 'max:100'],
            'paid_to' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        abort_unless(BranchAccess::canUse(auth('admin')->user(), (int) $data['branch_id']), 403);

        return $data;
    }
}
