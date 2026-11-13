@extends('admin.layouts.master')

@section('title', 'New bill')

@push('css')
    <style>
        .pos .card:hover { transform: none; box-shadow: var(--bs-box-shadow-sm) !important; }
        .ta { position: relative; }
        .ta-list { position: absolute; z-index: 1056; left: 0; right: 0; top: calc(100% + 4px); max-height: 22rem; overflow: auto; background: #fff; border: 1px solid var(--bs-border-color); border-radius: .75rem; box-shadow: 0 16px 40px rgba(0,0,0,.12); padding: .25rem; }
        .ta-item { display: flex; justify-content: space-between; gap: 1rem; padding: .55rem .75rem; border-radius: .55rem; cursor: pointer; }
        .ta-item.is-active, .ta-item:hover { background: var(--bs-primary-bg-subtle); }
        .ta-item small { color: var(--bs-secondary-color); }
        .ta-empty { padding: .75rem; color: var(--bs-secondary-color); font-size: .9rem; }
        .chip-btn { border: 1px solid var(--bs-border-color); background: #fff; border-radius: 99px; padding: .2rem .75rem; font-size: .85rem; }
        .chip-btn:hover { border-color: var(--bs-primary); color: var(--bs-primary); }
        .patient-chip { display: flex; gap: .9rem; align-items: center; padding: .75rem 1rem; border-radius: .9rem; background: var(--bs-success-bg-subtle); border: 1px solid var(--bs-success-border-subtle); }
        .patient-avatar { width: 2.6rem; height: 2.6rem; border-radius: 50%; display: grid; place-items: center; background: #fff; color: var(--bs-success); font-size: 1.1rem; }
        .cart-table td { vertical-align: middle; }
        .cart-sub { font-size: .8rem; color: var(--bs-secondary-color); }
        .sum-row { display: flex; justify-content: space-between; align-items: center; padding: .3rem 0; }
        .sum-net { font-size: 1.8rem; font-weight: 700; font-variant-numeric: tabular-nums; }
        .num { font-variant-numeric: tabular-nums; }
        .sticky-side { position: sticky; top: 1rem; }
        .kbd { font-size: .7rem; padding: .05rem .35rem; border: 1px solid var(--bs-border-color); border-radius: .3rem; background: var(--bs-tertiary-bg); }
    </style>
@endpush

@section('master_content')
    <x-admin.page-header title="New bill" icon="fas fa-cash-register" subtitle="Find or add the patient, add tests, take payment.">
        <a href="{{ route('admin.invoices.index') }}" class="btn btn-sm btn-light"><i class="fas fa-list me-1"></i> Invoices</a>
    </x-admin.page-header>

    @if ($errors->any())
        <div class="alert alert-danger"><b>Please fix these:</b><ul class="mb-0 small">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('admin.invoices.store') }}" id="billForm" class="pos" autocomplete="off" novalidate>
        @csrf
        <input type="hidden" name="patient_id" id="patientId" value="{{ $patient['id'] ?? '' }}">
        <input type="hidden" name="referrer_id" id="referrerId" value="{{ $referrer['id'] ?? '' }}">
        <input type="hidden" name="after" id="afterAction" value="show">
        <div id="itemInputs"></div>

        <div class="row g-3">
            <div class="col-xl-8">
                {{-- Patient --}}
                <div class="card mb-3">
                    <div class="card-header d-flex flex-wrap align-items-center gap-2">
                        <b class="me-auto"><i class="fas fa-user text-primary me-1"></i> Patient</b>
                        <div class="ta flex-grow-1" style="max-width: 26rem">
                            <input type="search" class="form-control form-control-sm" id="patientSearch" placeholder="Search existing: mobile, name or patient ID" aria-label="Search patient">
                            <div class="ta-list" id="patientResults" hidden></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="patientChip" hidden></div>
                        <div id="newPatient">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <label class="form-label small fw-semibold" for="pName">Name <span class="text-danger">*</span></label>
                                    <input class="form-control @error('patient.name') is-invalid @enderror" id="pName" name="patient[name]" value="{{ old('patient.name') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold" for="pMobile">Mobile <span class="text-danger">*</span></label>
                                    <input class="form-control @error('patient.mobile') is-invalid @enderror" id="pMobile" name="patient[mobile]" value="{{ old('patient.mobile') }}" placeholder="01XXXXXXXXX" inputmode="tel">
                                    <div class="form-text" id="mobileHint"></div>
                                    <div class="form-check small mt-1" id="familyBox" @if (! old('as_family_member')) hidden @endif>
                                        <input type="hidden" name="as_family_member" value="0">
                                        <input class="form-check-input" type="checkbox" name="as_family_member" value="1" id="asFamily" @checked(old('as_family_member'))>
                                        <label class="form-check-label" for="asFamily">Add as a new family member on this number</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <span class="form-label small fw-semibold d-block mb-2">Gender <span class="text-danger">*</span></span>
                                    <div class="btn-group btn-group-sm w-100" role="group" aria-label="Gender">
                                        @foreach (['male' => 'M', 'female' => 'F', 'other' => 'O'] as $g => $short)
                                            <input type="radio" class="btn-check" name="patient[gender]" id="g_{{ $g }}" value="{{ $g }}" @checked(old('patient.gender') === $g)>
                                            <label class="btn btn-outline-primary" for="g_{{ $g }}" title="{{ ucfirst($g) }}">{{ $short }}</label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-4 col-md-2">
                                    <label class="form-label small fw-semibold" for="pAgeY">Age (Y)</label>
                                    <input class="form-control" type="number" min="0" max="130" id="pAgeY" name="patient[age_years]" value="{{ old('patient.age_years') }}">
                                </div>
                                <div class="col-4 col-md-2">
                                    <label class="form-label small fw-semibold" for="pAgeM">Months</label>
                                    <input class="form-control" type="number" min="0" max="11" id="pAgeM" name="patient[age_months]" value="{{ old('patient.age_months') }}">
                                </div>
                                <div class="col-4 col-md-2">
                                    <label class="form-label small fw-semibold" for="pAgeD">Days</label>
                                    <input class="form-control" type="number" min="0" max="31" id="pAgeD" name="patient[age_days]" value="{{ old('patient.age_days') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold" for="pAddress">Address</label>
                                    <input class="form-control" id="pAddress" name="patient[address]" value="{{ old('patient.address') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Items --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <b class="me-auto"><i class="fas fa-flask text-primary me-1"></i> Tests & packages <span class="badge text-bg-primary" id="cartCount">0</span></b>
                            <div class="ta flex-grow-1" style="max-width: 30rem">
                                <input type="search" class="form-control" id="itemSearch" placeholder="Type a test or package name / code" aria-label="Search tests and packages">
                                <div class="ta-list" id="itemResults" hidden></div>
                            </div>
                            <span class="small text-muted d-none d-md-inline">Press <span class="kbd">/</span> to search</span>
                        </div>
                        @if ($popular->isNotEmpty())
                            <div class="d-flex flex-wrap gap-1 mt-2" id="popularChips">
                                <small class="text-muted me-1 align-self-center">Quick add:</small>
                                @foreach ($popular as $t)
                                    <button type="button" class="chip-btn" data-quick='@json(\App\Http\Controllers\Admin\InvoiceController::testRow($t))'>{{ $t->code }}</button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <table class="table cart-table mb-0">
                            <thead class="small text-muted"><tr><th style="width:2.5rem">#</th><th>Item</th><th class="text-end">Price</th><th style="width:3rem"></th></tr></thead>
                            <tbody id="cartBody"></tbody>
                        </table>
                        <p class="text-center text-muted py-4 mb-0" id="cartEmpty"><i class="fas fa-vials fa-2x d-block mb-2 opacity-50"></i>No tests added yet.</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <label class="form-label small fw-semibold" for="note">Note (printed on the invoice)</label>
                        <textarea class="form-control" id="note" name="note" rows="2">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="sticky-side">
                    <div class="card mb-3">
                        <div class="card-body">
                            @if ($branches->count() > 1)
                                <x-admin.select name="branch_id" label="Branch" :options="$branches->pluck('name_en', 'id')" :value="$defaultBranch" required />
                            @else
                                <input type="hidden" name="branch_id" id="f_branch_id" value="{{ $defaultBranch }}">
                                <div class="small text-muted mb-2"><i class="fas fa-code-branch me-1"></i> {{ $branches->first()->name_en }}</div>
                            @endif

                            <label class="form-label fw-semibold" for="referrerSearch">Ref. by</label>
                            <div id="referrerChip" class="mb-2" hidden></div>
                            <div class="ta mb-3" id="referrerBox">
                                <input type="search" class="form-control" id="referrerSearch" placeholder="Self (search doctor / agent)" aria-label="Search referrer">
                                <div class="ta-list" id="referrerResults" hidden></div>
                            </div>

                            <div class="form-check form-switch">
                                <input type="hidden" name="home_collection" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="homeCollection" name="home_collection" value="1" @checked(old('home_collection'))>
                                <label class="form-check-label" for="homeCollection">Home sample collection <span class="text-muted small">(+৳{{ number_format($rules['home']) }})</span></label>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="sum-row"><span class="text-muted">Subtotal</span><b class="num" id="sSubtotal">৳ 0</b></div>

                            <div class="sum-row gap-2">
                                <span class="text-muted">Discount</span>
                                <div class="input-group input-group-sm" style="max-width: 11rem">
                                    <input type="number" step="0.01" min="0" class="form-control text-end" id="discountValue" name="discount_value" value="{{ old('discount_value') }}" placeholder="0" aria-label="Discount">
                                    <input type="radio" class="btn-check" name="discount_type" id="dtPercent" value="percent" @checked(old('discount_type', 'percent') === 'percent')>
                                    <label class="btn btn-outline-secondary" for="dtPercent">%</label>
                                    <input type="radio" class="btn-check" name="discount_type" id="dtFlat" value="flat" @checked(old('discount_type') === 'flat')>
                                    <label class="btn btn-outline-secondary" for="dtFlat">৳</label>
                                </div>
                            </div>
                            <div class="sum-row small" id="discountLine" hidden><span class="text-muted">Discount amount</span><span class="num text-danger" id="sDiscount">− ৳ 0</span></div>
                            <div id="discountExtra" hidden>
                                <input class="form-control form-control-sm mt-1" name="discount_reason" id="discountReason" value="{{ old('discount_reason') }}" placeholder="Reason for discount (required)">
                                <div class="alert alert-warning small p-2 mt-2 mb-0" id="approvalBox" hidden>
                                    <div class="mb-1"><i class="fas fa-user-shield me-1"></i> <span id="approvalText"></span></div>
                                    <div class="row g-1">
                                        <div class="col-6">
                                            <select class="form-select form-select-sm" name="approver_id" aria-label="Approver">
                                                <option value="">Approver…</option>
                                                @foreach ($approvers as $a)
                                                    <option value="{{ $a->id }}" @selected(old('approver_id') == $a->id)>{{ $a->name }} (≤{{ (float) $a->max_discount_percent }}%)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-6"><input type="password" class="form-control form-control-sm" name="approver_password" placeholder="Their password" autocomplete="new-password" aria-label="Approver password"></div>
                                    </div>
                                </div>
                            </div>

                            @if ($rules['vat'] > 0)
                                <div class="sum-row"><span class="text-muted">VAT ({{ $rules['vat'] }}%)</span><span class="num" id="sVat">৳ 0</span></div>
                            @endif
                            <div class="sum-row" id="homeLine" hidden><span class="text-muted">Home collection</span><span class="num" id="sHome">৳ 0</span></div>
                            <div class="sum-row small" id="roundLine" hidden><span class="text-muted">Round off</span><span class="num" id="sRound">0</span></div>
                            <hr class="my-2">
                            <div class="sum-row"><b>Net payable</b><span class="sum-net text-primary" id="sNet">৳ 0</span></div>

                            <div class="row g-2 mt-1">
                                <div class="col-7">
                                    <label class="form-label small fw-semibold mb-1" for="paidAmount">Paid now</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" class="form-control text-end fw-semibold" id="paidAmount" name="paid_amount" value="{{ old('paid_amount') }}">
                                        <button class="btn btn-outline-secondary" type="button" id="payFull" title="Pay full">Full</button>
                                    </div>
                                </div>
                                <div class="col-5">
                                    <label class="form-label small fw-semibold mb-1" for="payMethod">Method</label>
                                    <select class="form-select" id="payMethod" name="payment_method">
                                        @foreach (\App\Models\Payment::METHODS as $value => $label)
                                            <option value="{{ $value }}" @selected(old('payment_method', 'cash') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12" id="trxBox" hidden>
                                    <input class="form-control form-control-sm" name="transaction_ref" value="{{ old('transaction_ref') }}" placeholder="Transaction ID / last 4 digits">
                                </div>
                            </div>
                            <div class="sum-row mt-2"><span class="text-muted">Due</span><b class="num fs-5" id="sDue">৳ 0</b></div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg" data-after="print"><i class="fas fa-print me-1"></i> Save & print</button>
                        <button type="submit" class="btn btn-outline-success" data-after="show"><i class="fas fa-save me-1"></i> Save only</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('script')
    <script>
        window.POS = {
            rules: @json($rules),
            urls: {
                items: "{{ route('admin.invoices.items') }}",
                patients: "{{ route('admin.patients.search') }}",
                referrers: "{{ route('admin.referrers.search') }}",
            },
            initial: { patient: @json($patient), referrer: @json($referrer), cart: @json($cart) },
        };
    </script>
    <script src="{{ asset('custom/js/billing.js') }}?v=1"></script>
@endpush
