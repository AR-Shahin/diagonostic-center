@extends('admin.layouts.master')

@section('title', 'Departments')

@push('css')
    <x-utility.datatable-css />
@endpush

@section('master_content')
    <x-admin.page-header title="Departments" icon="fas fa-layer-group" subtitle="Groups tests (Pathology, Radiology…). Shown as tabs on the website and used for commission rates.">
        @if (in_array('department-create', $permissions))
            <button class="btn btn-sm btn-success" data-crud-create data-action="{{ route('admin.departments.store') }}" data-title="Add department"><i class="fas fa-plus me-1"></i> Add department</button>
        @endif
    </x-admin.page-header>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover align-middle data-table w-100">
                <thead><tr><th>#</th><th>Name</th><th>Tests</th><th>Website</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <x-admin.crud-modal title="Add department">
        <div class="row">
            <div class="col-md-6"><x-admin.input name="name_en" label="Name (English)" required /></div>
            <div class="col-md-6"><x-admin.input name="name_bn" label="Name (বাংলা)" /></div>
            <div class="col-md-6"><x-admin.input name="slug" label="URL slug" help="Leave empty to create it from the English name." /></div>
            <div class="col-md-4"><x-admin.select name="icon" label="Icon" :options="$icons" default="bi-clipboard2-pulse" required /></div>
            <div class="col-md-2 d-flex align-items-center justify-content-center"><i class="bi bi-clipboard2-pulse fs-1 text-primary" data-icon-preview></i></div>
            <div class="col-md-6"><x-admin.textarea name="description_en" label="Short description (English)" rows="2" /></div>
            <div class="col-md-6"><x-admin.textarea name="description_bn" label="Short description (বাংলা)" rows="2" /></div>
            <div class="col-md-6"><x-admin.textarea name="highlights_en" label="Highlights (English)" rows="3" help="One per line, e.g. Routine blood & urine tests" /></div>
            <div class="col-md-6"><x-admin.textarea name="highlights_bn" label="Highlights (বাংলা)" rows="3" /></div>
            <div class="col-md-4"><x-admin.input name="sort" label="Display order" type="number" default="0" min="0" /></div>
            <div class="col-md-8 pt-md-4 d-flex gap-4">
                <x-admin.switch name="show_on_website" label="Show on website" :default="true" />
                <x-admin.switch name="status" label="Active" :default="true" />
            </div>
        </div>
    </x-admin.crud-modal>
@endsection

@push('script')
    <x-utility.datatable-js />
    <script src="{{ asset('custom/js/crud-modal.js') }}"></script>
    <script>
        initalizeDatatable("{{ route('admin.departments.index') }}", [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name_en', name: 'name_en' },
            { data: 'lab_tests_count', searchable: false },
            { data: 'website', orderable: false, searchable: false },
            { data: 'state', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' },
        ]);
        document.getElementById('f_icon').addEventListener('change', (e) => {
            document.querySelector('[data-icon-preview]').className = 'bi ' + e.target.value + ' fs-1 text-primary';
        });
    </script>
@endpush
