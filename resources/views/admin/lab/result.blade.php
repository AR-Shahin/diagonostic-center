@extends('admin.layouts.master')

@section('title', $item->name.' · '.$invoice->patient_name)

@push('css')
    <style>
        .card:hover { transform: none; }
        .res-table td, .res-table th { vertical-align: middle; }
        .res-table .group-row td { background: var(--bs-tertiary-bg); font-weight: 600; font-size: .85rem; text-transform: uppercase; letter-spacing: .03em; }
        .res-input { max-width: 11rem; font-weight: 600; font-variant-numeric: tabular-nums; }
        .res-input.is-high, .res-input.is-low { border-color: #dc3545; color: #b02a37; background: #fff5f5; }
        .flag { display: inline-block; min-width: 1.6rem; font-weight: 700; }
        .flag.H, .flag.L { color: #b02a37; }
        .prev { font-size: .78rem; color: var(--bs-secondary-color); }
        .editor-toolbar .btn { min-width: 2.2rem; }
        .editor { min-height: 18rem; padding: 1rem 1.2rem; border: 1px solid var(--bs-border-color); border-radius: 0 0 .5rem .5rem; background: #fff; line-height: 1.7; outline: none; }
        .editor:focus { border-color: #86b7fe; box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15); }
        .verified-stamp { border: 2px solid #198754; color: #198754; border-radius: .6rem; padding: .3rem .8rem; font-weight: 700; display: inline-block; }
    </style>
@endpush

@section('master_content')
    @php
        $result = $item->result;
        $locked = $result?->isVerified() || $item->lab_status === 'cancelled' || ! in_array('lab-result', $permissions);
        $needsSample = $item->sample_id && ! $item->sample?->isCollected();
        $groups = $test->parameters->groupBy(fn ($p) => $p->group_name ?? '');
    @endphp

    <x-admin.page-header :title="$item->name" icon="fas fa-keyboard"
        :subtitle="$invoice->patient_name.' · '.$invoice->patient_age.' / '.ucfirst($invoice->patient_gender).' · '.$invoice->invoice_no.($item->sample ? ' · Sample '.$item->sample->sample_no : '')">
        <a href="{{ route('admin.lab.invoice', $invoice) }}" class="btn btn-sm btn-light"><i class="fas fa-arrow-left me-1"></i> Back to bill</a>
    </x-admin.page-header>

    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0 small">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @if ($result?->isVerified())
        <div class="alert alert-success d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span><span class="verified-stamp me-2"><i class="fas fa-check-circle"></i> VERIFIED</span> by {{ $result->verifiedBy->name ?? '—' }} on {{ $result->verified_at->format('d M Y, h:i A') }}</span>
            @if (in_array('lab-verify', $permissions) && $item->lab_status !== 'cancelled')
                <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#amendModal"><i class="fas fa-edit me-1"></i> Amend</button>
            @endif
        </div>
    @elseif ($result)
        <div class="alert alert-info small mb-3"><i class="fas fa-info-circle me-1"></i> Entered by {{ $result->enteredBy->name ?? '—' }} at {{ $result->entered_at?->format('d M, h:i A') }} — waiting for verification.
            @if ($result->amendments) <b>Amended {{ $result->amendments }}×</b> ({{ $result->amend_reason }}). @endif</div>
    @endif
    @if ($needsSample)
        <div class="alert alert-warning"><i class="fas fa-vial me-1"></i> The sample ({{ $item->sample->sample_no }}) is not collected yet. Mark it collected on the bill’s lab page first.</div>
    @endif

    <form method="POST" action="{{ route('admin.lab.save', $item) }}" id="resultForm">
        @csrf
        <fieldset @disabled($locked || $needsSample)>
            @if ($test->report_type === 'parameter')
                <div class="card mb-3">
                    <div class="card-body p-0 table-responsive">
                        <table class="table res-table mb-0">
                            <thead class="small text-muted"><tr><th>Test</th><th>Result</th><th></th><th>Unit</th><th>Reference range</th></tr></thead>
                            <tbody>
                                @foreach ($groups as $groupName => $params)
                                    @if ($groupName !== '')<tr class="group-row"><td colspan="5">{{ $groupName }}</td></tr>@endif
                                    @foreach ($params as $p)
                                        @php
                                            $saved = $values[$p->id] ?? null;
                                            $val = old("values.{$p->id}", $saved?->value ?? $p->default_value);
                                            $range = $ranges[$p->id];
                                            $prev = $previous[$p->id] ?? null;
                                        @endphp
                                        <tr>
                                            <td><label for="v{{ $p->id }}" class="fw-semibold mb-0">{{ $p->name }}</label>
                                                @if ($prev)<div class="prev">Previous: {{ $prev->value }} {{ $prev->unit }} @if ($prev->flag)({{ $prev->flag }})@endif · {{ $prev->result?->verified_at?->format('d M Y') }}</div>@endif</td>
                                            <td>
                                                @if ($p->formula)
                                                    <input class="form-control res-input bg-body-tertiary" id="v{{ $p->id }}" value="{{ $saved?->value }}" readonly data-code="{{ $p->code }}" data-formula="{{ $p->formula }}" data-decimals="{{ $p->decimal_places }}" data-low="{{ $range['low'] }}" data-high="{{ $range['high'] }}" title="Calculated: {{ $p->formula }}">
                                                    <div class="prev">= {{ $p->formula }}</div>
                                                @elseif ($p->input_type === 'option')
                                                    <select class="form-select res-input" id="v{{ $p->id }}" name="values[{{ $p->id }}]" data-code="{{ $p->code }}">
                                                        <option value="">—</option>
                                                        @foreach (array_map('trim', explode(',', (string) $p->options)) as $opt)
                                                            <option @selected($val === $opt)>{{ $opt }}</option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <input class="form-control res-input" id="v{{ $p->id }}" name="values[{{ $p->id }}]" value="{{ $val }}" data-code="{{ $p->code }}"
                                                        @if ($p->input_type === 'number') inputmode="decimal" data-numeric data-low="{{ $range['low'] }}" data-high="{{ $range['high'] }}" @endif autocomplete="off">
                                                @endif
                                            </td>
                                            <td><span class="flag {{ $saved?->flag }}" data-flag-for="v{{ $p->id }}">{{ $saved?->flag }}</span></td>
                                            <td class="text-muted">{{ $p->unit?->name }}</td>
                                            <td class="small">{{ $range['label'] }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                @if ($test->parameters->isEmpty())
                                    <tr><td colspan="5" class="text-center text-muted py-4">This test has no parameters yet. Add them in Test catalog → Tests.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif ($test->report_type === 'template')
                @php
                    $body = old('body', $result?->body ?: ($test->reportTemplates->firstWhere('is_default', true) ?? $test->reportTemplates->first())?->body);
                @endphp
                <div class="card mb-3">
                    <div class="card-header d-flex flex-wrap gap-2 align-items-center editor-toolbar">
                        <div class="btn-group btn-group-sm" role="group" aria-label="Formatting">
                            <button type="button" class="btn btn-light" data-cmd="bold" title="Bold"><b>B</b></button>
                            <button type="button" class="btn btn-light" data-cmd="italic" title="Italic"><i>I</i></button>
                            <button type="button" class="btn btn-light" data-cmd="underline" title="Underline"><u>U</u></button>
                            <button type="button" class="btn btn-light" data-cmd="insertUnorderedList" title="Bullets"><i class="fas fa-list-ul"></i></button>
                            <button type="button" class="btn btn-light" data-cmd="insertOrderedList" title="Numbering"><i class="fas fa-list-ol"></i></button>
                            <button type="button" class="btn btn-light" data-cmd="formatBlock" data-value="h4" title="Heading">H</button>
                            <button type="button" class="btn btn-light" data-cmd="formatBlock" data-value="p" title="Normal text">¶</button>
                        </div>
                        @if ($test->reportTemplates->count())
                            <select class="form-select form-select-sm w-auto ms-auto" id="templatePicker" aria-label="Load a template">
                                <option value="">Load template…</option>
                                @foreach ($test->reportTemplates as $tpl)<option value="{{ $tpl->id }}">{{ $tpl->name }}</option>@endforeach
                            </select>
                        @endif
                    </div>
                    <div class="editor" id="editor" contenteditable="{{ $locked ? 'false' : 'true' }}" aria-label="Report text">{!! \App\Services\Lab\ReportHtml::clean((string) $body) !!}</div>
                    <textarea name="body" id="bodyField" hidden></textarea>
                </div>
                <template id="templates">@foreach ($test->reportTemplates as $tpl)<div data-id="{{ $tpl->id }}">{!! \App\Services\Lab\ReportHtml::clean($tpl->body) !!}</div>@endforeach</template>
            @else
                @php
                    $c = old('culture', $result?->culture ?? ['growth' => false, 'antibiotics' => []]);
                @endphp
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="form-label fw-semibold d-block">Growth</span>
                            <div class="btn-group" role="group" aria-label="Growth">
                                <input type="radio" class="btn-check" name="culture[growth]" id="gNo" value="no" @checked(empty($c['growth']) || $c['growth'] === 'no')>
                                <label class="btn btn-outline-success" for="gNo">No growth</label>
                                <input type="radio" class="btn-check" name="culture[growth]" id="gYes" value="yes" @checked(! empty($c['growth']) && $c['growth'] !== 'no')>
                                <label class="btn btn-outline-danger" for="gYes">Growth found</label>
                            </div>
                        </div>
                        <div id="growthBox">
                            <div class="row g-2">
                                <div class="col-md-7"><label class="form-label fw-semibold" for="organism">Organism isolated</label><input class="form-control" id="organism" name="culture[organism]" value="{{ $c['organism'] ?? '' }}" placeholder="Escherichia coli"></div>
                                <div class="col-md-5"><label class="form-label fw-semibold" for="colony">Colony count</label><input class="form-control" id="colony" name="culture[colony_count]" value="{{ $c['colony_count'] ?? '' }}" placeholder="> 10^5 CFU/mL"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3 mb-1">
                                <b>Antibiotic sensitivity</b>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-light" id="addCommon">Add common antibiotics</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="addAb"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                            <table class="table table-sm align-middle"><thead class="small text-muted"><tr><th>Antibiotic</th><th style="width:16rem">Result</th><th></th></tr></thead><tbody id="abRows"></tbody></table>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card mb-3">
                <div class="card-body">
                    <label class="form-label fw-semibold" for="comment">Comment / note (printed on the report)</label>
                    <textarea class="form-control" id="comment" name="comment" rows="2">{{ old('comment', $result?->comment) }}</textarea>
                </div>
            </div>

            @unless ($locked || $needsSample)
                <div class="d-flex flex-wrap gap-2 justify-content-end mb-5">
                    <input type="hidden" name="next" value="list" id="nextField">
                    <button class="btn btn-outline-primary" name="action" value="save" onclick="document.getElementById('nextField').value='stay'"><i class="fas fa-save me-1"></i> Save</button>
                    <button class="btn btn-primary" name="action" value="save"><i class="fas fa-paper-plane me-1"></i> Save & send for verification</button>
                    @if (in_array('lab-verify', $permissions))
                        <button class="btn btn-success" name="action" value="verify" onclick="return confirm('Verify and sign this report?')"><i class="fas fa-user-check me-1"></i> Save & verify</button>
                    @endif
                </div>
            @endunless
        </fieldset>
    </form>

    @if ($result && ! $result->isVerified() && $locked === false && in_array('lab-verify', $permissions) && $item->lab_status === 'processing')
        <p class="text-muted small text-end">Tip: “Save & verify” signs the report with your name.</p>
    @endif

    @if ($result?->isVerified())
        <div class="modal fade" id="amendModal" tabindex="-1" aria-labelledby="amendTitle" aria-hidden="true">
            <div class="modal-dialog">
                <form class="modal-content" method="POST" action="{{ route('admin.lab.amend', $item) }}">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title" id="amendTitle">Amend verified report</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                        <p class="small text-muted">The report goes back to “awaiting verification”. The reason and the number of amendments are kept.</p>
                        <label class="form-label fw-semibold" for="amendReason">Reason</label>
                        <input class="form-control" id="amendReason" name="reason" required>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button><button class="btn btn-warning">Open for correction</button></div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('script')
    <script>
        (function () {
            const form = document.getElementById('resultForm');

            /* ---- Parameter results: live H/L flag + calculated values ---- */
            const codeInputs = () => Object.fromEntries([...form.querySelectorAll('[data-code]')].map((el) => [el.dataset.code.toUpperCase(), el.value]));
            const evalFormula = (formula, values) => {
                let missing = false;
                const expr = formula.replace(/[A-Za-z_][A-Za-z0-9_]*/g, (code) => {
                    const v = values[code.toUpperCase()];
                    if (v === undefined || v === '' || isNaN(parseFloat(v))) { missing = true; return '0'; }
                    return '(' + parseFloat(v) + ')';
                });
                if (missing || !/^[0-9+\-*/(). ]+$/.test(expr)) return null;
                try { const r = Function('"use strict";return (' + expr + ')')(); return Number.isFinite(r) ? r : null; } catch (e) { return null; }
            };
            const flag = (el) => {
                const v = parseFloat(el.value);
                const low = el.dataset.low === '' ? null : parseFloat(el.dataset.low);
                const high = el.dataset.high === '' ? null : parseFloat(el.dataset.high);
                let f = '';
                if (el.value !== '' && !isNaN(v)) { if (low !== null && v < low) f = 'L'; else if (high !== null && v > high) f = 'H'; }
                el.classList.toggle('is-low', f === 'L'); el.classList.toggle('is-high', f === 'H');
                const out = form.querySelector(`[data-flag-for="${el.id}"]`);
                if (out) { out.textContent = f; out.className = 'flag ' + f; }
            };
            const recalc = () => {
                const values = codeInputs();
                form.querySelectorAll('[data-formula]').forEach((el) => {
                    const r = evalFormula(el.dataset.formula, values);
                    el.value = r === null ? '' : r.toFixed(+el.dataset.decimals);
                    values[el.dataset.code.toUpperCase()] = el.value;
                    flag(el);
                });
            };
            form.addEventListener('input', (e) => { if (e.target.matches('[data-numeric]')) { flag(e.target); recalc(); } });
            form.querySelectorAll('[data-numeric]').forEach(flag);
            recalc();
            // Enter moves to the next box (fast keyboard entry)
            const boxes = [...form.querySelectorAll('.res-input:not([readonly])')];
            boxes.forEach((el, i) => el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') { e.preventDefault(); (boxes[i + 1] || el).focus(); }
            }));
            (boxes.find((b) => !b.value) || boxes[0])?.focus();

            /* ---- Template editor ---- */
            const editor = document.getElementById('editor');
            if (editor) {
                document.querySelectorAll('[data-cmd]').forEach((b) => b.addEventListener('click', () => {
                    editor.focus(); document.execCommand(b.dataset.cmd, false, b.dataset.value || null);
                }));
                document.getElementById('templatePicker')?.addEventListener('change', (e) => {
                    const tpl = document.getElementById('templates').content.querySelector(`[data-id="${e.target.value}"]`);
                    if (tpl && (editor.innerText.trim() === '' || confirm('Replace the current text with this template?'))) editor.innerHTML = tpl.innerHTML;
                    e.target.value = '';
                });
                form.addEventListener('submit', () => { document.getElementById('bodyField').value = editor.innerHTML; });
            }

            /* ---- Culture ---- */
            const abRows = document.getElementById('abRows');
            if (abRows) {
                let n = 0;
                const addRow = (name = '', result = '') => {
                    const i = n++;
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td><input class="form-control form-control-sm" name="culture[antibiotics][${i}][name]" value="${name.replace(/"/g, '&quot;')}"></td>
                        <td><div class="btn-group btn-group-sm">${['S', 'I', 'R'].map((r) => `<input type="radio" class="btn-check" name="culture[antibiotics][${i}][result]" id="ab${i}${r}" value="${r}" ${result === r ? 'checked' : ''}><label class="btn btn-outline-${r === 'S' ? 'success' : r === 'I' ? 'warning' : 'danger'}" for="ab${i}${r}">${{ S: 'Sensitive', I: 'Intermediate', R: 'Resistant' }[r]}</label>`).join('')}</div></td>
                        <td class="text-end"><button type="button" class="btn btn-sm btn-link text-danger" aria-label="Remove">✕</button></td>`;
                    tr.querySelector('button').addEventListener('click', () => tr.remove());
                    abRows.appendChild(tr);
                };
                @json(isset($c) ? ($c['antibiotics'] ?? []) : []).forEach((a) => addRow(a.name, a.result));
                document.getElementById('addAb').addEventListener('click', () => addRow());
                document.getElementById('addCommon').addEventListener('click', () => ['Amikacin', 'Amoxiclav', 'Azithromycin', 'Ceftriaxone', 'Cefixime', 'Ciprofloxacin', 'Gentamicin', 'Imipenem', 'Meropenem', 'Nitrofurantoin', 'Cotrimoxazole', 'Levofloxacin'].forEach((a) => addRow(a)));
                const toggle = () => { document.getElementById('growthBox').hidden = !document.getElementById('gYes').checked; };
                form.querySelectorAll('[name="culture[growth]"]').forEach((r) => r.addEventListener('change', toggle));
                toggle();
            }
        })();
    </script>
@endpush
