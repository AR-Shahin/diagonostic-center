@extends('admin.layouts.master')

@section('title', 'Patients')

@push('css')
    <x-utility.datatable-css />
@endpush

@section('master_content')
    <x-admin.page-header title="Patients" icon="fas fa-user-injured" subtitle="Search by name, mobile or patient ID. One mobile number can have several family members.">
        @if (in_array('patient-create', $permissions))
            <button class="btn btn-sm btn-outline-success" data-crud-create data-action="{{ route('admin.patients.store') }}" data-title="Register patient"><i class="fas fa-user-plus me-1"></i> Register patient</button>
        @endif
        @if (in_array('invoice-create', $permissions))
            <a class="btn btn-sm btn-success" href="{{ route('admin.invoices.create') }}"><i class="fas fa-cash-register me-1"></i> New bill</a>
        @endif
    </x-admin.page-header>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover align-middle data-table w-100">
                <thead><tr><th>#</th><th>ID</th><th>Name</th><th>Mobile</th><th>Gender · Age</th><th>Bills</th><th>Due</th><th class="text-end">Actions</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <x-admin.crud-modal title="Register patient">
        @include('admin.patient._fields')
    </x-admin.crud-modal>
@endsection

@push('script')
    <x-utility.datatable-js />
    <script src="{{ asset('custom/js/crud-modal.js') }}"></script>
    <script>
        initalizeDatatable("{{ route('admin.patients.index') }}", [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'patient_code', name: 'patient_code' },
            { data: 'name', name: 'name' },
            { data: 'mobile', name: 'mobile' },
            { data: 'gender_age', orderable: false, searchable: false },
            { data: 'invoices_count', searchable: false },
            { data: 'due', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end text-nowrap' },
        ]);
    </script>
@endpush
