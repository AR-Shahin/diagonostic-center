@extends('admin.layouts.master')

@section('title', $test->exists ? 'Edit test' : 'Add test')

@push('css')
    <style>
        .param-card { box-shadow: 0 1px 2px rgba(0, 0, 0, .04); }
        .param-card:hover { transform: none; }
        .ranges-table td { min-width: 5.5rem; }
        .ranges-table td:first-child { min-width: 7.5rem; }
        .ranges-table td:nth-child(7) { min-width: 9rem; }
        .sticky-side { position: sticky; top: 1rem; }
        .margin-pill { font-variant-numeric: tabular-nums; }
    </style>
@endpush

@section('master_content')
    <x-admin.page-header :title="$test->exists ? 'Edit test: '.$test->name_en : 'Add test'" icon="fas fa-flask">
        <a href="{{ route('admin.lab-tests.index') }}" class="btn btn-sm btn-light"><i class="fas fa-arrow-left me-1"></i> All tests</a>
    </x-admin.page-header>

    @if ($errors->any())
        <div class="alert alert-danger">
            <b>Please fix these:</b>
            <ul class="mb-0 small">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ $test->exists ? route('admin.lab-tests.update', $test) : route('admin.lab-tests.store') }}" id="testForm">
        @csrf
        @if ($test->exists) @method('PUT') @endif

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header"><b>Basic information</b></div>
                    <div class="card-body row">
                        <div class="col-md-6"><x-admin.select name="department_id" label="Department" :options="$departments" :value="$test->department_id" placeholder="Select department" required /></div>
                        <div class="col-md-6"><x-admin.select name="sample_type_id" label="Sample type" :options="$sampleTypes" :value="$test->sample_type_id" placeholder="— None —" /></div>
                        <div class="col-md-3"><x-admin.input name="code" label="Code" :value="$test->code" required help="Unique, e.g. CBC" /></div>
                        <div class="col-md-5"><x-admin.input name="name_en" label="Name (English)" :value="$test->name_en" required /></div>
                        <div class="col-md-4"><x-admin.input name="name_bn" label="Name (বাংলা)" :value="$test->name_bn" /></div>
                        <div class="col-md-6"><x-admin.input name="slug" label="URL slug" :value="$test->slug" help="Leave empty to build it from the English name." /></div>
                        <div class="col-md-3"><x-admin.input name="delivery_hours" label="Report in (hours)" type="number" min="0" :value="$test->delivery_hours" /></div>
                        <div class="col-md-3"><x-admin.input name="sort" label="Display order" type="number" min="0" :value="$test->sort ?? 0" /></div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><b>Price</b></div>
                    <div class="card-body row align-items-end">
                        <div class="col-md-3"><x-admin.input name="price" label="Regular price (৳)" type="number" step="0.01" min="0" :value="$test->price" required /></div>
                        <div class="col-md-3"><x-admin.input name="offer_price" label="Offer price (৳)" type="number" step="0.01" min="0" :value="$test->offer_price" help="Optional. Billing uses it when set." /></div>
                        <div class="col-md-3"><x-admin.input name="cost" label="Cost (৳)" type="number" step="0.01" min="0" :value="$test->cost ?? 0" help="Reagents & consumables." /></div>
                        <div class="col-md-3 mb-3">
                            <div class="p-2 rounded-3 bg-body-tertiary text-center margin-pill">
                                <div class="small text-muted">Margin per test</div>
                                <div class="fs-5 fw-bold" id="marginOut">—</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><b>Patient information (website & receipt)</b></div>
                    <div class="card-body row">
                        <div class="col-md-6"><x-admin.textarea name="preparation_en" label="Preparation (English)" rows="2" :value="$test->preparation_en" placeholder="8–10 hours fasting required" /></div>
                        <div class="col-md-6"><x-admin.textarea name="preparation_bn" label="Preparation (বাংলা)" rows="2" :value="$test->preparation_bn" placeholder="৮–১০ ঘণ্টা উপবাস প্রয়োজন" /></div>
                        <div class="col-md-6"><x-admin.textarea name="description_en" label="About this test (English)" rows="3" :value="$test->description_en" /></div>
                        <div class="col-md-6"><x-admin.textarea name="description_bn" label="About this test (বাংলা)" rows="3" :value="$test->description_bn" /></div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header d-flex flex-wrap gap-2 align-items-center">
                        <b class="me-auto">Report setup</b>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Report type">
                            @foreach (\App\Models\LabTest::REPORT_TYPES as $value => $label)
                                <input type="radio" class="btn-check" name="report_type" id="rt_{{ $value }}" value="{{ $value }}" @checked(old('report_type', $test->report_type) === $value)>
                                <label class="btn btn-outline-primary" for="rt_{{ $value }}" title="{{ $label }}">{{ ucfirst($value) }}</label>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-body">
                        <div data-report="parameter">
                            <p class="small text-muted">Add each value the lab will enter (e.g. Haemoglobin, WBC). Ranges decide the H/L flag on the report.</p>
                            <div id="paramList">
                                @foreach ($parameters as $i => $p)
                                    @include('admin.lab-test._parameter', ['i' => $i, 'p' => $p])
                                @endforeach
                            </div>
                            <p class="text-muted small {{ count($parameters) ? 'd-none' : '' }}" id="paramEmpty">No parameters yet.</p>
                            <button type="button" class="btn btn-sm btn-primary" id="addParam"><i class="fas fa-plus me-1"></i> Add parameter</button>
                        </div>

                        <div data-report="template">
                            <p class="small text-muted">The default report text the consultant starts from (X-ray, USG, ECG…). Simple HTML like &lt;p&gt; and &lt;b&gt; is allowed.</p>
                            <textarea name="template_body" rows="10" class="form-control font-monospace" placeholder="<p><b>Findings:</b></p>">{{ old('template_body', $template?->body) }}</textarea>
                        </div>

                        <div data-report="culture">
                            <p class="small text-muted mb-0">Culture reports record the organism and an antibiotic sensitivity table. The result-entry screen for this comes with the lab module (phase 3).</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sticky-side">
                    <div class="card mb-3">
                        <div class="card-header"><b>Visibility</b></div>
                        <div class="card-body">
                            <x-admin.switch name="status" label="Active (can be billed)" :checked="$test->status" />
                            <x-admin.switch name="show_on_website" label="Show on website" :checked="$test->show_on_website" />
                            <x-admin.switch name="is_popular" label="Popular (home page)" :checked="$test->is_popular" />
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-header"><b>Available at</b></div>
                        <div class="card-body">
                            @php($selectedBranches = collect(old('branches', $test->exists ? $test->branches->pluck('id')->all() : []))->map(fn ($v) => (int) $v)->all())
                            @forelse ($branches as $branch)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="branches[]" value="{{ $branch->id }}" id="br_{{ $branch->id }}" @checked(in_array($branch->id, $selectedBranches, true))>
                                    <label class="form-check-label" for="br_{{ $branch->id }}">{{ $branch->name_en }} <span class="text-muted small">({{ $branch->code }})</span></label>
                                </div>
                            @empty
                                <p class="small text-muted mb-0">No branches yet.</p>
                            @endforelse
                            <div class="form-text">Leave all unticked if every branch offers this test.</div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i class="fas fa-save me-1"></i> Save test</button>
                </div>
            </div>
        </div>
    </form>

    <template id="paramTemplate">@include('admin.lab-test._parameter', ['i' => '__P__', 'p' => ['ranges' => [['gender' => 'any']]]])</template>
    <template id="rangeTemplate">@include('admin.lab-test._range', ['i' => '__P__', 'j' => '__R__', 'r' => []])</template>
@endsection

@push('script')
    <script>
        (function () {
            const form = document.getElementById('testForm');
            const list = document.getElementById('paramList');
            const empty = document.getElementById('paramEmpty');
            let paramSeq = list.querySelectorAll('[data-param]').length + 1000;
            let rangeSeq = 1000;

            // Report type → show the matching setup block
            const showReport = () => {
                const type = form.querySelector('[name="report_type"]:checked')?.value;
                document.querySelectorAll('[data-report]').forEach((el) => { el.hidden = el.dataset.report !== type; });
            };
            form.querySelectorAll('[name="report_type"]').forEach((r) => r.addEventListener('change', showReport));
            showReport();

            // Live margin
            const money = (v) => '৳ ' + Math.round(v).toLocaleString('en-US');
            const margin = () => {
                const price = parseFloat(form.offer_price.value || form.price.value || 0);
                const cost = parseFloat(form.cost.value || 0);
                const out = document.getElementById('marginOut');
                if (!price) { out.textContent = '—'; return; }
                const m = price - cost;
                out.textContent = money(m) + ' (' + Math.round((m / price) * 100) + '%)';
                out.className = 'fs-5 fw-bold ' + (m < 0 ? 'text-danger' : 'text-success');
            };
            ['price', 'offer_price', 'cost'].forEach((n) => form[n].addEventListener('input', margin));
            margin();

            // Parameters
            const renumber = () => {
                list.querySelectorAll('[data-param]').forEach((card, i) => { card.querySelector('[data-param-number]').textContent = i + 1; });
                empty.classList.toggle('d-none', list.children.length > 0);
            };
            const toggleInputType = (card) => {
                const type = card.querySelector('[data-input-type]').value;
                card.querySelectorAll('[data-show-for]').forEach((el) => { el.hidden = el.dataset.showFor !== type; });
            };
            const fromTemplate = (id, replacements) => {
                let html = document.getElementById(id).innerHTML;
                Object.entries(replacements).forEach(([k, v]) => { html = html.replaceAll(k, v); });
                const holder = document.createElement('template');
                holder.innerHTML = html.trim();
                return holder.content.firstElementChild;
            };

            document.getElementById('addParam').addEventListener('click', () => {
                const idx = paramSeq++;
                const card = fromTemplate('paramTemplate', { '__P__': idx });
                // template already holds one "any" range row named with __R__ = 0
                list.appendChild(card);
                toggleInputType(card);
                renumber();
                card.querySelector('[name$="[code]"]').focus();
            });

            list.addEventListener('click', (e) => {
                const card = e.target.closest('[data-param]');
                if (!card) return;
                if (e.target.closest('[data-remove-param]')) {
                    if (confirm('Remove this parameter?')) { card.remove(); renumber(); }
                } else if (e.target.closest('[data-move]')) {
                    const dir = e.target.closest('[data-move]').dataset.move;
                    if (dir === 'up' && card.previousElementSibling) list.insertBefore(card, card.previousElementSibling);
                    if (dir === 'down' && card.nextElementSibling) list.insertBefore(card.nextElementSibling, card);
                    renumber();
                } else if (e.target.closest('[data-add-range]')) {
                    card.querySelector('[data-ranges]').appendChild(fromTemplate('rangeTemplate', { '__P__': card.dataset.index, '__R__': rangeSeq++ }));
                } else if (e.target.closest('[data-remove-range]')) {
                    e.target.closest('[data-range]').remove();
                }
            });
            list.addEventListener('change', (e) => {
                if (e.target.matches('[data-input-type]')) toggleInputType(e.target.closest('[data-param]'));
            });
            list.addEventListener('input', (e) => {
                if (e.target.matches('[data-param-name]')) {
                    e.target.closest('[data-param]').querySelector('[data-param-title]').textContent = e.target.value || 'New parameter';
                }
            });

            list.querySelectorAll('[data-param]').forEach(toggleInputType);
            renumber();
        })();
    </script>
@endpush
