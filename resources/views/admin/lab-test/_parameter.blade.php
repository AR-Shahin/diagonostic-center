{{-- One result parameter card. $i = index (or __P__ in the JS template), $p = data array. --}}
@php($n = "parameters[$i]")
<div class="param-card border rounded-3 p-3 mb-3 bg-body" data-param data-index="{{ $i }}">
    <input type="hidden" name="{{ $n }}[id]" value="{{ $p['id'] ?? '' }}">
    <div class="d-flex align-items-center gap-2 mb-2">
        <span class="badge text-bg-primary" data-param-number></span>
        <strong class="me-auto" data-param-title>{{ $p['name'] ?? 'New parameter' }}</strong>
        <button type="button" class="btn btn-sm btn-light" data-move="up" title="Move up"><i class="fas fa-arrow-up"></i></button>
        <button type="button" class="btn btn-sm btn-light" data-move="down" title="Move down"><i class="fas fa-arrow-down"></i></button>
        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-param title="Remove parameter"><i class="fas fa-trash"></i></button>
    </div>
    <div class="row g-2">
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Code <span class="text-danger">*</span></label>
            <input class="form-control form-control-sm text-uppercase" name="{{ $n }}[code]" value="{{ $p['code'] ?? '' }}" placeholder="HB" required maxlength="30">
        </div>
        <div class="col-6 col-md-4">
            <label class="form-label small mb-1">Name <span class="text-danger">*</span></label>
            <input class="form-control form-control-sm" name="{{ $n }}[name]" value="{{ $p['name'] ?? '' }}" placeholder="Haemoglobin" required data-param-name>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small mb-1">Group (optional)</label>
            <input class="form-control form-control-sm" name="{{ $n }}[group_name]" value="{{ $p['group_name'] ?? '' }}" placeholder="Optional">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small mb-1">Unit</label>
            <select class="form-select form-select-sm" name="{{ $n }}[unit_id]">
                <option value="">—</option>
                @foreach ($units as $id => $name)
                    <option value="{{ $id }}" @selected((string) ($p['unit_id'] ?? '') === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Input</label>
            <select class="form-select form-select-sm" name="{{ $n }}[input_type]" data-input-type>
                @foreach (\App\Models\TestParameter::INPUT_TYPES as $value => $label)
                    <option value="{{ $value }}" @selected(($p['input_type'] ?? 'number') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2" data-show-for="number">
            <label class="form-label small mb-1">Decimals</label>
            <input class="form-control form-control-sm" type="number" min="0" max="4" name="{{ $n }}[decimal_places]" value="{{ $p['decimal_places'] ?? 2 }}">
        </div>
        <div class="col-12 col-md-4" data-show-for="option">
            <label class="form-label small mb-1">Choices (comma separated)</label>
            <input class="form-control form-control-sm" name="{{ $n }}[options]" value="{{ $p['options'] ?? '' }}" placeholder="Nil, Trace, +, ++">
        </div>
        <div class="col-12 col-md-4" data-show-for="number">
            <label class="form-label small mb-1">Formula (calculated)</label>
            <input class="form-control form-control-sm font-monospace" name="{{ $n }}[formula]" value="{{ $p['formula'] ?? '' }}" placeholder="Optional, e.g. TC - HDL">
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label small mb-1">Default value</label>
            <input class="form-control form-control-sm" name="{{ $n }}[default_value]" value="{{ $p['default_value'] ?? '' }}" placeholder="Optional">
        </div>
    </div>

    <div class="mt-3">
        <div class="small fw-semibold mb-1">Reference ranges <span class="text-muted fw-normal">— leave age empty for all ages; the most specific matching row is used.</span></div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-1 ranges-table">
                <thead class="small text-muted"><tr><th>Gender</th><th>Age from</th><th>Age to</th><th>Age unit</th><th>Low</th><th>High</th><th>Printed text</th><th></th></tr></thead>
                <tbody data-ranges>
                    @foreach ($p['ranges'] ?? [] as $j => $r)
                        @include('admin.lab-test._range', ['i' => $i, 'j' => $j, 'r' => $r])
                    @endforeach
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-add-range><i class="fas fa-plus me-1"></i> Add range</button>
    </div>
</div>
