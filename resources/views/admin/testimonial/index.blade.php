@extends('admin.layouts.master')

@section('title', 'Patient reviews')

@push('css')
    <x-utility.datatable-css />
@endpush

@section('master_content')
    <x-admin.page-header title="Patient reviews" icon="fas fa-comment-dots" subtitle="Only real reviews, entered with the patient's permission. A review without consent ticked is never shown.">
        <button class="btn btn-sm btn-success" data-crud-create data-action="{{ route('admin.testimonials.store') }}" data-title="Add review"><i class="fas fa-plus me-1"></i> Add review</button>
    </x-admin.page-header>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover align-middle data-table w-100">
                <thead><tr><th>#</th><th>Patient</th><th>Review</th><th>Rating</th><th>Website</th><th class="text-end">Actions</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <x-admin.crud-modal title="Add review">
        <div class="row">
            <div class="col-md-6"><x-admin.input name="name" label="Name as it should appear" required placeholder="Rahima K." help="First name + initial is enough" /></div>
            <div class="col-md-6"><x-admin.select name="rating" label="Rating" :options="[5 => '★★★★★ 5', 4 => '★★★★ 4', 3 => '★★★ 3', 2 => '★★ 2', 1 => '★ 1']" default="5" /></div>
            <div class="col-md-6"><x-admin.input name="meta_bn" label="Service (বাংলা)" placeholder="হেলথ চেকআপ" /></div>
            <div class="col-md-6"><x-admin.input name="meta_en" label="Service (English)" placeholder="Health check-up" /></div>
            <div class="col-md-6"><x-admin.textarea name="text_bn" label="Review (বাংলা)" rows="3" help="Patient's own words. Do not edit the meaning." /></div>
            <div class="col-md-6"><x-admin.textarea name="text_en" label="Review (English)" rows="3" /></div>
            <div class="col-md-8"><x-admin.input name="consent_note" label="How consent was given" placeholder="Signed form 12 Nov / SMS reply" /></div>
            <div class="col-md-4"><x-admin.input name="sort" label="Order" type="number" default="0" min="0" /></div>
            <div class="col-12 d-flex gap-4">
                <x-admin.switch name="consent" label="Patient agreed to publish" :default="false" />
                <x-admin.switch name="status" label="Show on website" :default="true" />
            </div>
        </div>
    </x-admin.crud-modal>
@endsection

@push('script')
    <x-utility.datatable-js />
    <script src="{{ asset('custom/js/crud-modal.js') }}"></script>
    <script>
        initalizeDatatable("{{ route('admin.testimonials.index') }}", [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'text', orderable: false, searchable: false },
            { data: 'rating', name: 'rating' },
            { data: 'state', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' },
        ]);
    </script>
@endpush
