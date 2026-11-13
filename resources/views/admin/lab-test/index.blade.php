@extends('admin.layouts.master')

@section('title', 'Tests')

@push('css')
    <x-utility.datatable-css />
@endpush

@section('master_content')
    <x-admin.page-header title="Tests" icon="fas fa-flask" subtitle="Price list, preparation and report setup for every test.">
        @if (in_array('labtest-create', $permissions))
            <a class="btn btn-sm btn-success" href="{{ route('admin.lab-tests.create') }}"><i class="fas fa-plus me-1"></i> Add test</a>
        @endif
    </x-admin.page-header>

    <div class="card">
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-sm-4 col-lg-3">
                    <select class="form-select form-select-sm" id="filterDepartment" aria-label="Filter by department">
                        <option value="">All departments</option>
                        @foreach ($departments as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3 col-lg-2">
                    <select class="form-select form-select-sm" id="filterState" aria-label="Filter by status">
                        <option value="">Any status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle w-100" id="testsTable">
                    <thead><tr><th>#</th><th>Code</th><th>Test</th><th>Department</th><th>Price</th><th>Report</th><th>Website</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <x-utility.datatable-js />
    <script>
        const testsTable = new DataTable('#testsTable', {
            processing: true, serverSide: true, searchDelay: 500, pageLength: 25, order: [],
            ajax: {
                url: "{{ route('admin.lab-tests.index') }}",
                data: (d) => { d.department_id = $('#filterDepartment').val(); d.state = $('#filterState').val(); },
            },
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'code', name: 'code' },
                { data: 'name_en', name: 'name_en' },
                { data: 'department', orderable: false, searchable: false },
                { data: 'price', name: 'price', searchable: false },
                { data: 'type', orderable: false, searchable: false },
                { data: 'website', orderable: false, searchable: false },
                { data: 'state', orderable: false, searchable: false },
                { data: 'actions', orderable: false, searchable: false, className: 'text-end' },
            ],
        });
        $('#filterDepartment, #filterState').on('change', () => testsTable.ajax.reload());
    </script>
@endpush
