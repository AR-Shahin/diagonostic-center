@extends('admin.layouts.master')

@section('title', 'FAQ')

@push('css')
    <x-utility.datatable-css />
@endpush

@section('master_content')
    <x-admin.page-header title="FAQ" icon="fas fa-question-circle" subtitle="Questions and answers at the bottom of the home page. Lower order number shows first.">
        <button class="btn btn-sm btn-success" data-crud-create data-action="{{ route('admin.faqs.store') }}" data-title="Add question"><i class="fas fa-plus me-1"></i> Add question</button>
    </x-admin.page-header>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover align-middle data-table w-100">
                <thead><tr><th>#</th><th>Question</th><th>Order</th><th>Website</th><th class="text-end">Actions</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <x-admin.crud-modal title="Add question">
        <x-admin.input name="question_bn" label="Question (বাংলা)" />
        <x-admin.textarea name="answer_bn" label="Answer (বাংলা)" rows="3" />
        <x-admin.input name="question_en" label="Question (English)" />
        <x-admin.textarea name="answer_en" label="Answer (English)" rows="3" />
        <div class="row">
            <div class="col-md-4"><x-admin.input name="sort" label="Order" type="number" default="0" min="0" /></div>
            <div class="col-md-8 pt-md-4"><x-admin.switch name="status" label="Show on website" :default="true" /></div>
        </div>
    </x-admin.crud-modal>
@endsection

@push('script')
    <x-utility.datatable-js />
    <script src="{{ asset('custom/js/crud-modal.js') }}"></script>
    <script>
        initalizeDatatable("{{ route('admin.faqs.index') }}", [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'question_bn', name: 'question_bn' },
            { data: 'sort', searchable: false },
            { data: 'state', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' },
        ]);
    </script>
@endpush
