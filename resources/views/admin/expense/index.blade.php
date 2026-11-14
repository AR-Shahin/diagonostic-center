@extends('admin.layouts.master')

@section('title', 'Expenses')

@push('css')
    <x-utility.datatable-css />
    <style>.card:hover { transform: none; } .num { font-variant-numeric: tabular-nums; }</style>
@endpush

@section('master_content')
    <x-admin.page-header title="Expenses" icon="fas fa-receipt" subtitle="Rent, salary, reagents and every other cost. Cash expenses are taken out of the cash closing of the person who entered them.">
        @if (in_array('expense-edit', $permissions))
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#catModal"><i class="fas fa-tags me-1"></i> Categories</button>
        @endif
        @if (in_array('expense-create', $permissions))
            <button class="btn btn-sm btn-success" data-crud-create data-action="{{ route('admin.expenses.store') }}" data-title="Add expense"><i class="fas fa-plus me-1"></i> Add expense</button>
        @endif
    </x-admin.page-header>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="d-flex flex-wrap gap-2 align-items-end">
                <div><label class="form-label small mb-1" for="eFrom">From</label><input type="date" class="form-control form-control-sm" id="eFrom" name="from" value="{{ $from->toDateString() }}"></div>
                <div><label class="form-label small mb-1" for="eTo">To</label><input type="date" class="form-control form-control-sm" id="eTo" name="to" value="{{ $to->toDateString() }}"></div>
                <div>
                    <label class="form-label small mb-1" for="eCat">Category</label>
                    <select class="form-select form-select-sm" id="eCat" name="category_id">
                        <option value="">All</option>
                        @foreach ($allCategories as $c)<option value="{{ $c->id }}" @selected($categoryId === $c->id)>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                @if ($branches->count() > 1)
                    <div>
                        <label class="form-label small mb-1" for="eBranch">Branch</label>
                        <select class="form-select form-select-sm" id="eBranch" name="branch_id">
                            <option value="">All</option>
                            @foreach ($branches as $id => $name)<option value="{{ $id }}" @selected($branchId === $id)>{{ $name }}</option>@endforeach
                        </select>
                    </div>
                @endif
                <button class="btn btn-sm btn-primary">Apply</button>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-sm table-hover align-middle data-table w-100">
                        <thead><tr><th>#</th><th>Date</th><th>Category</th><th>Branch</th><th>Paid to</th><th>Method</th><th>Amount</th><th>Entered by</th><th class="text-end">Actions</th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body">
                    <small class="text-muted">Total in period</small>
                    <div class="fs-3 fw-bold num">৳ {{ number_format($total) }}</div>
                    <hr>
                    @forelse ($byCategory as $row)
                        @php
                            $pct = $total > 0 ? round($row->total / $total * 100) : 0;
                        @endphp
                        <div class="d-flex justify-content-between small"><span>{{ $row->category?->name }}</span><span class="num">৳ {{ number_format((float) $row->total) }}</span></div>
                        <div class="progress mb-2" style="height:5px" role="progressbar" aria-label="{{ $row->category?->name }}" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" style="width: {{ $pct }}%"></div></div>
                    @empty
                        <p class="text-muted small mb-0">No expenses in this period.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <x-admin.crud-modal title="Add expense">
        <div class="row">
            <div class="col-md-6"><x-admin.input name="expense_date" label="Date" type="date" :default="now()->toDateString()" :max="now()->toDateString()" required /></div>
            <div class="col-md-6"><x-admin.select name="expense_category_id" label="Category" :options="$categories" placeholder="Select" required /></div>
            <div class="col-md-6"><x-admin.input name="amount" label="Amount (৳)" type="number" step="0.01" min="1" required /></div>
            <div class="col-md-6"><x-admin.select name="branch_id" label="Branch" :options="$branches" :default="$defaultBranch" required /></div>
            <div class="col-md-6"><x-admin.select name="method" label="Paid by" :options="\App\Models\Payment::METHODS" default="cash" required /></div>
            <div class="col-md-6"><x-admin.input name="reference" label="Reference (cheque / txn ID / memo no)" /></div>
            <div class="col-12"><x-admin.input name="paid_to" label="Paid to" placeholder="Landlord, DESCO, supplier name…" /></div>
            <div class="col-12"><x-admin.textarea name="note" label="Note" rows="2" /></div>
        </div>
        <p class="form-text mb-0">Cash expenses reduce your cash in hand at day closing.</p>
    </x-admin.crud-modal>

    @if (in_array('expense-edit', $permissions))
        <div class="modal fade" id="catModal" tabindex="-1" aria-labelledby="catTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title" id="catTitle">Expense categories</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('admin.expenses.categories.store') }}" class="input-group mb-3">
                            @csrf
                            <input class="form-control" name="name" placeholder="New category" aria-label="New category" required maxlength="100">
                            <button class="btn btn-success">Add</button>
                        </form>
                        <ul class="list-group">
                            @foreach ($allCategories as $c)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span @class(['text-muted text-decoration-line-through' => ! $c->status])>{{ $c->name }} <small class="text-muted">({{ $c->expenses_count }})</small></span>
                                    <form method="POST" action="{{ route('admin.expenses.categories.toggle', $c) }}">@csrf<button class="btn btn-sm btn-light">{{ $c->status ? 'Hide' : 'Show' }}</button></form>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('script')
    <x-utility.datatable-js />
    <script src="{{ asset('custom/js/crud-modal.js') }}"></script>
    <script>
        initalizeDatatable({{ \Illuminate\Support\Js::from(route('admin.expenses.index', request()->only(['from', 'to', 'category_id', 'branch_id']))) }}, [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'expense_date', name: 'expense_date', searchable: false },
            { data: 'category', orderable: false, searchable: false },
            { data: 'branch', orderable: false, searchable: false },
            { data: 'paid_to', name: 'paid_to' },
            { data: 'method', name: 'method' },
            { data: 'amount', name: 'amount', className: 'text-end' },
            { data: 'by', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' },
        ]);
    </script>
@endpush
