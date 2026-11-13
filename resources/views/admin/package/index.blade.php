@extends('admin.layouts.master')

@section('title', 'Health packages')

@push('css')
    <x-utility.datatable-css />
@endpush

@section('master_content')
    <x-admin.page-header title="Health packages" icon="fas fa-box-open" subtitle="A fixed price for a bundle of tests. Income and commission are split across the tests in proportion to their prices.">
        @if (in_array('package-create', $permissions))
            <a class="btn btn-sm btn-success" href="{{ route('admin.packages.create') }}"><i class="fas fa-plus me-1"></i> Add package</a>
        @endif
    </x-admin.page-header>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover align-middle data-table w-100">
                <thead><tr><th>#</th><th>Code</th><th>Package</th><th>Tests</th><th>Price</th><th>Separately</th><th>Saving</th><th>Validity</th><th>Website</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@push('script')
    <x-utility.datatable-js />
    <script>
        initalizeDatatable("{{ route('admin.packages.index') }}", [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'name_en', name: 'name_en' },
            { data: 'lab_tests_count', searchable: false },
            { data: 'price', name: 'price', searchable: false },
            { data: 'regular', orderable: false, searchable: false },
            { data: 'saving', orderable: false, searchable: false },
            { data: 'validity', orderable: false, searchable: false },
            { data: 'website', orderable: false, searchable: false },
            { data: 'state', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' },
        ]);
    </script>
@endpush
