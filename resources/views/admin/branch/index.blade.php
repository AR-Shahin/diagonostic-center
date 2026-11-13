@extends('admin.layouts.master')

@section('title', 'Branches')

@push('css')
    <x-utility.datatable-css />
@endpush

@section('master_content')
    <x-admin.page-header title="Branches" icon="fas fa-code-branch" subtitle="Each branch has its own address, hours and staff. Bills and reports will be tagged with a branch.">
        @if (in_array('branch-create', $permissions))
            <button class="btn btn-sm btn-success" data-crud-create data-action="{{ route('admin.branches.store') }}" data-title="Add branch"><i class="fas fa-plus me-1"></i> Add branch</button>
        @endif
    </x-admin.page-header>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover align-middle data-table w-100">
                <thead><tr><th>#</th><th>Code</th><th>Name</th><th>Phone</th><th>Website</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <x-admin.crud-modal title="Add branch">
        <div class="row">
            <div class="col-md-3"><x-admin.input name="code" label="Code" required help="Short code, e.g. MAIN, DHN" /></div>
            <div class="col-md-5"><x-admin.input name="name_en" label="Name (English)" required /></div>
            <div class="col-md-4"><x-admin.input name="name_bn" label="Name (বাংলা)" /></div>
            <div class="col-md-6"><x-admin.input name="phone" label="Phone" /></div>
            <div class="col-md-6"><x-admin.input name="email" label="Email" type="email" /></div>
            <div class="col-md-6"><x-admin.textarea name="address_en" label="Address (English)" rows="2" /></div>
            <div class="col-md-6"><x-admin.textarea name="address_bn" label="Address (বাংলা)" rows="2" /></div>
            <div class="col-md-6"><x-admin.input name="opening_hours_en" label="Opening hours (English)" placeholder="Every day 8 AM – 10 PM" /></div>
            <div class="col-md-6"><x-admin.input name="opening_hours_bn" label="Opening hours (বাংলা)" placeholder="প্রতিদিন সকাল ৮টা – রাত ১০টা" /></div>
            <div class="col-md-6"><x-admin.textarea name="facilities_en" label="Facilities (English)" rows="3" help="One per line, shown as chips on the website." /></div>
            <div class="col-md-6"><x-admin.textarea name="facilities_bn" label="Facilities (বাংলা)" rows="3" /></div>
            <div class="col-md-8"><x-admin.input name="map_url" label="Google Maps link" type="url" placeholder="https://maps.google.com/..." /></div>
            <div class="col-md-4"><x-admin.input name="sort" label="Display order" type="number" default="0" min="0" /></div>
            <div class="col-md-8"><x-admin.input name="image" label="Photo (optional)" type="file" accept="image/*" /></div>
            <div class="col-md-4 pt-md-4">
                <x-admin.switch name="is_main" label="Main branch" />
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
        initalizeDatatable("{{ route('admin.branches.index') }}", [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'name_en', name: 'name_en' },
            { data: 'phone', name: 'phone' },
            { data: 'website', orderable: false, searchable: false },
            { data: 'state', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' },
        ]);
    </script>
@endpush
