@extends('admin.layouts.master')

@section('title', 'Units')

@push('css')
    <x-utility.datatable-css />
@endpush

@section('master_content')
    <x-admin.page-header title="Units" icon="fas fa-ruler" subtitle="Result units such as mg/dL, g/dL, %.">
        @if (in_array('unit-create', $permissions))
            <button class="btn btn-sm btn-success" data-crud-create data-action="{{ route('admin.units.store') }}" data-title="Add unit"><i class="fas fa-plus me-1"></i> Add unit</button>
        @endif
    </x-admin.page-header>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover align-middle data-table w-100">
                <thead><tr><th>#</th><th>Unit</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <x-admin.crud-modal title="Add unit" size="modal-sm">
        <x-admin.input name="name" label="Unit" required placeholder="mg/dL" />
        <x-admin.switch name="status" label="Active" :default="true" />
    </x-admin.crud-modal>
@endsection

@push('script')
    <x-utility.datatable-js />
    <script src="{{ asset('custom/js/crud-modal.js') }}"></script>
    <script>
        initalizeDatatable("{{ route('admin.units.index') }}", [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'state', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' },
        ]);
    </script>
@endpush
