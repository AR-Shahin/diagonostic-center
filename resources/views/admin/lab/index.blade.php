@extends('admin.layouts.master')

@section('title', 'Lab work list')

@push('css')
    <x-utility.datatable-css />
    <style>
        .stage-tabs { display: grid; grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr)); gap: .6rem; }
        .stage-tab { display: flex; align-items: center; gap: .7rem; padding: .8rem 1rem; border-radius: .9rem; border: 1px solid var(--bs-border-color); background: #fff; color: inherit; text-decoration: none; }
        .stage-tab b { font-size: 1.4rem; line-height: 1; font-variant-numeric: tabular-nums; }
        .stage-tab small { display: block; color: var(--bs-secondary-color); }
        .stage-tab i { font-size: 1.2rem; color: var(--bs-primary); width: 1.4rem; text-align: center; }
        .stage-tab.active { border-color: var(--bs-primary); box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .12); background: var(--bs-primary-bg-subtle); }
        .scan-box input { font-family: ui-monospace, monospace; font-size: 1.05rem; }
    </style>
@endpush

@section('master_content')
    <x-admin.page-header title="Lab work list" icon="fas fa-microscope" subtitle="Every test moves left to right: sample → result → verification → delivery.">
        @if ($overdue)
            <span class="badge text-bg-danger align-self-center fs-6"><i class="fas fa-clock me-1"></i> {{ $overdue }} overdue</span>
        @endif
    </x-admin.page-header>

    <div class="row g-3 mb-3">
        <div class="col-lg-5">
            <form class="card h-100 scan-box" method="GET" action="{{ route('admin.lab.scan') }}">
                <div class="card-body">
                    <label class="form-label fw-semibold" for="scanCode"><i class="fas fa-barcode me-1"></i> Scan a sample barcode or type an invoice number</label>
                    <div class="input-group">
                        <input class="form-control" id="scanCode" name="code" placeholder="2611130001 or INV-MAIN-2611-00001" autofocus autocomplete="off">
                        <button class="btn btn-primary">Open</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-lg-7">
            <div class="stage-tabs">
                @foreach (\App\Http\Controllers\Admin\LabController::STAGES as $key => [$label, $icon])
                    <a href="{{ route('admin.lab.index', ['stage' => $key, 'department_id' => $departmentId]) }}" @class(['stage-tab', 'active' => $stage === $key])>
                        <i class="{{ $icon }}"></i>
                        <span><b>{{ $counts[$key] }}</b><small>{{ $label }}</small></span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 mb-2">
                <form method="GET" class="d-flex gap-2">
                    <input type="hidden" name="stage" value="{{ $stage }}">
                    <select class="form-select form-select-sm" name="department_id" onchange="this.form.submit()" aria-label="Department">
                        <option value="">All departments</option>
                        @foreach ($departments as $id => $name)<option value="{{ $id }}" @selected($departmentId == $id)>{{ $name }}</option>@endforeach
                    </select>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle w-100" id="labTable">
                    <thead><tr><th>#</th><th>Bill</th><th>Patient</th><th>Test</th><th>Sample</th><th>Report due</th><th>Status</th><th class="text-end"></th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <x-utility.datatable-js />
    <script>
        new DataTable('#labTable', {
            processing: true, serverSide: true, searchDelay: 400, pageLength: 50, order: [],
            ajax: { url: "{{ route('admin.lab.index') }}", data: (d) => Object.assign(d, { stage: @json($stage), department_id: @json($departmentId) }) },
            language: { search: 'Search (test, bill, patient, barcode):' },
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'bill', orderable: false, searchable: false },
                { data: 'patient', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'sample_no', orderable: false, searchable: false },
                { data: 'delivery_at', name: 'delivery_at', searchable: false },
                { data: 'status', orderable: false, searchable: false },
                { data: 'actions', orderable: false, searchable: false, className: 'text-end text-nowrap' },
            ],
        });
    </script>
@endpush
