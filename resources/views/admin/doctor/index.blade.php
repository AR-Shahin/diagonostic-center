@extends('admin.layouts.master')

@section('title', 'Doctors')

@push('css')
    <x-utility.datatable-css />
@endpush

@section('master_content')
    <x-admin.page-header title="Doctors" icon="fas fa-user-md" subtitle="Consultants shown on the website's Doctors page, with chamber days and fees. Patients can request an appointment with them.">
        <a class="btn btn-sm btn-light" target="_blank" href="{{ route('site.doctors') }}"><i class="fas fa-external-link-alt me-1"></i> View on website</a>
        <button class="btn btn-sm btn-success" data-crud-create data-action="{{ route('admin.doctors.store') }}" data-title="Add doctor"><i class="fas fa-plus me-1"></i> Add doctor</button>
    </x-admin.page-header>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover align-middle data-table w-100">
                <thead><tr><th>#</th><th>Doctor</th><th>Speciality</th><th>Chamber</th><th>Website</th><th class="text-end">Actions</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <x-admin.crud-modal title="Add doctor" size="modal-xl">
        <div class="row">
            <div class="col-md-4"><x-admin.input name="name_en" label="Name (English)" required placeholder="Dr. Rahim Uddin" /></div>
            <div class="col-md-4"><x-admin.input name="name_bn" label="Name (বাংলা)" placeholder="ডা. রহিম উদ্দিন" /></div>
            <div class="col-md-4"><x-admin.input name="qualification" label="Degrees" placeholder="MBBS, FCPS (Medicine)" /></div>
            <div class="col-md-3"><x-admin.input name="speciality_en" label="Speciality (English)" placeholder="Medicine" /></div>
            <div class="col-md-3"><x-admin.input name="speciality_bn" label="Speciality (বাংলা)" placeholder="মেডিসিন" /></div>
            <div class="col-md-3"><x-admin.input name="designation_en" label="Designation (English)" placeholder="Associate Professor, DMCH" /></div>
            <div class="col-md-3"><x-admin.input name="designation_bn" label="Designation (বাংলা)" /></div>
            <div class="col-12"><hr class="my-2"><b class="small text-muted">Chamber</b></div>
            <div class="col-md-4"><x-admin.select name="days[]" label="Days" :options="$days" multiple size="7" help="Ctrl / ⌘ + click to pick several" /></div>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-6"><x-admin.input name="time_from" label="From" type="time" /></div>
                    <div class="col-6"><x-admin.input name="time_to" label="To" type="time" /></div>
                    <div class="col-6"><x-admin.select name="branch_id" label="Branch" :options="$branches" placeholder="—" /></div>
                    <div class="col-6"><x-admin.input name="fee" label="Visit fee (৳)" type="number" min="0" step="1" /></div>
                    <div class="col-6"><x-admin.input name="schedule_note_en" label="Note (English)" placeholder="Call for a serial" /></div>
                    <div class="col-6"><x-admin.input name="schedule_note_bn" label="Note (বাংলা)" placeholder="সিরিয়ালের জন্য কল করুন" /></div>
                </div>
            </div>
            <div class="col-12"><hr class="my-2"></div>
            <div class="col-md-6"><x-admin.textarea name="bio_en" label="Short profile (English)" rows="2" /></div>
            <div class="col-md-6"><x-admin.textarea name="bio_bn" label="Short profile (বাংলা)" rows="2" /></div>
            <div class="col-md-4">
                <label class="form-label fw-semibold" for="f_photo">Photo</label>
                <input class="form-control @error('photo') is-invalid @enderror" type="file" name="photo" id="f_photo" accept="image/png,image/jpeg,image/webp">
                @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Square, at least 300×300, under 1 MB. Leave empty to keep the current one.</div>
            </div>
            <div class="col-md-3"><x-admin.input name="slug" label="URL slug" help="Empty = from the English name" /></div>
            <div class="col-md-2"><x-admin.input name="sort" label="Order" type="number" default="0" min="0" /></div>
            <div class="col-md-3 pt-md-2">
                <x-admin.switch name="accepts_appointments" label="Takes appointments" :default="true" />
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
        initalizeDatatable("{{ route('admin.doctors.index') }}", [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name_en', name: 'name_en' },
            { data: 'speciality', name: 'speciality_en' },
            { data: 'schedule', orderable: false, searchable: false },
            { data: 'website', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' },
        ]);
    </script>
@endpush
