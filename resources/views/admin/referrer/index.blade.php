@extends('admin.layouts.master')

@section('title', 'Referrers')

@push('css')
    <x-utility.datatable-css />
@endpush

@section('master_content')
    <x-admin.page-header title="Referrers" icon="fas fa-user-md" subtitle="Doctors, RMPs and agents who send patients. Shown as “Ref. by” on the invoice; commission rates come in a later phase.">
        @if (in_array('referrer-create', $permissions))
            <button class="btn btn-sm btn-success" data-crud-create data-action="{{ route('admin.referrers.store') }}" data-title="Add referrer"><i class="fas fa-plus me-1"></i> Add referrer</button>
        @endif
    </x-admin.page-header>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover align-middle data-table w-100">
                <thead><tr><th>#</th><th>Code</th><th>Name</th><th>Type</th><th>Mobile</th><th>Bills</th><th>Commission</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <x-admin.crud-modal title="Add referrer">
        <div class="row">
            <div class="col-md-4"><x-admin.select name="type" label="Type" :options="\App\Models\Referrer::TYPES" default="doctor" required /></div>
            <div class="col-md-8"><x-admin.input name="name" label="Name" required placeholder="Dr. ..." /></div>
            <div class="col-md-6"><x-admin.input name="degree" label="Degree / designation" placeholder="MBBS, FCPS (Medicine)" /></div>
            <div class="col-md-6"><x-admin.input name="organisation" label="Hospital / chamber / company" /></div>
            <div class="col-md-6"><x-admin.input name="mobile" label="Mobile" /></div>
            <div class="col-md-6"><x-admin.input name="address" label="Address" /></div>
            <div class="col-12 d-flex gap-4">
                <x-admin.switch name="commission_enabled" label="Eligible for commission" :default="true" />
                <x-admin.switch name="status" label="Active" :default="true" />
            </div>
        </div>
    </x-admin.crud-modal>
@endsection

@push('script')
    <x-utility.datatable-js />
    <script src="{{ asset('custom/js/crud-modal.js') }}"></script>
    <script>
        initalizeDatatable("{{ route('admin.referrers.index') }}", [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'type', name: 'type' },
            { data: 'mobile', name: 'mobile' },
            { data: 'invoices_count', searchable: false },
            { data: 'commission', orderable: false, searchable: false },
            { data: 'state', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' },
        ]);
    </script>
@endpush
