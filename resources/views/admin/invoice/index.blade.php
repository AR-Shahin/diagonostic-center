@extends('admin.layouts.master')

@php
    $isDue = $filters['status'] === 'due';
@endphp
@section('title', $isDue ? 'Due list' : 'Invoices')

@push('css')
    <x-utility.datatable-css />
    <style>
        .stat-tile { border-radius: .9rem; padding: .9rem 1rem; background: #fff; border: 1px solid var(--bs-border-color); height: 100%; }
        .stat-tile small { color: var(--bs-secondary-color); display: block; }
        .stat-tile b { font-size: 1.35rem; font-variant-numeric: tabular-nums; }
    </style>
@endpush

@section('master_content')
    <x-admin.page-header :title="$isDue ? 'Due list' : 'Invoices'" :icon="$isDue ? 'fas fa-hand-holding-usd' : 'fas fa-file-invoice-dollar'"
        :subtitle="$isDue ? 'Every unpaid or partly paid bill, oldest first in the list order you choose.' : $filters['from']->format('d M Y').($filters['from']->isSameDay($filters['to']) ? '' : ' – '.$filters['to']->format('d M Y'))">
        @if (in_array('invoice-create', $permissions))
            <a class="btn btn-sm btn-success" href="{{ route('admin.invoices.create') }}"><i class="fas fa-cash-register me-1"></i> New bill</a>
        @endif
    </x-admin.page-header>

    <form class="card mb-3" method="GET">
        <div class="card-body row g-2 align-items-end">
            @unless ($isDue)
                <div class="col-6 col-md-2"><label class="form-label small mb-1" for="from">From</label><input type="date" class="form-control form-control-sm" id="from" name="from" value="{{ $filters['from']->toDateString() }}"></div>
                <div class="col-6 col-md-2"><label class="form-label small mb-1" for="to">To</label><input type="date" class="form-control form-control-sm" id="to" name="to" value="{{ $filters['to']->toDateString() }}"></div>
            @endunless
            @unless ($restricted)
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1" for="branch">Branch</label>
                    <select class="form-select form-select-sm" id="branch" name="branch_id">
                        <option value="">All branches</option>
                        @foreach ($branches as $b)<option value="{{ $b->id }}" @selected($filters['branch_id'] == $b->id)>{{ $b->name_en }}</option>@endforeach
                    </select>
                </div>
            @endunless
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1" for="status">Status</label>
                <select class="form-select form-select-sm" id="status" name="status">
                    <option value="all">All</option>
                    <option value="due" @selected($isDue)>With due</option>
                    @foreach (\App\Models\Invoice::STATUSES as $k => $v)<option value="{{ $k }}" @selected($filters['status'] === $k)>{{ $v }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-auto d-flex gap-2">
                <button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i> Apply</button>
                @unless ($isDue)
                    <a class="btn btn-sm btn-light" href="{{ route('admin.invoices.index', ['from' => now()->toDateString(), 'to' => now()->toDateString()]) }}">Today</a>
                    <a class="btn btn-sm btn-light" href="{{ route('admin.invoices.index', ['from' => now()->startOfMonth()->toDateString(), 'to' => now()->toDateString()]) }}">This month</a>
                @endunless
            </div>
        </div>
    </form>

    @unless ($isDue)
        <div class="row g-2 mb-3">
            <div class="col-6 col-lg-2"><div class="stat-tile"><small>Bills</small><b>{{ number_format($summary['count']) }}</b></div></div>
            <div class="col-6 col-lg-2"><div class="stat-tile"><small>Gross</small><b>৳ {{ number_format($summary['gross']) }}</b></div></div>
            <div class="col-6 col-lg-2"><div class="stat-tile"><small>Discount</small><b class="text-danger">৳ {{ number_format($summary['discount']) }}</b></div></div>
            <div class="col-6 col-lg-2"><div class="stat-tile"><small>Net billed</small><b>৳ {{ number_format($summary['net']) }}</b></div></div>
            <div class="col-6 col-lg-2"><div class="stat-tile"><small>Cash received <i class="fas fa-info-circle" title="All money received in these dates (incl. old dues), minus refunds"></i></small><b class="text-success">৳ {{ number_format($summary['cash_in_hand']) }}</b></div></div>
            <div class="col-6 col-lg-2"><div class="stat-tile"><small>Due on these bills</small><b class="text-danger">৳ {{ number_format($summary['due']) }}</b></div></div>
        </div>
    @else
        <div class="alert alert-danger d-flex justify-content-between align-items-center"><span><i class="fas fa-exclamation-circle me-1"></i> Total outstanding due</span><b class="fs-4">৳ {{ number_format($summary['all_due']) }}</b></div>
    @endunless

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover align-middle w-100" id="invoiceTable">
                <thead><tr><th>#</th><th>Invoice</th><th>Patient</th><th>Ref. by</th><th class="text-end">Net</th><th class="text-end">Paid</th><th class="text-end">Due</th><th>Status</th><th class="text-end"></th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@push('script')
    @php
        $tableFilters = [
            'from' => $filters['from']->toDateString(), 'to' => $filters['to']->toDateString(),
            'branch_id' => $filters['branch_id'], 'status' => $filters['status'],
        ];
    @endphp
    <x-utility.datatable-js />
    <script>
        new DataTable('#invoiceTable', {
            processing: true, serverSide: true, searchDelay: 500, pageLength: 25, order: [],
            ajax: { url: "{{ route('admin.invoices.index') }}", data: (d) => Object.assign(d, {!! json_encode($tableFilters) !!}) },
            language: { search: 'Search (invoice, name, mobile):' },
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'invoice_no', name: 'invoice_no' },
                { data: 'patient_name', name: 'patient_name', orderable: false },
                { data: 'ref', orderable: false, searchable: false },
                { data: 'net_amount', name: 'net_amount', searchable: false, className: 'text-end' },
                { data: 'paid_amount', name: 'paid_amount', searchable: false, className: 'text-end' },
                { data: 'due_amount', name: 'due_amount', searchable: false, className: 'text-end' },
                { data: 'status', name: 'status', searchable: false },
                { data: 'actions', orderable: false, searchable: false, className: 'text-end text-nowrap' },
            ],
        });
    </script>
@endpush
