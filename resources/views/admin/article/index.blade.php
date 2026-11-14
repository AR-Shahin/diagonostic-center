@extends('admin.layouts.master')

@section('title', 'Health articles')

@push('css')
    <x-utility.datatable-css />
@endpush

@section('master_content')
    <x-admin.page-header title="Health articles" icon="fas fa-newspaper" subtitle="Health tips and test-preparation advice. The newest three show on the home page; all published ones on the Health tips page.">
        <a class="btn btn-sm btn-light" target="_blank" href="{{ route('site.articles') }}"><i class="fas fa-external-link-alt me-1"></i> View on website</a>
        <button class="btn btn-sm btn-success" data-crud-create data-action="{{ route('admin.articles.store') }}" data-title="New article"><i class="fas fa-plus me-1"></i> New article</button>
    </x-admin.page-header>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover align-middle data-table w-100">
                <thead><tr><th>#</th><th>Title</th><th>Category</th><th>State</th><th class="text-end">Actions</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <x-admin.crud-modal title="New article" size="modal-xl">
        <div class="row">
            <div class="col-md-6"><x-admin.input name="title_bn" label="Title (বাংলা)" /></div>
            <div class="col-md-6"><x-admin.input name="title_en" label="Title (English)" /></div>
            <div class="col-md-3"><x-admin.input name="category_bn" label="Category (বাংলা)" placeholder="টেস্ট প্রস্তুতি" /></div>
            <div class="col-md-3"><x-admin.input name="category_en" label="Category (English)" placeholder="Test preparation" /></div>
            <div class="col-md-3"><x-admin.input name="published_at" label="Publish on" type="datetime-local" help="Empty = draft (not shown)" /></div>
            <div class="col-md-3"><x-admin.input name="slug" label="URL slug" help="Empty = from the English title" /></div>
            <div class="col-md-6"><x-admin.textarea name="excerpt_bn" label="Summary (বাংলা)" rows="2" maxlength="300" help="One or two lines shown on the card" /></div>
            <div class="col-md-6"><x-admin.textarea name="excerpt_en" label="Summary (English)" rows="2" maxlength="300" /></div>
            <div class="col-md-6"><x-admin.textarea name="body_bn" label="Article (বাংলা)" rows="10" /></div>
            <div class="col-md-6"><x-admin.textarea name="body_en" label="Article (English)" rows="10" /></div>
            <div class="col-12 form-text mt-n2 mb-3">Plain text is fine: leave a blank line between paragraphs. Simple tags also work: &lt;h2&gt; heading, &lt;b&gt; bold, &lt;ul&gt;&lt;li&gt; list.</div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="f_image">Cover picture</label>
                <input class="form-control @error('image') is-invalid @enderror" type="file" name="image" id="f_image" accept="image/png,image/jpeg,image/webp">
                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Wide picture (about 1280×720), under 2 MB. Only use pictures you own or have permission for.</div>
            </div>
        </div>
    </x-admin.crud-modal>
@endsection

@push('script')
    <x-utility.datatable-js />
    <script src="{{ asset('custom/js/crud-modal.js') }}"></script>
    <script>
        initalizeDatatable("{{ route('admin.articles.index') }}", [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'title_bn', name: 'title_bn' },
            { data: 'category', orderable: false, searchable: false },
            { data: 'state', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' },
        ]);
    </script>
@endpush
