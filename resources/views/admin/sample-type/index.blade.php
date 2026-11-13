@extends('admin.layouts.master')

@section('title', 'Sample types')

@push('css')
    <x-utility.datatable-css />
@endpush

@section('master_content')
    <x-admin.page-header title="Sample types" icon="fas fa-vial" subtitle="Blood (EDTA), Serum, Urine… printed on sample labels.">
        @if (in_array('sampletype-create', $permissions))
            <button class="btn btn-sm btn-success" data-crud-create data-action="{{ route('admin.sample-types.store') }}" data-title="Add sample type"><i class="fas fa-plus me-1"></i> Add sample type</button>
        @endif
    </x-admin.page-header>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover align-middle data-table w-100">
                <thead><tr><th>#</th><th>Name</th><th>বাংলা</th><th>Container</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <x-admin.crud-modal title="Add sample type" size="">
        <x-admin.input name="name_en" label="Name (English)" required />
        <x-admin.input name="name_bn" label="Name (বাংলা)" />
        <x-admin.input name="container" label="Container / tube" placeholder="EDTA tube – purple cap" />
        <x-admin.switch name="status" label="Active" :default="true" />
    </x-admin.crud-modal>
@endsection

@push('script')
    <x-utility.datatable-js />
    <script src="{{ asset('custom/js/crud-modal.js') }}"></script>
    <script>
        initalizeDatatable("{{ route('admin.sample-types.index') }}", [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name_en', name: 'name_en' },
            { data: 'name_bn', name: 'name_bn' },
            { data: 'container', name: 'container' },
            { data: 'state', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' },
        ]);
    </script>
@endpush
