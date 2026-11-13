{{-- One modal form reused for create + edit. Buttons with data-crud-create / data-crud-edit open it (see custom/js/crud-modal.js). --}}
@props(['title', 'size' => 'modal-lg'])
<div class="modal fade" id="crudModal" tabindex="-1" aria-labelledby="crudModalTitle" aria-hidden="true" data-reopen="{{ $errors->any() ? 1 : 0 }}">
    <div class="modal-dialog {{ $size }} modal-dialog-scrollable">
        <form class="modal-content" method="POST" action="{{ old('_form_action') }}" enctype="multipart/form-data" novalidate>
            @csrf
            <input type="hidden" name="_method" value="{{ old('_method', 'POST') }}">
            <input type="hidden" name="_form_action" value="{{ old('_form_action') }}">
            <input type="hidden" name="_form_title" value="{{ old('_form_title', $title) }}">
            <div class="modal-header">
                <h5 class="modal-title" id="crudModalTitle" data-crud-title>{{ old('_form_title', $title) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if ($errors->any())
                    <div class="alert alert-danger py-2 small mb-3">Please fix the highlighted fields.</div>
                @endif
                {{ $slot }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Save</button>
            </div>
        </form>
    </div>
</div>
