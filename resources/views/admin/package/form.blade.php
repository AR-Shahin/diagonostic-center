@extends('admin.layouts.master')

@section('title', $package->exists ? 'Edit package' : 'Add package')

@push('css')
    <style>
        .test-picker { max-height: 26rem; overflow: auto; }
        .test-picker .form-check { padding: .45rem .5rem .45rem 2rem; border-radius: .5rem; }
        .test-picker .form-check:hover { background: var(--bs-tertiary-bg); }
        .sticky-side { position: sticky; top: 1rem; }
        .summary-num { font-variant-numeric: tabular-nums; }
        .icon-choice .btn { width: 2.75rem; height: 2.75rem; font-size: 1.2rem; }
    </style>
@endpush

@section('master_content')
    <x-admin.page-header :title="$package->exists ? 'Edit package: '.$package->name_en : 'Add package'" icon="fas fa-box-open">
        <a href="{{ route('admin.packages.index') }}" class="btn btn-sm btn-light"><i class="fas fa-arrow-left me-1"></i> All packages</a>
    </x-admin.page-header>

    @if ($errors->any())
        <div class="alert alert-danger"><b>Please fix these:</b><ul class="mb-0 small">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ $package->exists ? route('admin.packages.update', $package) : route('admin.packages.store') }}" id="packageForm">
        @csrf
        @if ($package->exists) @method('PUT') @endif

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header"><b>Package details</b></div>
                    <div class="card-body row">
                        <div class="col-md-3"><x-admin.input name="code" label="Code" :value="$package->code" required /></div>
                        <div class="col-md-5"><x-admin.input name="name_en" label="Name (English)" :value="$package->name_en" required /></div>
                        <div class="col-md-4"><x-admin.input name="name_bn" label="Name (বাংলা)" :value="$package->name_bn" /></div>
                        <div class="col-md-6"><x-admin.input name="tagline_en" label="Short line (English)" :value="$package->tagline_en" /></div>
                        <div class="col-md-6"><x-admin.input name="tagline_bn" label="Short line (বাংলা)" :value="$package->tagline_bn" /></div>
                        <div class="col-md-6"><x-admin.input name="slug" label="URL slug" :value="$package->slug" help="Leave empty to build it from the English name." /></div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Icon</label>
                            <div class="d-flex flex-wrap gap-1 icon-choice">
                                @foreach (\App\Models\Package::ICONS as $icon)
                                    <input type="radio" class="btn-check" name="icon" id="ic_{{ $icon }}" value="{{ $icon }}" @checked(old('icon', $package->icon) === $icon)>
                                    <label class="btn btn-outline-primary d-grid place-items-center" for="ic_{{ $icon }}" title="{{ $icon }}"><i class="bi {{ $icon }}"></i></label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header d-flex flex-wrap gap-2 align-items-center">
                        <b class="me-auto">Included tests <span class="badge text-bg-primary" id="pickedCount">0</span></b>
                        <input type="search" class="form-control form-control-sm w-auto" id="testSearch" placeholder="Search tests…" aria-label="Search tests">
                    </div>
                    <div class="card-body test-picker" id="testPicker">
                        @foreach ($tests->groupBy(fn ($t) => $t->department?->name_en ?? 'Other') as $dept => $group)
                            <div class="small text-uppercase text-muted fw-semibold mt-2 mb-1" data-group>{{ $dept }}</div>
                            @foreach ($group as $t)
                                <div class="form-check" data-test-row data-search="{{ strtolower($t->code.' '.$t->name_en) }}">
                                    <input class="form-check-input" type="checkbox" name="tests[]" value="{{ $t->id }}" id="t_{{ $t->id }}" data-price="{{ (float) $t->price }}" @checked(in_array($t->id, $selected, true))>
                                    <label class="form-check-label d-flex justify-content-between w-100" for="t_{{ $t->id }}">
                                        <span>{{ $t->name_en }} <span class="text-muted small">{{ $t->code }}</span></span>
                                        <span class="text-muted summary-num">৳ {{ number_format((float) $t->price) }}</span>
                                    </label>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><b>Who & when</b></div>
                    <div class="card-body row">
                        <div class="col-md-4"><x-admin.select name="gender" label="For" :options="['any' => 'Everyone', 'male' => 'Men', 'female' => 'Women']" :value="$package->gender" /></div>
                        <div class="col-md-4"><x-admin.input name="min_age" label="Min age (years)" type="number" min="0" :value="$package->min_age" /></div>
                        <div class="col-md-4"><x-admin.input name="max_age" label="Max age (years)" type="number" min="0" :value="$package->max_age" /></div>
                        <div class="col-md-4"><x-admin.input name="valid_from" label="Valid from" type="date" :value="$package->valid_from?->toDateString()" /></div>
                        <div class="col-md-4"><x-admin.input name="valid_until" label="Valid until" type="date" :value="$package->valid_until?->toDateString()" help="Empty = no end date." /></div>
                        <div class="col-md-4"><x-admin.input name="sort" label="Display order" type="number" min="0" :value="$package->sort ?? 0" /></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sticky-side">
                    <div class="card mb-3">
                        <div class="card-header"><b>Price</b></div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-1"><span class="text-muted">Tests separately</span><b class="summary-num" id="regularOut">৳ 0</b></div>
                            <x-admin.input name="price" label="Package price (৳)" type="number" step="0.01" min="1" :value="$package->price" required />
                            <div class="d-flex justify-content-between"><span class="text-muted">Patient saves</span><b class="summary-num text-success" id="savingOut">—</b></div>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-header"><b>Visibility</b></div>
                        <div class="card-body">
                            <x-admin.switch name="status" label="Active (can be billed)" :checked="$package->status" />
                            <x-admin.switch name="show_on_website" label="Show on website" :checked="$package->show_on_website" />
                            <x-admin.switch name="is_featured" label="Featured (highlighted card)" :checked="$package->is_featured" help="Only one package can be featured." />
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i class="fas fa-save me-1"></i> Save package</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('script')
    <script>
        (function () {
            const form = document.getElementById('packageForm');
            const boxes = [...form.querySelectorAll('input[name="tests[]"]')];
            const money = (v) => '৳ ' + Math.round(v).toLocaleString('en-US');

            const update = () => {
                const picked = boxes.filter((b) => b.checked);
                const regular = picked.reduce((sum, b) => sum + parseFloat(b.dataset.price || 0), 0);
                const price = parseFloat(form.price.value || 0);
                document.getElementById('pickedCount').textContent = picked.length;
                document.getElementById('regularOut').textContent = money(regular);
                const out = document.getElementById('savingOut');
                if (!price || !regular) { out.textContent = '—'; out.className = 'summary-num'; return; }
                const saving = regular - price;
                out.textContent = saving >= 0 ? money(saving) + ' (' + Math.round(saving / regular * 100) + '%)' : 'Price is higher than separately!';
                out.className = 'summary-num fw-bold ' + (saving >= 0 ? 'text-success' : 'text-danger');
            };
            boxes.forEach((b) => b.addEventListener('change', update));
            form.price.addEventListener('input', update);
            update();

            document.getElementById('testSearch').addEventListener('input', (e) => {
                const q = e.target.value.trim().toLowerCase();
                document.querySelectorAll('[data-test-row]').forEach((row) => { row.hidden = q && !row.dataset.search.includes(q); });
            });
        })();
    </script>
@endpush
